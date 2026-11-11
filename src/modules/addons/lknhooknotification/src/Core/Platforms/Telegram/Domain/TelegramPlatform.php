<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Domain;

use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Domain\NotificationTemplate;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportStatus;
use Lkn\HookNotification\Core\Platforms\Common\AbstractPlatform;
use Lkn\HookNotification\Core\Platforms\Common\PlatformNotificationSendResult;
use Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure\TelegramApiClient;
use Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure\TelegramLinkRepository;

final class TelegramPlatform extends AbstractPlatform
{
    public function sendNotification(
        AbstractNotification $notification,
        NotificationTemplate $template,
    ): PlatformNotificationSendResult {
        /** @var TelegramSettings $settings */
        $settings = $this->platformSettings;

        if (!$settings->enabled) {
            return new PlatformNotificationSendResult(
                NotificationReportStatus::NOT_SENT,
                'The platform is disabled.'
            );
        }

        $linkRepository = new TelegramLinkRepository();
        $link           = $linkRepository->findByClientId($notification->client->id);

        if (!$link) {
            return new PlatformNotificationSendResult(
                NotificationReportStatus::NOT_SENT,
                'telegram-not-linked'
            );
        }

        $chatId = (string) $link->chat_id;
        $target = self::maskChatId($chatId);
        $text   = $notification->fillTemplate($template);

        /** @var TelegramApiClient $apiClient */
        $apiClient = $this->apiClient;
        $response  = $apiClient->sendMessage($chatId, $text, $settings->parseMode);

        $ok = is_array($response->body) && ($response->body['ok'] ?? false) === true;

        if (!$ok) {
            $error = is_array($response->body)
                ? (string) ($response->body['description'] ?? json_encode($response->body))
                : (string) $response->body;

            $linkRepository->setLastError($notification->client->id, $error);

            lkn_hn_log(
                "{$template->platform->value}: api error",
                [
                    'notification' => $notification->code,
                    'target' => $target,
                ],
                [
                    'http_code' => $response->httpStatusCode,
                    'error' => $error,
                ]
            );

            return new PlatformNotificationSendResult(
                NotificationReportStatus::ERROR,
                $error,
                $target
            );
        }

        $linkRepository->setLastError($notification->client->id, null);

        return new PlatformNotificationSendResult(
            NotificationReportStatus::SENT,
            'The notification was sent.',
            $target
        );
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
