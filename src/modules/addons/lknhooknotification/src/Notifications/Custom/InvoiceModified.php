<?php

/**
 * Code: InvoiceModified
 *
 * Cron-based notification that detects when an existing invoice's data has
 * changed (total, due date, status, items, etc.) since the last cron run.
 *
 * WHMCS does not natively track a "last modified" timestamp on invoices, so
 * this notification keeps its own snapshot table (mod_ishost_invoice_snapshot)
 * with a hash of the fields that matter, and compares it on every run.
 * A change in the hash means the invoice was modified since the last check.
 */

namespace Lkn\HookNotification\Notifications\Custom;

use DateTime;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\Notification\Domain\AbstractCronNotification;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameter;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameterCollection;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;
use WHMCS\Database\Capsule;

final class InvoiceModifiedNotification extends AbstractCronNotification implements ResendableNotificationInterface
{
    private const WHMCS_DOMAIN = 'https://indianserverhosting.com';

    private const SNAPSHOT_TABLE = 'mod_ishost_invoice_snapshot';

    public function __construct()
    {
        $parameters = [
            new NotificationParameter(
                'client_id',
                lkn_hn_lang('Client ID'),
                fn (): int => $this->client->id
            ),
            new NotificationParameter(
                'client_email',
                lkn_hn_lang('Client email'),
                fn (): string => getClientEmailByClientId($this->client->id)
            ),
            new NotificationParameter(
                'client_first_name',
                lkn_hn_lang('Client first name'),
                fn (): string => getClientFirstNameByClientId($this->client->id)
            ),
            new NotificationParameter(
                'client_last_name',
                lkn_hn_lang('Client last name'),
                fn (): string => getClientLastNameByClientId($this->client->id)
            ),
            new NotificationParameter(
                'client_full_name',
                lkn_hn_lang('Client full name'),
                fn (): string => getClientFullNameByClientId($this->client->id)
            ),
            new NotificationParameter(
                'invoice_id',
                lkn_hn_lang('Invoice ID'),
                // Internal database ID, e.g. 202509779
                fn (): int => $this->whmcsHookParams['invoice_id']
            ),
            new NotificationParameter(
                'invoice_number',
                lkn_hn_lang('Invoice number'),
                // Formatted, customer-facing invoice number, e.g. ISH2025-10~E/759
                fn (): string => (string) $this->whmcsHookParams['invoicenum']
            ),
            new NotificationParameter(
                'invoice_creation_date',
                lkn_hn_lang('Invoice creation date'),
                fn (): string => $this->whmcsHookParams['date']
            ),
            new NotificationParameter(
                'invoice_due_date',
                lkn_hn_lang('Invoice due date'),
                fn (): string => getInvoiceDueDateByInvoiceId($this->whmcsHookParams['invoice_id'])
            ),
            new NotificationParameter(
                'invoice_status',
                lkn_hn_lang('Invoice status'),
                fn (): string => $this->whmcsHookParams['status']
            ),
            new NotificationParameter(
                'invoice_amount',
                lkn_hn_lang('Invoice amount'),
                // Subtotal, i.e. the invoice amount before credit is applied.
                fn (): string => $this->whmcsHookParams['subtotal']
            ),
            new NotificationParameter(
                'credit_used',
                lkn_hn_lang('Credit used'),
                fn (): string => $this->whmcsHookParams['credit']
            ),
            new NotificationParameter(
                'invoice_total_after_credit',
                lkn_hn_lang('Invoice total (after credit)'),
                fn (): string => $this->whmcsHookParams['total']
            ),
            new NotificationParameter(
                'invoice_balance',
                lkn_hn_lang('Invoice balance'),
                fn (): string => getInvoiceBalance($this->whmcsHookParams['invoice_id'])
            ),
            new NotificationParameter(
                'invoice_items',
                lkn_hn_lang('Invoice items'),
                fn (): string => getItemsRelatedToInvoice($this->whmcsHookParams['invoice_id'])
            ),
            new NotificationParameter(
                'invoice_item',
                lkn_hn_lang('Invoice item'),
                // First item on the invoice only.
                fn (): string => $this->getFirstInvoiceItem()
            ),
            new NotificationParameter(
                'invoice_pdf_url',
                lkn_hn_lang('Invoice PDF URL'),
                fn (): string => getInvoicePdfUrlByInvocieId($this->whmcsHookParams['invoice_id'])
            ),
            new NotificationParameter(
                'modified_date',
                lkn_hn_lang('Modified date'),
                fn (): string => (new DateTime())->format('Y-m-d')
            ),
            new NotificationParameter(
                'modified_time',
                lkn_hn_lang('Modified time'),
                fn (): string => (new DateTime())->format('H:i:s')
            ),
            new NotificationParameter(
                'whmcs_domain',
                lkn_hn_lang('WHMCS domain'),
                fn (): string => self::WHMCS_DOMAIN
            ),
            new NotificationParameter(
                'message_signature',
                lkn_hn_lang('Message signature'),
                fn (): string => "Thank you for choosing Indian Server Hosting!\nIndian Server Hosting Team"
            ),
        ];

        parent::__construct(
            'InvoiceModified',
            NotificationReportCategory::INVOICE,
            Hooks::DAILY_CRON_JOB,
            new NotificationParameterCollection($parameters),
            fn () => $this->whmcsHookParams['client_id'],
            fn () => $this->whmcsHookParams['report_category_id'],
        );
    }

