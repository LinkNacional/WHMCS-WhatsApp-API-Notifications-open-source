<?php

namespace Lkn\HookNotification\Core\WHMCS\SafePasswordReset;

use Lkn\HookNotification\Core\Notification\Application\Services\NotificationService;
use Lkn\HookNotification\Core\Shared\Infrastructure\I18n\I18n;
use Throwable;

final class SafePasswordResetController {
    private readonly NotificationService $notificationService;


    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function handleClientAreaPassowordReset(array $whmcsHookParams): void
    {
        try {
            if (!$this->notificationService->isNotificationEnabled('SafePasswordReset')) {
                return;
            }

            $clientLanguage = $whmcsHookParams['language'];

            $translations     = I18n::getInstance()->getTranslationsForCurrentLanguage($clientLanguage);
            $translationsJson = htmlspecialchars(json_encode($translations), ENT_QUOTES, 'UTF-8');

            $frontEndScriptUrl = moduleUrl() . '/src/Core/WHMCS/SafePasswordReset/safe_password_reset.js';

            // Fase 1 (hardening): expose the WHMCS client area CSRF token to the JS
            // so it can POST it back; api.php validates it via check_token().
            $csrfToken = function_exists('generate_token') ? generate_token() : '';
            $csrfToken = htmlspecialchars((string) $csrfToken, ENT_QUOTES, 'UTF-8');

            echo "<script
            async
            fetchpriority='high'
            referrerpolicy='origin'
            type='text/javascript'
            src='{$frontEndScriptUrl}'
            data-translations='{$translationsJson}'
            data-csrf-token='{$csrfToken}'>
        </script>";
        } catch (Throwable $th) {
            lkn_hn_log(
                'handleClientAreaPassowordReset exception',
                ['error' => $th->__toString()]
            );
        }
    }
}
