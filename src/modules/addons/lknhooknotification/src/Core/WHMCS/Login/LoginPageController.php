<?php

namespace Lkn\HookNotification\Core\WHMCS\Login;

use Lkn\HookNotification\Core\Notification\Application\Services\NotificationService;
use Lkn\HookNotification\Core\Shared\Infrastructure\I18n\I18n;
use Throwable;

final class LoginPageController
{
    private readonly NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function handleClientAreaLogin(array $whmcsHookParams): void
    {
        try {
            // Fase 3: só injeta o fluxo se a notificação "Login OTP" estiver habilitada.
            if (!$this->notificationService->isNotificationEnabled('LoginOtp')) {
                return;
            }

            $clientLanguage = $whmcsHookParams['language'] ?? 'english';

            $translations     = I18n::getInstance()->getTranslationsForCurrentLanguage($clientLanguage);
            $translationsJson = htmlspecialchars(json_encode($translations), ENT_QUOTES, 'UTF-8');

            $frontEndScriptUrl = moduleUrl() . '/src/Core/WHMCS/Login/login_otp.js';

            // Expõe o token CSRF do client area para o JS (api.php valida via check_token()).
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
            lkn_hn_log('handleClientAreaLogin exception', ['error' => $th->__toString()]);
        }
    }
}
