<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Http\Controllers;

use Lkn\HookNotification\Core\Platforms\Telegram\Application\TelegramLinkService;
use Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure\TelegramLinkRepository;
use Lkn\HookNotification\Core\Shared\Infrastructure\Interfaces\BaseController;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;
use WHMCS\Database\Capsule;

final class TelegramLinksController extends BaseController
{
    public function __construct(View $view)
    {
        parent::__construct($view);

        // The links page uses the shared admin layout, not the platform views.
        $this->view->setTemplateDir(dirname(__DIR__, 4) . '/AdminUI/Http/Views');
    }

    public function viewLinks(array $request): void
    {
        $service = new TelegramLinkService();
        $repo    = new TelegramLinkRepository();

        if (lkn_hn_is_post_request() && isset($request['unlink-telegram'])) {
            $clientId = (int) ($request['client-id'] ?? 0);

            if ($clientId > 0) {
                $service->unlinkByClientId($clientId);
                $this->view->alert('success', lkn_hn_lang('Telegram link removed.'));
            }
        }

        $rows = [];

        foreach ($repo->all() as $link) {
            $client = Capsule::table('tblclients')
                ->where('id', $link->client_id)
                ->first(['firstname', 'lastname', 'email']);

            $rows[] = [
                'client_id' => (int) $link->client_id,
                'client_name' => trim(($client->firstname ?? '') . ' ' . ($client->lastname ?? '')),
                'email' => $client->email ?? '',
                'chat_id' => self::maskChatId((string) $link->chat_id),
                'username' => !empty($link->username) ? '@' . (string) $link->username : '',
                'linked_at' => (string) $link->linked_at,
            ];
        }

        $this->view->view('telegram_links', [
            'links' => $rows,
        ]);
    }

    private static function maskChatId(string $chatId): string
    {
        $length = strlen($chatId);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($chatId, 0, 3) . str_repeat('*', max(2, $length - 5)) . substr($chatId, -2);
    }
}
