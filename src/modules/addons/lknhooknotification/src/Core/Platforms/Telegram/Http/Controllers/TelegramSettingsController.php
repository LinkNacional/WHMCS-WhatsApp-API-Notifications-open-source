<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Http\Controllers;

use Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure\TelegramApiClient;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use Lkn\HookNotification\Core\Shared\Infrastructure\Interfaces\BaseController;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;
use WHMCS\Database\Capsule;

final class TelegramSettingsController extends BaseController
{
    public function __construct()
    {
        parent::__construct(new View());
    }

    public function handle(array $request): string
    {
        $botToken = (string) lkn_hn_config(Settings::TELEGRAM_BOT_TOKEN);
        $secret   = (string) lkn_hn_config(Settings::TELEGRAM_WEBHOOK_SECRET);
        $client   = new TelegramApiClient($botToken);

        if (lkn_hn_is_post_request() && isset($request['telegram-action'])) {
            $action = (string) $request['telegram-action'];

            if ($action === 'test-bot') {
                $response = $client->getMe();
                $this->alertFromResponse($response);
            } elseif ($action === 'set-webhook') {
                if ($secret === '') {
                    $this->view->alert('danger', lkn_hn_lang('Configure the Webhook Secret before setting the webhook.'));
                } else {
                    $response = $client->setWebhook($this->webhookUrl(), $secret);
                    $this->alertFromResponse($response);
                }
            } elseif ($action === 'webhook-info') {
                $response = $client->getWebhookInfo();
                $this->alertFromResponse($response);
            } elseif ($action === 'delete-webhook') {
                $response = $client->deleteWebhook();
                $this->alertFromResponse($response);
            }
        }

        $status = [
            'token' => $botToken !== '',
            'username' => (string) lkn_hn_config(Settings::TELEGRAM_BOT_USERNAME) !== '',
            'secret' => $secret !== '',
            'enabled' => (bool) lkn_hn_config(Settings::TELEGRAM_ENABLE),
        ];

        return $this->view->view('telegram_settings_actions', [
            'webhook_url' => $this->webhookUrl(),
            'status' => $status,
        ])->render();
    }

    private function webhookUrl(): string
    {
        $systemUrl = (string) Capsule::table('tblconfiguration')
            ->where('setting', 'SystemURL')
            ->value('value');

        return rtrim($systemUrl, '/')
            . '/modules/addons/lknhooknotification/src/Core/telegram_webhook.php';
    }

    private function alertFromResponse($response): void
    {
        $ok = is_array($response->body) && ($response->body['ok'] ?? false) === true;

        $this->view->alert(
            $ok ? 'success' : 'danger',
            $ok
                ? lkn_hn_lang('Telegram API request completed.')
                : lkn_hn_lang(
                    'Telegram API request failed: [1]',
                    [is_array($response->body) ? (string) ($response->body['description'] ?? '') : '']
                )
        );
    }
}
