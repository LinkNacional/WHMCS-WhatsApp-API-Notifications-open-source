<?php

namespace Lkn\HookNotification\Core\Shared\Infrastructure\Repository;

use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use WHMCS\Language\ClientLanguage;

final class ClientRepository extends BaseRepository
{
    public function getCustomField(int $clientId, int $customFieldId): ?string
    {
        return $this->query->table('tblcustomfieldsvalues')
            ->where('relid', $clientId)
            ->where('fieldid', $customFieldId)
            ->first('value')
            ->value;
    }

    public function getWhmcsPhoneNumber(int $clientId): ?string
    {
        return $this->query->table('tblclients')
            ->where('id', $clientId)
            ->first('phonenumber')
            ->phonenumber;
    }

    public function getClientCountry(int $clientId): ?string
    {
        return $this->query->table('tblclients')
            ->where('id', $clientId)
            ->first('country')
            ->country;
    }

    /**
     * @param  integer $clientId
     *
     * @return array{locale: string, langCode: string}
     */
    public function getClientLang(int $clientId): array
    {
        $clientLangInWhmcsFormat = $this->query
            ->table('tblclients')
            ->where('id', $clientId)
            ->first('language')
            ->language;

        /**
         * @var array (
         *     [locale] => en_GB
         *     [language] => english
         *     [languageCode] => en
         *     [countryCode] => GB
         *     [localisedName] => English
         * )[] $clientLocalesList
         */
        $clientLocalesList = ClientLanguage::getLocales();

        if (!$clientLangInWhmcsFormat) {
            $parsedClientLang = current(
                array_filter(
                    $clientLocalesList,
                    fn(array $item): bool =>
                    $item['locale'] === lkn_hn_config(Settings::WP_MSG_TEMPLATE_LANG)
                )
            );

            return [
                'locale' => $parsedClientLang['locale'] ?? 'pt_BR',
                'langCode' => $parsedClientLang['languageCode'],
            ];
        }

        $parsedClientLang = current(
            array_filter(
                $clientLocalesList,
                fn ($item) => $item['language'] === $clientLangInWhmcsFormat
            )
        );

        return [
            'locale' => $parsedClientLang['locale'] ?? 'pt_BR',
            'langCode' => $parsedClientLang['languageCode'],
        ];
    }
}
