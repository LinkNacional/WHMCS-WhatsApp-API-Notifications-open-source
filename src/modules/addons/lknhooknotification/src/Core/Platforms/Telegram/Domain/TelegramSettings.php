<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Domain;

use Lkn\HookNotification\Core\Platforms\Common\AbstractPlatformSettings;

final class TelegramSettings extends AbstractPlatformSettings
{
    public function __construct(
        public readonly ?bool $enabled,
        public readonly ?string $botToken,
        public readonly ?string $botUsername,
        public readonly ?string $webhookSecret,
        public readonly ?string $parseMode,
        public readonly ?string $proxyType = null,
        public readonly ?string $proxyHost = null,
        public readonly ?string $proxyPort = null,
        public readonly ?string $proxyUsername = null,
        public readonly ?string $proxyPassword = null,
    ) {
        parent::__construct(null);
    }
}
