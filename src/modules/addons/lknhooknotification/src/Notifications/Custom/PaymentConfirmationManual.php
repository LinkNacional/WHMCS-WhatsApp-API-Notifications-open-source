<?php

/**
 * Code: PaymentConfirmationManual
 *
 * Manual notification, fired by an admin from the invoice page, used to send
 * a payment confirmation message to the client via WhatsApp.
 */

namespace Lkn\HookNotification\Notifications\Custom;

use Lkn\HookNotification\Core\Notification\Domain\AbstractManualNotification;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameter;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameterCollection;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;
use WHMCS\Database\Capsule;

final class PaymentConfirmationManualNotification extends AbstractManualNotification
{
    public function __construct()
    {
        parent::__construct(
            'PaymentConfirmationManual',
            NotificationReportCategory::INVOICE,
            Hooks::ADMIN_INVOICES_CONTROLS_OUTPUT,
            new NotificationParameterCollection([
                new NotificationParameter(
                    'invoice_id',
                    lkn_hn_lang('Invoice ID'),
                    // Internal database ID, e.g. 202509779
                    fn (): int => $this->whmcsHookParams['invoiceid']
                ),
                new NotificationParameter(
                    'invoice_number',
                    lkn_hn_lang('Invoice number'),
                    // Formatted, customer-facing invoice number, e.g. ISH2025-10~E/759
                    fn (): string => Capsule::table('tblinvoices')
                        ->where('id', $this->whmcsHookParams['invoiceid'])
                        ->value('invoicenum')
                ),
                new NotificationParameter(
                    'invoice_items',
                    lkn_hn_lang('Invoice items'),
                    fn (): string => getItemsRelatedToInvoice($this->whmcsHookParams['invoiceid'])
                ),
                new NotificationParameter(
                    'invoice_total',
                    lkn_hn_lang('Invoice total'),
                    fn (): string => getInvoiceTotal($this->whmcsHookParams['invoiceid'])
                ),
                new NotificationParameter(
                    'invoice_balance',
                    lkn_hn_lang('Invoice balance'),
                    fn (): string => getInvoiceBalance($this->whmcsHookParams['invoiceid'])
                ),
                new NotificationParameter(
                    'invoice_pdf_url',
                    lkn_hn_lang('Invoice PDF URL'),
                    fn (): string => getInvoicePdfUrlByInvocieId($this->whmcsHookParams['invoiceid'])
                ),
                new NotificationParameter(
                    'invoice_pdf_filename',
                    lkn_hn_lang('Invoice PDF filename'),
                    fn (): string => "Invoice-{$this->whmcsHookParams['invoiceid']}.pdf"
                ),
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
                    'client_full_name',
                    lkn_hn_lang('Client full name'),
                    fn (): string => getClientFullNameByClientId($this->client->id)
                ),
                new NotificationParameter(
                    'payment_amount',
                    lkn_hn_lang('Payment amount'),
                    fn (): string => $this->getLastTransaction()->amountin ?? getInvoiceTotal($this->whmcsHookParams['invoiceid'])
                ),
                new NotificationParameter(
                    'payment_method',
                    lkn_hn_lang('Payment method'),
                    fn (): string => $this->getPaymentMethodName()
                ),
                new NotificationParameter(
                    'transaction_id',
                    lkn_hn_lang('Transaction ID'),
                    fn (): string => $this->getLastTransaction()->transid ?? ''
                ),
                new NotificationParameter(
                    'payment_date',
                    lkn_hn_lang('Payment date'),
                    fn (): string => $this->getPaymentDateTime()->format('Y-m-d')
                ),
                new NotificationParameter(
                    'payment_time',
                    lkn_hn_lang('Payment time'),
                    fn (): string => $this->getPaymentDateTime()->format('H:i:s')
                ),
            ]),
            fn () => getClientIdByInvoiceId($this->whmcsHookParams['invoiceid']),
            fn () => $this->whmcsHookParams['invoiceid']
        );
    }

    /**
     * Fetches the most recent payment transaction linked to this invoice
     * from tblaccounts. Returns null if the invoice has no logged transaction
     * (e.g. it was marked paid manually without a gateway transaction).
     */
    private function getLastTransaction()
    {
        return Capsule::table('tblaccounts')
            ->where('invoiceid', $this->whmcsHookParams['invoiceid'])
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Resolves a human-readable payment method name. Falls back to the
     * invoice's configured payment method (gateway system name) if there is
     * no logged transaction to read the gateway from.
     */
    private function getPaymentMethodName(): string
    {
        $transaction = $this->getLastTransaction();

        $gatewayModule = $transaction->gateway
            ?? Capsule::table('tblinvoices')
                ->where('id', $this->whmcsHookParams['invoiceid'])
                ->value('paymentmethod');

        if (empty($gatewayModule)) {
            return '';
        }

        return Capsule::table('tblpaymentgateways')
            ->where('gateway', $gatewayModule)
            ->where('setting', 'Name')
            ->value('value') ?? $gatewayModule;
    }

    /**
     * Resolves the payment date/time. Uses the logged transaction date when
     * available, otherwise falls back to the invoice's datepaid field, and
     * finally to now() if the invoice has no paid date recorded yet.
     */
    private function getPaymentDateTime(): \DateTime
    {
        $transaction = $this->getLastTransaction();

        $rawDate = $transaction->date
            ?? Capsule::table('tblinvoices')
                ->where('id', $this->whmcsHookParams['invoiceid'])
                ->value('datepaid');

        if (empty($rawDate) || $rawDate === '0000-00-00 00:00:00') {
            return new \DateTime();
        }

        return new \DateTime($rawDate);
    }
}
