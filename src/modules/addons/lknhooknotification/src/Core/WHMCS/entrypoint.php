<?php

use Lkn\HookNotification\Core\WHMCS\Login\LoginPageController;
use Lkn\HookNotification\Core\WHMCS\SafePasswordReset\SafePasswordResetController;

use Lkn\HookNotification\Core\WHMCS\Telegram\TelegramProfileController;

add_hook(
    'ClientAreaPagePasswordReset',
    1,
    fn (array $whmcsHookParams) =>  (new SafePasswordResetController())->handleClientAreaPassowordReset($whmcsHookParams)
);

add_hook(
    'ClientAreaPageLogin',
    1,
    fn (array $whmcsHookParams) =>  (new LoginPageController())->handleClientAreaLogin($whmcsHookParams)
);

add_hook(
    'ClientAreaFooterOutput',
    1,
    function (array $whmcsHookParams) {
        try {
            return (new TelegramProfileController())->handleFooter($whmcsHookParams);
        } catch (\Throwable $th) {
            lkn_hn_log('Telegram profile error', [], ['error' => $th->getMessage()]);

            return null;
        }
    }
);

add_hook(
    'ClientDelete',
    1,
    function (array $whmcsHookParams): void {
        try {
            (new TelegramProfileController())->handleClientDelete($whmcsHookParams);
        } catch (\Throwable $th) {
            lkn_hn_log('Telegram client delete error', [], ['error' => $th->getMessage()]);
        }
    }
);
