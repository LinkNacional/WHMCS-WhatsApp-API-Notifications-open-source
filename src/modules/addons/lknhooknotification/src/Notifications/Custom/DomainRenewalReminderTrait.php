<?php

/**
 * Shared logic for the 7 domain renewal reminder notifications.
 *
 * This is a TRAIT, not a notification class — it is not auto-discovered or
 * instantiated by the module on its own. Each of the 7 reminder classes
 * (DomainRenewalReminderFirst ... Seventh) uses this trait and sets
 * $offsetDays / $isBeforeExpiry before calling parent::__construct(), so
 * each still has its own code, its own WhatsApp template, its own
 * enable/disable toggle, and its own notification report.
 */

namespace Lkn\HookNotification\Notifications\Custom;

use DateTime;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameter;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameterCollection;
use WHMCS\Database\Capsule;

trait DomainRenewalReminderTrait
{
    /** Days before (true) or after (false) the expiry date this reminder fires on. */
    protected int $offsetDays;

    protected bool $isBeforeExpiry;

    protected function getWhmcsDomain(): string
    {
        return 'https://indianserverhosting.com';
    }

    protected function buildDomainReminderParameters(): NotificationParameterCollection
    {
        return new NotificationParameterCollection([
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
                'domain_name',
                lkn_hn_lang('Domain name'),
                fn (): string => $this->whmcsHookParams['domain_name']
            ),
            new NotificationParameter(
                'registrar',
                lkn_hn_lang('Registrar'),
                fn (): string => $this->whmcsHookParams['registrar']
            ),
            new NotificationParameter(
                'expiry_date',
                lkn_hn_lang('Expiry date'),
                fn (): string => $this->whmcsHookParams['expiry_date']
            ),
            new NotificationParameter(
                'days_until_expiry',
                lkn_hn_lang('Days until expiry'),
                // 0 once the domain has already expired.
                fn (): int => max(0, $this->getDaysUntilExpiry())
            ),
            new NotificationParameter(
                'days_expired',
                lkn_hn_lang('Days expired'),
                // 0 while the domain has not expired yet.
                fn (): int => max(0, -$this->getDaysUntilExpiry())
            ),
            new NotificationParameter(
                'renewal_amount',
                lkn_hn_lang('Renewal amount'),
                fn (): string => $this->whmcsHookParams['renewal_amount']
            ),
            new NotificationParameter(
                'whmcs_domain',
                lkn_hn_lang('WHMCS domain'),
                fn (): string => $this->getWhmcsDomain()
            ),
            new NotificationParameter(
                'message_signature',
                lkn_hn_lang('Message signature'),
                fn (): string => "Thank you for choosing Indian Server Hosting!\nIndian Server Hosting Team"
            ),
        ]);
    }

    /**
     * Builds the payload list fed into the notification: one entry per
     * active domain whose expiry date lands exactly $offsetDays before
     * (or after) today, depending on $isBeforeExpiry.
     */
    public function getPayload(): array
    {
        $targetDate = new DateTime('today');

        if ($this->isBeforeExpiry) {
            $targetDate->modify("+{$this->offsetDays} days");
        } else {
            $targetDate->modify("-{$this->offsetDays} days");
        }

        $domains = Capsule::table('tbldomains')
            ->whereDate('expirydate', $targetDate->format('Y-m-d'))
            ->where('status', 'Active')
            ->get();

        $payloads = [];

        foreach ($domains as $domain) {
            $payloads[] = [
                'client_id'           => $domain->userid,
                'report_category_id' => $domain->id,
                'domain_id'           => $domain->id,
                'domain_name'         => $domain->domain,
                'registrar'           => $domain->registrar ?: 'N/A',
                'expiry_date'         => $domain->expirydate,
                'renewal_amount'      => $domain->recurringamount,
            ];
        }

        return $payloads;
    }

    /**
     * Signed day difference between today and the domain's expiry date.
     * Positive = days remaining until expiry. Negative = days since expiry.
     */
    private function getDaysUntilExpiry(): int
    {
        $expiry = new DateTime($this->whmcsHookParams['expiry_date']);
        $today  = new DateTime('today');
        $diff   = $today->diff($expiry);

        return $diff->invert ? -(int) $diff->format('%a') : (int) $diff->format('%a');
    }

    /**
     * Rebuilds the payload for a single domain, fresh from the database, so
     * any of the 7 renewal reminders can be resent from the Notification
     * Reports page regardless of whether today's date still matches the
     * reminder's configured offset.
     */
    public function buildResendPayload(int $categoryId, ?int $clientId): ?array
    {
        $domain = Capsule::table('tbldomains')->where('id', $categoryId)->first();

        if ($domain === null) {
            return null;
        }

        return [
            'client_id'           => $domain->userid,
            'report_category_id' => $domain->id,
            'domain_id'           => $domain->id,
            'domain_name'         => $domain->domain,
            'registrar'           => $domain->registrar ?: 'N/A',
            'expiry_date'         => $domain->expirydate,
            'renewal_amount'      => $domain->recurringamount,
        ];
    }
}
