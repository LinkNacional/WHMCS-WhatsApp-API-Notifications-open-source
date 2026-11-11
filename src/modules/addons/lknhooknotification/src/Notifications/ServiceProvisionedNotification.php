<?php

/**
 * Code: ServiceProvisioned
 */

namespace Lkn\HookNotification\Notifications\Custom;

use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameter;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameterCollection;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportCategory;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;

final class ServiceProvisionedNotification extends AbstractNotification
{
    public function __construct()
    {
        parent::__construct(
            'ServiceProvisioned',
            NotificationReportCategory::SERVICE,
            Hooks::AFTER_MODULE_CREATE,
            new NotificationParameterCollection([
                new NotificationParameter(
                    'service_id',
                    lkn_hn_lang('Service ID'),
                    fn (): int => (int) $this->whmcsHookParams['serviceid']
                ),
                new NotificationParameter(
                    'client_id',
                    lkn_hn_lang('Client ID'),
                    fn (): int => (int) $this->whmcsHookParams['userid']
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
                    'client_email',
                    lkn_hn_lang('Client email'),
                    fn (): string => getClientEmailByClientId($this->client->id)
                ),
                new NotificationParameter(
                    'product_id',
                    lkn_hn_lang('Product ID'),
                    fn (): int => (int) $this->whmcsHookParams['pid']
                ),
                new NotificationParameter(
                    'product_name',
                    lkn_hn_lang('Product name'),
                    fn (): string => getServiceProductNameByProductId((int) $this->whmcsHookParams['pid'])
                ),
                new NotificationParameter(
                    'domain',
                    lkn_hn_lang('Domain'),
                    fn (): string => !empty($this->whmcsHookParams['domain'])
                        ? (string) $this->whmcsHookParams['domain']
                        : getHostDomainByHostId((int) $this->whmcsHookParams['serviceid'])
                ),
                new NotificationParameter(
                    'username',
                    lkn_hn_lang('Service username'),
                    fn (): string => !empty($this->whmcsHookParams['username'])
                        ? (string) $this->whmcsHookParams['username']
                        : getServiceUsernameByServiceId((int) $this->whmcsHookParams['serviceid'])
                ),
                new NotificationParameter(
                    'service_status',
                    lkn_hn_lang('Service status'),
                    fn (): string => getServiceStatusByServiceId((int) $this->whmcsHookParams['serviceid'])
                ),
                new NotificationParameter(
                    'product_details_url',
                    lkn_hn_lang('Product details URL'),
                    fn (): string => getServiceProductDetailsUrlByServiceId((int) $this->whmcsHookParams['serviceid'])
                ),
            ]),
            fn (): int => (int) $this->whmcsHookParams['userid'],
            fn (): int => (int) $this->whmcsHookParams['serviceid'],
            lkn_hn_lang('Sent after the provisioning module creates the service.')
        );
    }

    public function shouldRun(): bool
    {
        return !empty($this->whmcsHookParams['serviceid'])
            && !empty($this->whmcsHookParams['userid']);
    }
}
