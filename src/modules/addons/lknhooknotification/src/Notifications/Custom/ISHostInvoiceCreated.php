<?php

/**
 * Code: ISHostInvoiceCreated
 *
 * Cron-based notification that fires for invoices created on the current day.
 *
 * @see https://developers.whmcs.com/hooks-reference/invoices-and-quotes/#invoicecreated
 */

namespace Lkn\HookNotification\Notifications\Custom;

use DateTime;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\Notification\Domain\AbstractCronNotification;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameter;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameterCollection;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;
use WHMCS\Database\Capsule;

final class ISHostInvoiceCreatedNotification extends AbstractCronNotification implements ResendableNotificationInterface
{
    /**
     * Domain shown in the "WHMCS domain" parameter.
     * Update this if the installation domain ever changes.
     */
    private const WHMCS_DOMAIN = 'https://indianserverhosting.com';

    public function __construct()
    {
        $parameters = [
            new NotificationParameter(
                'client_first_name',
                lkn_hn_lang('Client first name'),
                fn (): string => getClientFirstNameByClientId($this->client->id)
            ),
            new NotificationParameter(
                'client_last_name',
                lkn_hn_lang('Client last name'),
                // Assumes a getClientLastNameByClientId() helper exists in the
                // module, following the same convention as the first/full name
                // helpers already used elsewhere. Add it if it isn't defined yet.
                fn (): string => getClientLastNameByClientId($this->client->id)
            ),
            new NotificationParameter(
                'client_full_name',
                lkn_hn_lang('Client full name'),
                fn (): string => getClientFullNameByClientId($this->client->id)
            ),
            new NotificationParameter(
                'message_signature',
                lkn_hn_lang('Message signature'),
                // Static, editable closing signature used at the end of the message.
                fn (): string => "Thank you for choosing Indian Server Hosting!\nIndian Server Hosting Team"
            ),
            new NotificationParameter(
                'whmcs_domain',
                lkn_hn_lang('WHMCS domain'),
                fn (): string => self::WHMCS_DOMAIN
            ),
            new NotificationParameter(
                'invoice_number',
                lkn_hn_lang('Invoice number'),
                // Formatted, customer-facing invoice number, e.g. ISH2025-10~E/759
                fn (): string => (string) $this->whmcsHookParams['invoicenum']
            ),
            new NotificationParameter(
                'invoice_id',
                lkn_hn_lang('Invoice ID'),
                // Internal database ID, e.g. 202509779
                fn (): string => (string) $this->whmcsHookParams['invoice_id']
            ),
            new NotificationParameter(
                'invoice_creation_date',
                lkn_hn_lang('Invoice creation date'),
                fn (): string => $this->whmcsHookParams['date']
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
                'invoice_due_date',
                lkn_hn_lang('Invoice due date'),
                fn (): string => getInvoiceDueDateByInvoiceId($this->whmcsHookParams['invoice_id'])
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
        ];

        parent::__construct(
            'ISHostInvoiceCreated',
            NotificationReportCategory::INVOICE,
            Hooks::DAILY_CRON_JOB,
            new NotificationParameterCollection($parameters),
            fn () => $this->whmcsHookParams['client_id'],
            fn () => $this->whmcsHookParams['report_category_id'],
        );
    }

    /**
     * Builds the payload list fed into the notification, one entry per
     * invoice created today. Each entry is passed to the closures above as
     * if it were $this->whmcsHookParams.
     */
    public function getPayload(): array
    {
        $invoices = localAPI('GetInvoices', [
            'limitnum' => 1000,
        ]);

        $payloads = [];

        foreach ($invoices['invoices']['invoice'] as $invoice) {
            $createdDate  = new DateTime($invoice['date']);
            $currentDate  = new DateTime();

            // Only notify for invoices created today.
            if ($createdDate->format('Y-m-d') !== $currentDate->format('Y-m-d')) {
                continue;
            }

            if ($invoice['total'] === '0.00') {
                continue;
            }

            $payloads[] = [
                'client_id'           => $invoice['userid'],
                'report_category_id' => $invoice['id'],
                'invoice_id'          => $invoice['id'],
                'invoicenum'          => $invoice['invoicenum'],
                'date'                => $invoice['date'],
                'duedate'             => $invoice['duedate'],
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
            'subtotal'            => $invoice->subtotal,
            'credit'              => $invoice->credit,
            'total'               => $invoice->total,
        ];
    }
}
