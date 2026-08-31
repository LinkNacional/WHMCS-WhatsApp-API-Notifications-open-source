<?php

namespace Lkn\HookNotification\Domains\Platforms\Evolution;

use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameter;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameterCollection;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;

return [
    'invoice' => [
        'category' => NotificationReportCategory::INVOICE,
        'categoryIdFinder' => fn (): int => $this->whmcsHookParams['invoiceid'],
        'clientIdFinder' => fn (): int => getClientIdByInvoiceId($this->whmcsHookParams['invoiceid']),
        'params' => new NotificationParameterCollection([
            new NotificationParameter(
                'invoice_id',
                lkn_hn_lang('invoice_id'),
                fn () => $this->whmcsHookParams['invoiceid']
            ),
            new NotificationParameter(
                'invoice_items',
                lkn_hn_lang('invoice_items'),
                fn (): string => getItemsRelatedToInvoice($this->whmcsHookParams['invoiceid'])
            ),
            new NotificationParameter(
                'invoice_due_date',
                lkn_hn_lang('invoice_due_date'),
                fn () => getInvoiceDueDateByInvoiceId($this->whmcsHookParams['invoiceid'])
            ),
            new NotificationParameter(
                'invoice_pdf_url',
                lkn_hn_lang('invoice_pdf_url'),
                fn (): string => getInvoicePdfUrlByInvocieId($this->whmcsHookParams['invoiceid'])
            ),
            new NotificationParameter(
                'invoice_balance',
                lkn_hn_lang('invoice_balance'),
                fn (): string => getInvoiceBalance($this->whmcsHookParams['invoiceid'])
            ),
            new NotificationParameter(
                'invoice_total',
                lkn_hn_lang('invoice_total'),
                fn (): string => getInvoiceTotal($this->whmcsHookParams['invoiceid'])
            ),
            new NotificationParameter(
                'invoice_subtotal',
                lkn_hn_lang('invoice_subtotal'),
                fn (): string => getInvoiceSubtotal($this->whmcsHookParams['invoiceid'])
            ),
            new NotificationParameter(
                'client_id',
                lkn_hn_lang('client_id'),
                fn () => $this->clientId
            ),
            new NotificationParameter(
                'client_first_name',
                lkn_hn_lang('client_first_name'),
                fn () => getClientFirstNameByClientId($this->clientId)
            ),
            new NotificationParameter(
                'client_email',
                lkn_hn_lang('client_email'),
                fn () => getClientEmailByClientId($this->clientId)
            ),
            new NotificationParameter(
                'client_full_name',
                lkn_hn_lang('client_full_name'),
                fn () => getClientFullNameByClientId($this->clientId)
            ),
        ]),
        'hooks' => [
            Hooks::INVOICE_CANCELLED,
            Hooks::INVOICE_CHANGE_GATEWAY,
            Hooks::INVOICE_CREATED,
            // Hooks::INVOICE_CREATION,
            // Hooks::INVOICE_CREATION_PRE_EMAIL,
            Hooks::INVOICE_PAID,
            // Hooks::INVOICE_PAID_PRE_EMAIL,
            // Hooks::INVOICE_PAYMENT_REMINDER,
            // Hooks::INVOICE_REFUNDED,
            Hooks::INVOICE_SPLIT,
            Hooks::INVOICE_UNPAID,
        ],
    ],
    'order' => [
        'category' => NotificationReportCategory::ORDER,
        'categoryIdFinder' => fn(): int => $this->whmcsHookParams['orderid'] ?? $this->whmcsHookParams['orderId'] ?? $this->whmcsHookParams['OrderID'],
        'clientIdFinder' => fn(): int => getClientIdByOrderId($this->categoryId),
        'params' => new NotificationParameterCollection([
            new NotificationParameter(
                'order_id',
                lkn_hn_lang('order_id'),
                fn () => $this->categoryId
            ),
            new NotificationParameter(
                'order_items_descrip',
                lkn_hn_lang('order_items_descrip'),
                fn () => getOrderItemsDescripByOrderId($this->categoryId)
            ),
            new NotificationParameter(
                'client_first_name',
                lkn_hn_lang('client_first_name'),
                fn () => getClientFirstNameByClientId($this->clientId)
            ),
            new NotificationParameter(
                'client_full_name',
                lkn_hn_lang('client_full_name'),
                fn () => getClientFullNameByClientId($this->clientId)
            ),
        ]),
        'hooks' => [
            Hooks::ORDER_PAID,
            Hooks::AFTER_SHOPPING_CART_CHECKOUT,
            Hooks::CANCEL_ORDER,
        ],
    ],
    'ticket' => [
        'category' => NotificationReportCategory::TICKET,
        'categoryIdFinder' => fn (): int => $this->whmcsHookParams['ticketid'],
        'clientIdFinder' => fn (): int => getClientIdByTicketId($this->whmcsHookParams['ticketid']),
        'params' => new NotificationParameterCollection([
            new NotificationParameter(
                'ticket_id',
                lkn_hn_lang('ticket_id'),
                fn () => strval($this->whmcsHookParams['ticketid'])
            ),
            new NotificationParameter(
                'ticket_mask',
                lkn_hn_lang('ticket_mask'),
                fn () => getTicketMask($this->whmcsHookParams['ticketid'])
            ),
            new NotificationParameter(
                'ticket_subject',
                lkn_hn_lang('ticket_subject'),
                fn () => $this->whmcsHookParams['subject']
            ),
            new NotificationParameter(
                'ticket_status',
                lkn_hn_lang('ticket_status'),
                fn () => $this->whmcsHookParams['status']
            ),
            new NotificationParameter(
                'client_first_name',
                lkn_hn_lang('client_first_name'),
                fn () => empty($this->clientId)
                    ? getTicketNameColumn($this->whmcsHookParams['ticketid'])
                    : getClientFirstNameByClientId($this->clientId)
            ),
            new NotificationParameter(
                'client_full_name',
                lkn_hn_lang('client_full_name'),
                fn () => empty($this->clientId)
                    ? getTicketNameColumn($this->whmcsHookParams['ticketid'])
                    : getClientFullNameByClientId($this->clientId)
            ),
        ]),
        'hooks' => [
            // Hooks::TICKET_ADD_NOTE,
            Hooks::TICKET_ADMIN_REPLY,
            Hooks::TICKET_CLOSE,
            // Hooks::TICKET_DELETE,
            // Hooks::TICKET_DELETE_REPLY,
            // Hooks::TICKET_DEPARTMENT_CHANGE,
            // Hooks::TICKET_FLAGGED,
            // Hooks::TICKET_MERGE,
            Hooks::TICKET_OPEN,
            // Hooks::TICKET_OPEN_ADMIN,
            // Hooks::TICKET_OPEN_VALIDATION,
            // Hooks::TICKET_PIPING,
            // Hooks::TICKET_PRIORITY_CHANGE,
            // Hooks::TICKET_SPLIT,
            // Hooks::TICKET_STATUS_CHANGE,
            // Hooks::TICKET_SUBJECT_CHANGE,
            // Hooks::TICKET_USER_REPLY,
        ],
    ],
    'module' => [
        'category' => NotificationReportCategory::SERVICE,
        'categoryIdFinder' => fn (): int => $this->whmcsHookParams['params']['serviceid'],
        'clientIdFinder' => fn (): int => getClientIdByModuleId($this->categoryId),
        'params' => new NotificationParameterCollection([
            new NotificationParameter(
                'service_id',
                lkn_hn_lang('service_id'),
                fn () => $this->whmcsHookParams['params']['serviceid']
            ),
            new NotificationParameter(
                'client_first_name',
                lkn_hn_lang('client_first_name'),
                fn () => getClientFirstNameByClientId($this->clientId)
            ),
            new NotificationParameter(
                'client_full_name',
                lkn_hn_lang('client_full_name'),
                fn () => getClientFullNameByClientId($this->clientId)
            ),
        ]),
        'hooks' => [
            // Hooks::AFTER_MODULE_CHANGE_PACKAGE,
            // Hooks::AFTER_MODULE_CHANGE_PACKAGE_FAILED,
            // Hooks::AFTER_MODULE_CHANGE_PASSWORD,
            // Hooks::AFTER_MODULE_CHANGE_PASSWORD_FAILED,
            // Hooks::AFTER_MODULE_CREATE,
            // Hooks::AFTER_MODULE_CREATE_FAILED,
            // Hooks::AFTER_MODULE_CUSTOM,
            // Hooks::AFTER_MODULE_CUSTOM_FAILED,
            // Hooks::AFTER_MODULE_DEPROVISION_ADD_ON_FEATURE,
            // Hooks::AFTER_MODULE_DEPROVISION_ADD_ON_FEATURE_FAILED,
            // Hooks::AFTER_MODULE_PROVISION_ADD_ON_FEATURE,
            // Hooks::AFTER_MODULE_PROVISION_ADD_ON_FEATURE_FAILED,
            Hooks::AFTER_MODULE_SUSPEND,
            // Hooks::AFTER_MODULE_SUSPEND_ADD_ON_FEATURE,
            // Hooks::AFTER_MODULE_SUSPEND_ADD_ON_FEATURE_FAILED,
            // Hooks::AFTER_MODULE_SUSPEND_FAILED,
            // Hooks::AFTER_MODULE_TERMINATE,
            // Hooks::AFTER_MODULE_TERMINATE_FAILED,
            Hooks::AFTER_MODULE_UNSUSPEND,
            // Hooks::AFTER_MODULE_UNSUSPEND_ADD_ON_FEATURE,
            // Hooks::AFTER_MODULE_UNSUSPEND_ADD_ON_FEATURE_FAILED,
            // Hooks::AFTER_MODULE_UNSUSPEND_FAILED,
        ],
    ],
];
