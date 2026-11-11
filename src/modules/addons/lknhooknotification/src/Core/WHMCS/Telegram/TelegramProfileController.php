<?php

namespace Lkn\HookNotification\Core\WHMCS\Telegram;

use Lkn\HookNotification\Core\Platforms\Telegram\Application\TelegramLinkService;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;

final class TelegramProfileController
{
    public function handleFooter(array $vars): ?string
    {
        if (!$this->isProfilePage($vars) || !lkn_hn_config(Settings::TELEGRAM_ENABLE)) {
            return null;
        }

        $clientId = $this->clientId($vars);

        if (!$clientId) {
            return null;
        }

        $service = new TelegramLinkService();

        if (lkn_hn_is_post_request() && isset($_POST['lkn_telegram_unlink'])) {
            if (function_exists('check_token') && check_token()) {
                $service->unlinkByClientId($clientId);
            }

            return '<script>window.location.href="clientarea.php?action=details";</script>';
        }

        $link = $service->getLink($clientId);

        if ($link) {
            $username = !empty($link->username) ? '@' . (string) $link->username : '';
            $chatId   = $this->maskChatId((string) $link->chat_id);
            $token    = function_exists('generate_token') ? (string) generate_token() : '';

            $html = '<div class="alert alert-success" style="margin-top:20px;">'
                . '<h4>Notificacoes via Telegram</h4>'
                . '<p>Conectado ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8')
                . ' (' . htmlspecialchars($chatId, ENT_QUOTES, 'UTF-8') . ')</p>'
                . '<form method="post" action="clientarea.php?action=details">'
                . '<input type="hidden" name="token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">'
                . '<input type="hidden" name="lkn_telegram_unlink" value="1">'
                . '<button type="submit" class="btn btn-default">Desconectar Telegram</button>'
                . '</form></div>';
        } else {
            $token = $service->generateToken($clientId);
            $url   = $token ? $service->buildDeepLink($token) : null;

            if (!$url) {
                return null;
            }

            $html = '<div class="alert alert-info" style="margin-top:20px;">'
                . '<h4>Notificacoes via Telegram</h4>'
                . '<p>Vincule sua conta do Telegram para receber notificacoes por la tambem.</p>'
                . '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" class="btn btn-primary">'
                . 'Vincular Telegram</a></div>';
        }

        $encoded = json_encode($html, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        return '<script>document.addEventListener("DOMContentLoaded",function(){'
            . 'var h=' . $encoded . ';'
            . 'var t=document.querySelector("#frmMyDetails")||document.querySelector(".client-details")||document.querySelector("#main-body");'
            . 'if(t){t.insertAdjacentHTML("beforeend",h);}'
            . '});</script>';
    }

    public function handleClientDelete(array $vars): void
    {
        $clientId = (int) ($vars['userid'] ?? $vars['client_id'] ?? 0);

        if ($clientId > 0) {
            (new TelegramLinkService())->unlinkByClientId($clientId);
        }
    }

    private function isProfilePage(array $vars): bool
    {
        return ($vars['filename'] ?? '') === 'clientarea'
            && ($vars['action'] ?? '') === 'details';
    }

    private function clientId(array $vars): ?int
    {
        $clientId = $vars['clientsdetails']['userid'] ?? $vars['userid'] ?? null;

        return $clientId ? (int) $clientId : null;
    }

    private function maskChatId(string $chatId): string
    {
        $length = strlen($chatId);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($chatId, 0, 3) . str_repeat('*', max(2, $length - 5)) . substr($chatId, -2);
    }
}
