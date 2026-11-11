<?php

namespace Lkn\HookNotification\Core\Platforms\Common\Infrastructure;

use Lkn\HookNotification\Core\Platforms\Baileys\Domain\BaileysSettings;
use Lkn\HookNotification\Core\Platforms\Chatwoot\Domain\ChatwootSettings;
use Lkn\HookNotification\Core\Platforms\Chatwoot\Domain\LiveChatSettings;
use Lkn\HookNotification\Core\Platforms\EvolutionApi\Domain\EvolutionApiSettings;
use Lkn\HookNotification\Core\Platforms\MetaWhatsApp\Domain\MetaWhatsAppSettings;
use Lkn\HookNotification\Core\Platforms\Telegram\Domain\TelegramSettings;
use Lkn\HookNotification\Core\Platforms\Module\Domain\ModuleSettings;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;

final class PlatformSettingsFactory
{
    public static function makeBaileysSettings(array $raw): BaileysSettings
    {
        return new BaileysSettings(
            lkn_hn_config(Settings::BAILEYS_ENABLE),
            lkn_hn_config(Settings::BAILEYS_API_KEY),
            lkn_hn_config(Settings::BAILEYS_WP_CUSTOM_FIELD_ID),
            lkn_hn_config(Settings::BAILEYS_ENDPOINT_URL),
        );
    }

    public static function makeEvolutionApiSettings(array $raw): EvolutionApiSettings
    {
        return new EvolutionApiSettings(
            lkn_hn_config(Settings::WP_EVO_ENABLE),
            lkn_hn_config(Settings::WP_EVO_API_URL),
            lkn_hn_config(Settings::WP_EVO_API_KEY),
            lkn_hn_config(Settings::WP_EVO_INSTANCE_NAME),
            lkn_hn_config(Settings::WP_EVO_WP_NUMBER_CUSTOM_FIELD_ID),
        );
    }

    public static function makeMetaWhatsAppSettings(): MetaWhatsAppSettings
    {
        return new MetaWhatsAppSettings(
            lkn_hn_config(Settings::WP_META_ENABLE),
            lkn_hn_config(Settings::WP_USER_ACCESS_TOKEN),
            lkn_hn_config(Settings::WP_BUSINESS_ACCOUNT_ID),
            lkn_hn_config(Settings::WP_PHONE_NUMBER_ID),
            lkn_hn_config(Settings::WP_VERSION),
            lkn_hn_config(Settings::WP_CUSTOM_FIELD_ID),
            lkn_hn_config(Settings::WP_SHOW_INVOICE_REMINDER_BTN_WHEN_PAID),
            lkn_hn_config(Settings::WP_USE_TICKET_WHATSAPP_CF_WHEN_SET),
            lkn_hn_config(Settings::WP_MSG_TEMPLATE_LANG),
        );
    }

    public static function makeLiveChatSettings(array $raw = []): LiveChatSettings
    {
        return new LiveChatSettings(
            lkn_hn_config(Settings::CW_ENABLE_LIVE_CHAT),
            lkn_hn_config(Settings::CW_CLIENT_IDENTIFIER_KEY),
            lkn_hn_config(Settings::CW_LIVE_CHAT_USER_IDENTITY_TOKEN),
            lkn_hn_config(Settings::CW_LIVE_CHAT_SCRIPT),
            lkn_hn_config(Settings::CW_CLIENT_STATS_TO_SEND),
            lkn_hn_config(Settings::CW_CUSTOM_FIELDS_TO_SEND),
            lkn_hn_config(Settings::CW_LIVE_CHAT_MODULE_ATTRS_TO_SEND),
        );
    }

    public static function makeChatwootSettings(array $raw, LiveChatSettings $liveChatSettings, ModuleSettings $moduleSettings): ChatwootSettings
    {
        return new ChatwootSettings(
            lkn_hn_config(Settings::CW_ENABLED) ?? true,
            lkn_hn_config(Settings::CW_URL),
            lkn_hn_config(Settings::CW_API_ACCESS_TOKEN),
            lkn_hn_config(Settings::CW_WHATSAPP_INBOX_ID) ?? '0',
            lkn_hn_config(Settings::CW_FACEBOOK_INBOX_ID) ?? '0',
            lkn_hn_config(Settings::CW_PRIVATE_NOTE_MODE) ?? null,
            lkn_hn_config(Settings::CW_LISTEN_WHATSAPP) ?? null,
            lkn_hn_config(Settings::CW_WP_CUSTOM_FIELD_ID),
            $liveChatSettings,
            $moduleSettings,
            lkn_hn_config(Settings::CW_ACCOUNT_ID)
        );
    }

    public static function makeTelegramSettings(array $raw): TelegramSettings
    {
        return new TelegramSettings(
            lkn_hn_config(Settings::TELEGRAM_ENABLE),
            lkn_hn_config(Settings::TELEGRAM_BOT_TOKEN),
            lkn_hn_config(Settings::TELEGRAM_BOT_USERNAME),
            lkn_hn_config(Settings::TELEGRAM_WEBHOOK_SECRET),
            lkn_hn_config(Settings::TELEGRAM_PARSE_MODE),
            lkn_hn_config(Settings::TELEGRAM_PROXY_TYPE),
            lkn_hn_config(Settings::TELEGRAM_PROXY_HOST),
            lkn_hn_config(Settings::TELEGRAM_PROXY_PORT),
            lkn_hn_config(Settings::TELEGRAM_PROXY_USERNAME),
            lkn_hn_config(Settings::TELEGRAM_PROXY_PASSWORD),
        );
    }

    public static function makeModuleSettings(array $raw): ModuleSettings
    {
        return new ModuleSettings(
            lkn_hn_config(Settings::LANGUAGE),
            lkn_hn_config(Settings::LKN_LICENSE),
            lkn_hn_config(Settings::DEFAULT_CLIENT_NAME),
            lkn_hn_config(Settings::OBJECT_PAGES_TO_SHOW_REPORTS),
            lkn_hn_config(Settings::ENABLE_LOG),
        );
    }
}
