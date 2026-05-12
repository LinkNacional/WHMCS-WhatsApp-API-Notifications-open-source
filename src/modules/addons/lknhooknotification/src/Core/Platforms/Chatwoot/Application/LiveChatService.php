<?php

namespace Lkn\HookNotification\Core\Platforms\Chatwoot\Application;

use Lkn\HookNotification\Core\Platforms\Chatwoot\Domain\LiveChatSettings;
use Lkn\HookNotification\Core\Platforms\Common\Infrastructure\PlatformSettingsFactory;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;
use WHMCS\User\Client;

final class LiveChatService
{
    private readonly Client $signedInClient;
    private readonly View $view;
    private readonly LiveChatSettings $liveChatSettings;

    /**
     * @var array<mixed>
     */
    private readonly array $whmcsHookParams;

    /**
     * @param array<string, mixed> $whmcsHookParams
     */
    public function __construct(array $whmcsHookParams)
    {
        /** @var Client $client */
        $client = $whmcsHookParams['client'];

        $this->signedInClient = $client;
        $this->view = new View();
        $this->view->setTemplateDir(__DIR__ . '/../Http/Views');
        $this->liveChatSettings = PlatformSettingsFactory::makeLiveChatSettings();
        $this->whmcsHookParams = $whmcsHookParams;
    }

    public function handle(): string
    {
        if (
            ! $this->liveChatSettings->enableLiveChat ||
            ! $this->liveChatSettings->userIdentityValidation
        ) {
            return '';
        }

        [$clientIdentifierKey, $identifierHash] = self::makeIdentifierHash(
            $this->signedInClient->id,
            $this->liveChatSettings->userIdentityValidation
        );

        return $this->view->view(
            'live_chat',
            [
                'messenger_script' => $this->liveChatSettings->liveChatScript,
                'client_identifier_key' => $clientIdentifierKey,
                'identifier_hash' => $identifierHash,
                'client_details' => $this->getClientDetailsForLiveChat(),
                'custom_attrs_script' => json_encode(
                    $this->generateCustomAttrsSetterScript(),
                    JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_PRETTY_PRINT
                ),
            ]
        )->render();
    }

    /**
     * @return array{
     *   locale: string,
     *   name: string,
     *   email: string,
     *   phone_number: string,
     *   country_code: string,
     *   city: string,
     *   company_name: string,
     * }
     */
    private function getClientDetailsForLiveChat(): array
    {
        $clientLocale = '';

        foreach (($this->whmcsHookParams['locales'] ?? []) as $locale) {
            if (
                is_array($locale) &&
                isset($locale['language'], $locale['languageCode']) &&
                $locale['language'] === $this->signedInClient->language
            ) {
                $clientLocale = (string) $locale['languageCode'];
                break;
            }
        }

        $firstName = (string) ($this->whmcsHookParams['client']['firstname'] ?? '');
        $lastName = (string) ($this->whmcsHookParams['client']['lastname'] ?? '');
        $email = (string) ($this->whmcsHookParams['client']['email'] ?? '');
        $phoneNumber = (string) ($this->whmcsHookParams['client']['phonenumber'] ?? '');
        $countryCode = (string) ($this->whmcsHookParams['client']['country'] ?? '');
        $city = (string) ($this->whmcsHookParams['client']['city'] ?? '');
        $companyName = (string) ($this->whmcsHookParams['client']['companyname'] ?? '');

        return [
            'locale' => $clientLocale,
            'name' => lkn_hn_normalize_person_name(trim($firstName . ' ' . $lastName)),
            'email' => $email,
            'phone_number' => '+' . lkn_hn_remove_phone_number($phoneNumber),
            'country_code' => $countryCode,
            'city' => $city,
            'company_name' => lkn_hn_normalize_person_name($companyName),
        ];
    }

