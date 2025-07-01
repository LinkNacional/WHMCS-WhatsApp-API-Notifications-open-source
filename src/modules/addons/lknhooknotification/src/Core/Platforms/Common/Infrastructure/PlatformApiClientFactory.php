<?php

namespace Lkn\HookNotification\Core\Platforms\Common\Infrastructure;

use Lkn\HookNotification\Core\Platforms\Baileys\BaileysApiClient;
use Lkn\HookNotification\Core\Platforms\Baileys\Domain\BaileysSettings;
use Lkn\HookNotification\Core\Platforms\Chatwoot\Domain\ChatwootSettings;
use Lkn\HookNotification\Core\Platforms\Chatwoot\Infrastructure\ChatwootApiClient;
use Lkn\HookNotification\Core\Platforms\EvolutionApi\Domain\EvolutionApiSettings;
use Lkn\HookNotification\Core\Platforms\EvolutionApi\Infrastructure\EvolutionApiClient;
use Lkn\HookNotification\Core\Platforms\MetaWhatsApp\Domain\MetaWhatsAppSettings;
use Lkn\HookNotification\Core\Platforms\MetaWhatsApp\Infrastructure\MetaWhatsAppApiClient;

final class PlatformApiClientFactory
{
    public function makeBaileysClient(BaileysSettings $settings): BaileysApiClient
    {
        return new BaileysApiClient($settings->endpoint, $settings->apiToken);
    }

    public function makeEvolutionApiClient(EvolutionApiSettings $settings): EvolutionApiClient
    {
        return new EvolutionApiClient($settings->apiUrl, $settings->apiKey);
    }

    public function makeMetaWhatsAppClient(MetaWhatsAppSettings $settings): MetaWhatsAppApiClient
    {
        return new MetaWhatsAppApiClient(
            $settings->apiVersion,
            $settings->phoneNumberId,
            $settings->userAccessToken,
            $settings->businessAccountId,
        );
    }

    public function makeChatwootClient(ChatwootSettings $settings): ChatwootApiClient
    {
        return new ChatwootApiClient(
            $settings->accountId,
            $settings->url,
            $settings->apiAccessToken,
        );
    }
}