    /**
     * Builds the payload list fed into the notification: one entry per
     * invoice whose relevant data has changed since the last cron run.
     */
    public function getPayload(): array
    {
        $this->ensureSnapshotTableExists();

        $invoices = localAPI('GetInvoices', [
            'limitnum' => 1000,
        ]);

        $payloads = [];

        foreach ($invoices['invoices']['invoice'] as $invoice) {
            $invoiceId = $invoice['id'];
            $itemsHash = $this->getInvoiceItemsHash($invoiceId);

            $fingerprint = md5(implode('|', [
                $invoice['total'],
                $invoice['subtotal'],
                $invoice['credit'],
                $invoice['balance'],
                $invoice['duedate'],
                $invoice['status'],
                $itemsHash,
            ]));

            $existing = Capsule::table(self::SNAPSHOT_TABLE)
                ->where('invoice_id', $invoiceId)
                ->first();

            if ($existing === null) {
                // First time we see this invoice: store its baseline
                // fingerprint but don't fire a "modified" notification for it.
                Capsule::table(self::SNAPSHOT_TABLE)->insert([
                    'invoice_id'  => $invoiceId,
                    'fingerprint' => $fingerprint,
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);

                continue;
            }

            if ($existing->fingerprint === $fingerprint) {
                // No changes since last run.
                continue;
            }

            Capsule::table(self::SNAPSHOT_TABLE)
                ->where('invoice_id', $invoiceId)
                ->update([
                    'fingerprint' => $fingerprint,
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);

            $payloads[] = [
                'client_id'           => $invoice['userid'],
                'report_category_id' => $invoiceId,
                'invoice_id'          => $invoiceId,
                'invoicenum'          => $invoice['invoicenum'],
                'date'                => $invoice['date'],
                'duedate'             => $invoice['duedate'],
                'status'              => $invoice['status'],
                'subtotal'            => $invoice['subtotal'],
                'credit'              => $invoice['credit'],
                'total'               => $invoice['total'],
            ];
        }

        return $payloads;
    }

    private function getFirstInvoiceItem(): string
    {
        $invoiceId = $this->whmcsHookParams['invoice_id'];
        $items     = getInvoiceItemsDescriptionsByInvoiceId($invoiceId);

        return $items[0] ?? '';
    }

    /**
     * Hashes the invoice's line items (description + amount) so that item
     * additions, removals, or edits are also detected as a modification.
     */
    private function getInvoiceItemsHash(int $invoiceId): string
    {
        $items = Capsule::table('tblinvoiceitems')
            ->where('invoiceid', $invoiceId)
            ->orderBy('id')
            ->get(['description', 'amount']);

        $flattened = $items->map(function ($item) {
            return $item->description . ':' . $item->amount;
        })->implode('|');

        return md5($flattened);
    }

    private function ensureSnapshotTableExists(): void
    {
        if (Capsule::schema()->hasTable(self::SNAPSHOT_TABLE)) {
            return;
        }

        Capsule::schema()->create(self::SNAPSHOT_TABLE, function ($table) {
            $table->unsignedInteger('invoice_id')->primary();
            $table->string('fingerprint', 32);
            $table->dateTime('updated_at');
        });
    }

    /**
     * Rebuilds the payload for a single invoice, fresh from the database,
     * so this notification can be resent from the Notification Reports page.
     */
    public function buildResendPayload(int $categoryId, ?int $clientId): ?array
    {
        $invoice = Capsule::table('tblinvoices')->where('id', $categoryId)->first();

        if ($invoice === null) {
            return null;
        }

        return [
            'client_id'           => $invoice->userid,
            'report_category_id' => $invoice->id,
            'invoice_id'          => $invoice->id,
            'invoicenum'          => $invoice->invoicenum,
            'date'                => $invoice->date,
            'duedate'             => $invoice->duedate,
            'status'              => $invoice->status,
            'subtotal'            => $invoice->subtotal,
            'credit'              => $invoice->credit,
            'total'               => $invoice->total,
        ];
    }
}
