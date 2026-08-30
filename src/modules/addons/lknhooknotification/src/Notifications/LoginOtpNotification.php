<?php

namespace Lkn\HookNotification\Notifications\Custom;

use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameter;
use Lkn\HookNotification\Core\Notification\Domain\NotificationParameterCollection;

final class LoginOtpNotification extends AbstractNotification
{
    public function __construct()
    {
        parent::__construct(
            'LoginOtp',
            null,
            null,
            new NotificationParameterCollection([
                new NotificationParameter(
                    'otp_code',
                    lkn_hn_lang('OTP code'),
                    fn (): string => (string) ($this->whmcsHookParams['otp_code'] ?? '')
                ),
                new NotificationParameter(
                    'magic_link_url',
                    lkn_hn_lang('Magic link URL'),
                    fn (): string => (string) ($this->whmcsHookParams['magic_link_url'] ?? '')
                ),
                new NotificationParameter(
                    'expiry_minutes',
                    lkn_hn_lang('Expiry minutes'),
                    fn (): int => (int) ($this->whmcsHookParams['expiry_minutes'] ?? 0)
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
            ]),
            fn () => $this->whmcsHookParams['client_id'],
            description: lkn_hn_lang('Passwordless login via WhatsApp OTP (single-use code) and optional magic link.'),
        );
    }
}
