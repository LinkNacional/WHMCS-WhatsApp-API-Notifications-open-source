<?php

use Lkn\HookNotification\Core\WHMCS\Login\LoginPageController;
use Lkn\HookNotification\Core\WHMCS\SafePasswordReset\SafePasswordResetController;

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
