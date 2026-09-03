<?php

namespace Lkn\HookNotification\Core\NotificationReport\Domain;

enum NotificationReportStatus: string
{
    case SENT = 'sent';

    /**
     * Reasons:
     *
     * 1. No template found for client or system language.
     */
    case NOT_SENT = 'not_sent';

    /**
     * Reasons:
     *
     * 1. API error.
     */
    case ERROR = 'error';

    /**
     * Reasons:
     *
     * 1. Notification from NOT_SENT or ERROR was sent.
     */
    case RESENT = 'resent';

    public function label(): string
    {
        return match ($this) {
            self::SENT => lkn_hn_lang('Sent'),
            self::NOT_SENT => lkn_hn_lang('Not sent'),
            self::ERROR => lkn_hn_lang('Error'),
            self::RESENT => lkn_hn_lang('Resent'),
        };
    }
}