    private function generateCustomAttrsSetterScript(): array
    {
        /** @var array<string> $clientStatsToSend */
        $clientStatsToSend = lkn_hn_config(Settings::CW_CLIENT_STATS_TO_SEND);

        /** @var array<int> $customFieldsToSend */
        $customFieldsToSend = lkn_hn_config(Settings::CW_CUSTOM_FIELDS_TO_SEND);

        /** @var array<string> $selectedAdditionalCustomFields */
        $selectedAdditionalCustomFields = lkn_hn_config(Settings::CW_LIVE_CHAT_MODULE_ATTRS_TO_SEND);

        /** @var array<string, string> $customAttrs */
        $customAttrs = [];

        if (count($customFieldsToSend) > 0) {
            $customFields = lkn_hn_get_client_custom_fields_for_view();

            /** @var array<array{id: int, value: string}> $clientCustomFields */
            $clientCustomFields = $this->whmcsHookParams['clientsdetails']['customfields'] ?? [];

            foreach ($customFields as $customField) {
                if (! isset($customField['value'], $customField['label'])) {
                    continue;
                }

                $customFieldId = $customField['value'];

                if (! in_array($customFieldId, $customFieldsToSend, false)) {
                    continue;
                }

                $customFieldKey = strtolower(str_replace(' ', '_', (string) $customField['label'])) . '_' . $customFieldId;
                $customFieldValue = '';

                foreach ($clientCustomFields as $item) {
                    if (
                        is_array($item) &&
                        isset($item['id'], $item['value']) &&
                        (int) $item['id'] === (int) $customFieldId
                    ) {
                        $customFieldValue = (string) $item['value'];
                        break;
                    }
                }

                if ($customFieldValue === '') {
                    continue;
                }

                $customAttrs[$customFieldKey] = $customFieldValue;
            }
        }

        if (count($clientStatsToSend) > 0) {
            $clientDetails = localAPI(
                'GetClientsDetails',
                [
                    'clientid' => $this->signedInClient->id,
                    'stats' => true,
                ]
            );

            $clientStats = is_array($clientDetails['stats'] ?? null) ? $clientDetails['stats'] : [];

            foreach ($clientStatsToSend as $statsKey) {
                if (! array_key_exists($statsKey, $clientStats)) {
                    continue;
                }

                $statsValue = $clientStats[$statsKey];
                $statsValue = $statsValue instanceof \WHMCS\View\Formatter\Price
                    ? $statsValue->toPrefixed()
                    : $statsValue;

                if ($statsValue !== null && $statsValue !== '') {
                    $customAttrs[$statsKey] = (string) $statsValue;
                }
            }
        }

        if (count($selectedAdditionalCustomFields) > 0) {
            /** @var array<string> $additionalAttrsFields */
            $additionalAttrsFields = (require __DIR__ . '/../Infrastructure/constants.php')['module_attrs_options'] ?? [];

            foreach ($selectedAdditionalCustomFields as $attr) {
                if (! in_array($attr, $additionalAttrsFields, true)) {
                    continue;
                }

                $clientId = $this->signedInClient->id;

                $attrsValue = match ($attr) {
                    'client_initial_acessed_page' => $this->getCurrentRequestUrl(),
                    'client_profile_url' => lkn_hn_get_admin_root_url("clientssummary.php?userid=$clientId"),
                    'client_tickets_url' => lkn_hn_get_admin_root_url("client/$clientId/tickets"),
                    'client_invoices_url' => lkn_hn_get_admin_root_url("clientsinvoices.php?userid=$clientId"),
                    default => '',
                };

                if ($attrsValue === '') {
                    continue;
                }

                $customAttrs[$attr] = $attrsValue;
            }
        }

        return $customAttrs;
    }

    private function getCurrentRequestUrl(): string
    {
        $httpHost = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

        if ($httpHost === '' || $requestUri === '') {
            return '';
        }

        $scheme = empty($_SERVER['HTTPS']) ? 'http' : 'https';

        return $scheme . '://' . $httpHost . $requestUri;
    }

    /**
     * @see https://www.chatwoot.com/hc/user-guide/articles/1677587234-how-to-send-additional-user-information-to-chatwoot-using-sdk
     *
     * @param int $clientId
     * @param string $userIdentifyValidation
     *
     * @return array{0: string, 1: string}
     */
    private static function makeIdentifierHash(
        int $clientId,
        string $userIdentifyValidation
    ): array {
        $clientIdentifierKey = hash_hmac(
            'sha256',
            (string) $clientId,
            $userIdentifyValidation
        );

        $identifierHash = hash_hmac(
            'sha256',
            $clientIdentifierKey,
            $userIdentifyValidation
        );

        return [$clientIdentifierKey, $identifierHash];
    }
}
