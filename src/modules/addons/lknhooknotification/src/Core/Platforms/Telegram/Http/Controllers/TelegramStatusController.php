<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Http\Controllers;

use Lkn\HookNotification\Core\Platforms\Telegram\Application\TelegramLinkService;

final class TelegramStatusController
{
    /**
     * @return array{linked: bool, username: string|null, error?: string}
     */
    public function status(array $request = []): array
    {
        $clientId = (int) (\WHMCS\Session::get('uid') ?? ($_SESSION['uid'] ?? 0));

        if ($clientId <= 0) {
            return ['linked' => false, 'username' => null, 'error' => 'unauthorized'];
        }

        $link = (new TelegramLinkService())->getLink($clientId);

        if (!$link) {
            return ['linked' => false, 'username' => null];
        }

        return [
            'linked' => true,
            'username' => !empty($link->username) ? '@' . (string) $link->username : null,
        ];
    }
}
