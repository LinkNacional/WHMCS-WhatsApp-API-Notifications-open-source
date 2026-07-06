<?php

/**
 * Code: DomainRenewalReminderThird
 *
 * Third Renewal Notice (1 day before expiry)
 *
 * Cron-based notification, fires for active domains whose expiry date is
 * exactly 1 day(s) before today. Shares its parameter set
 * and payload logic with the other 6 renewal reminders via
 * DomainRenewalReminderTrait, but has its own code, template, enable/disable
 * toggle, and notification report.
 */

namespace Lkn\HookNotification\Notifications\Custom;

use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\Notification\Domain\AbstractCronNotification;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;

final class DomainRenewalReminderThirdNotification extends AbstractCronNotification
{
    use DomainRenewalReminderTrait;

    public function __construct()
    {
        $this->offsetDays     = 1;
        $this->isBeforeExpiry = true;

        parent::__construct(
            'DomainRenewalReminderThird',
            NotificationReportCategory::DOMAIN,
            Hooks::DAILY_CRON_JOB,
            $this->buildDomainReminderParameters(),
            fn () => $this->whmcsHookParams['client_id'],
            fn () => $this->whmcsHookParams['report_category_id'],
        );
    }
}
