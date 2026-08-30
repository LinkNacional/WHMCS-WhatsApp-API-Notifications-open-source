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

    /**
     * Reverse lookup: find clients whose WHMCS phone number matches the given
     * phone (normalized to digits only).
     *
     * @return array<int, object{id: int, email: string, phonenumber: string}>
     */
    public function getClientsByPhone(string $phone): array
    {
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        if ($normalized === '') {
            return [];
        }

        $matches = [];

        // 1. Campo de telefone do WHMCS (tblclients.phonenumber).
        $byWhmcsPhone = $this->query->table('tblclients')
            ->select(['id', 'email', 'phonenumber'])
            ->get()
            ->filter(function ($client) use ($normalized): bool {
                return preg_replace('/[^0-9]/', '', (string) ($client->phonenumber ?? '')) === $normalized;
            });

        foreach ($byWhmcsPhone as $client) {
            $matches[(int) $client->id] = $client;
        }

        // 2. Campo personalizado de WhatsApp (tblcustomfieldsvalues) pelo CF configurado.
        $wpCustomFieldId = lkn_hn_config(Settings::WP_CUSTOM_FIELD_ID);

        if ($wpCustomFieldId) {
            $byCustomField = $this->query->table('tblcustomfieldsvalues')
                ->where('fieldid', $wpCustomFieldId)
                ->select(['relid', 'value'])
                ->get()
                ->filter(function ($row) use ($normalized): bool {
                    return preg_replace('/[^0-9]/', '', (string) ($row->value ?? '')) === $normalized;
                });

            foreach ($byCustomField as $row) {
                $clientId = (int) $row->relid;

                if (isset($matches[$clientId])) {
                    continue;
                }

                $client = $this->query->table('tblclients')
                    ->where('id', $clientId)
                    ->select(['id', 'email', 'phonenumber'])
                    ->first();

                if ($client) {
                    $matches[$clientId] = $client;
                }
            }
        }

        return array_values($matches);
    }
}
