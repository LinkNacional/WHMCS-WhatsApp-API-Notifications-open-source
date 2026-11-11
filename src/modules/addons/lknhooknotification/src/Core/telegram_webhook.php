<?php

use Lkn\HookNotification\Core\Platforms\Telegram\Application\TelegramHandoffService;
use Lkn\HookNotification\Core\Platforms\Telegram\Application\TelegramLinkService;
use Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure\TelegramApiClient;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../../../../init.php';
require_once __DIR__ . '/Shared/Infrastructure/helpers.php';

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit;
    }

    $expectedSecret = (string) lkn_hn_config(Settings::TELEGRAM_WEBHOOK_SECRET);
    $receivedSecret = (string) ($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '');

    if ($expectedSecret === '' || !hash_equals($expectedSecret, $receivedSecret)) {
        http_response_code(403);
        exit;
    }

    $raw    = file_get_contents('php://input');
    $update = json_decode((string) $raw, true);
    $message = is_array($update) ? ($update['message'] ?? null) : null;

    if (!is_array($message) || ($message['chat']['type'] ?? '') !== 'private') {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    $chatId   = (string) ($message['chat']['id'] ?? '');
    $username = isset($message['from']['username']) && is_string($message['from']['username'])
        ? $message['from']['username']
        : null;
    $text = trim((string) ($message['text'] ?? ''));

    $apiClient   = new TelegramApiClient((string) lkn_hn_config(Settings::TELEGRAM_BOT_TOKEN));
    $linkService = new TelegramLinkService();

    if (str_starts_with($text, '/start ')) {
        $token  = trim(substr($text, 7));
        $result = $linkService->handleStart($token, $chatId, $username);

        if ($result['ok']) {
            $successMessage = trim((string) lkn_hn_config(Settings::TELEGRAM_LINK_SUCCESS_MESSAGE));

            if ($successMessage === '') {
                $successMessage = 'Conta vinculada com sucesso ao WHMCS!';
            }

            $systemUrl = rtrim((string) \WHMCS\Database\Capsule::table('tblconfiguration')
                ->where('setting', 'SystemURL')
                ->value('value'), '/');

            $returnKeyboard = null;

            if ($systemUrl !== '') {
                $returnKeyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Voltar ao WHMCS',
                                'url' => $systemUrl . '/clientarea.php?action=details',
                            ],
                        ],
                    ],
                ];
            }

            $apiClient->sendMessage($chatId, $successMessage, null, $returnKeyboard);
        } else {
            $apiClient->sendMessage($chatId, 'Nao foi possivel vincular. Gere um novo link no seu perfil.');
        }
    } elseif ($text === '/stop' || $text === '/desconectar') {
        $linkService->unlinkByChatId($chatId);
        $apiClient->sendMessage($chatId, 'Notificacoes do Telegram desconectadas.');
    } elseif ($text !== '' && !str_starts_with($text, '/')) {
        $handoff = new TelegramHandoffService();

        if ($handoff->isAutoReplyEnabled()) {
            $apiClient->sendMessage(
                $chatId,
                $handoff->getAutoReplyText(),
                null,
                $handoff->buildInlineKeyboard(true)
            );
        }
    }

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
} catch (Throwable $th) {
    lkn_hn_log(
        'Telegram webhook error',
        [],
        ['error' => $th->getMessage()]
    );

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false]);
}
