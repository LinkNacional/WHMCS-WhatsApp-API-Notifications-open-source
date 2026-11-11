<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Domain;

use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Domain\AbstractNotificationParser;
use Lkn\HookNotification\Core\Notification\Domain\NotificationTemplate;
use Lkn\HookNotification\Core\Shared\Infrastructure\BaseApiClient;
use Lkn\HookNotification\Core\Shared\Infrastructure\Result;

final class TelegramNotificationParser extends AbstractNotificationParser
{
    public function parse(
        AbstractNotification $notification,
        NotificationTemplate $template,
        ?BaseApiClient $apiClient = null,
    ): array|Result {
        return [];
    }
}
