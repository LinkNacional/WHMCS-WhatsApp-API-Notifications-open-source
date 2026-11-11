<?php

use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use Lkn\HookNotification\Core\Shared\Infrastructure\I18n\I18n;
use Lkn\HookNotification\Core\Shared\Infrastructure\Result;
use WHMCS\Database\Capsule;
use WHMCS\Language\ClientLanguage;
use WHMCS\Utility\Country;

/**
 * @return array{
 *     label: string,
 *     locale: string,
 *     locale_expanded: string,
 *     country_code: string
 * }
 */
function lkn_hn_get_language_locales_for_view(): array
{
    $whmcsLocales = ClientLanguage::getLocales();

    $result = [];

    foreach ($whmcsLocales as $item) {
        $label = $item['localisedName'];

        if ($item['locale'] === 'pt_BR') {
            $label .= ' (BR)';
        }

        $result[] = [
            'label' => $label,
            'value' => $item['locale'],
            'locale_expanded' => $item['language'],
            'country_code' => $item['countryCode'],
        ];
    }

    return $result;
}

function lkn_hn_get_client_countries_for_view()
{
    $countries = (new Country())->getCountries();

    return array_map(
        function (string $countryCode, array $item): array {
            return [
                'value' => $countryCode,
                'label' => $item['name'],
            ];
        },
        array_keys($countries),
        $countries,
    );
}

function lkn_hn_get_products_for_view(): array
{
    $products = Capsule::table('tblproducts')
        ->get(['id as value', 'name as label']);

    return array_map(function ($item) {
        return (array) $item;
    }, $products->toArray());
}

/**
 * @return array<array{label: string, value: string}>
 */
function lkn_hn_get_client_custom_fields_for_view(): array
{
    $query  = Capsule::table('tblcustomfields')->where('adminonly', '');
    $result = $query->get(['id as value', 'fieldname as label']);

    if (is_array($result)) {
        throw new Exception('Unable to retrieve custom fields');
    }

    return array_map(
        fn ($item) => (array) $item,
        $result->toArray()
    );
}

function define_i18n_lang()
{
    $language = lkn_hn_config(Settings::LANGUAGE);

    if (!$language) {
        $language = $language = Capsule::table('tblconfiguration')
            ->where('setting', 'Language')
            ->first('value')->value;
    }

    if (!in_array($language, ['english', 'portugues-br', 'portugues-pt'], true)) {
        $language = 'english';
    }

    return $language;
}

I18n::getInstance()::load(define_i18n_lang());


/**
 * This should work for both PHP and Smarty templates.
 *
 * @param  array|string $text
 *
 * @return string returns $text if it is not found on the current language.
 */
function lkn_hn_lang(array|string $text, array|Smarty_Internal_Template $params = []): string
{
    $key = is_array($text) ? ($text['text'] ?? '') : $text;

    if (empty($key)) {
        return '[empty]';
    }

    $translated = I18n::getInstance()->get($key);

    $params = is_array($text) ? ($text['params'] ?? []) : $params;

    if (!is_iterable($params)) {
        $params = [];
    }

    foreach ($params as $key => $value) {
        $key_       = $key;
        $key_      += 1;
        $translated = str_replace("[{$key_}]", $value, $translated);
    }

    return $translated;
}

function lkn_hn_log(
    string $action,
    array|object|string|null $request,
    array|object|string|null $response = '',
    array $masks = []
) {
    if (!lkn_hn_config(Settings::ENABLE_LOG)) {
        return;
    }

    $request = (array) $request;
    $request = empty($request) ? '' : json_encode($request, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $response = is_string($response) ? $response : json_encode((array) ($response), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    logModuleCall(
        'lknhooknotification',
        $action,
        $request,
        $response,
        '',
        $masks
    );
}

function lkn_hn_config(Settings $setting)
{
    if (!Capsule::schema()->hasTable('mod_lkn_hook_notification_configs')) {
        return null;
    }

    $value = Capsule::table('mod_lkn_hook_notification_configs')
        ->where('setting', $setting->value)
        ->first('value')
        ->value;

    $parsed_value = match ($setting) {
        Settings::CW_ENABLED => boolval($value),
        Settings::BAILEYS_ENABLE => boolval($value),
        Settings::WP_CUSTOM_FIELD_ID => is_integer($value) ? (int) $value : $value,
        Settings::WP_BUSINESS_ACCOUNT_ID => (int) $value,
        Settings::WP_MSG_TEMPLATE_ASSOCS => json_decode($value, true),
        Settings::WP_MESSAGE_TEMPLATES => is_string($value) ? json_decode($value, true) : null,
        Settings::DEFAULT_CLIENT_NAME => ucwords(strtolower($value)),
        Settings::CW_ACCOUNT_ID => (int) $value,
        Settings::CW_WHATSAPP_INBOX_ID => (int) $value,
        Settings::CW_FACEBOOK_INBOX_ID => (int) $value,
        Settings::CW_LISTEN_WHATSAPP => (bool) $value,
        Settings::CW_ACTIVE_NOTIFS => json_decode($value, true),
        Settings::CW_CLIENT_STATS_TO_SEND => $value ? json_decode($value, true) : [],
        Settings::CW_CUSTOM_FIELDS_TO_SEND => $value ? json_decode($value, true) : [],
        Settings::CW_ENABLE_LIVE_CHAT => (bool) $value,
        Settings::CW_LIVE_CHAT_MODULE_ATTRS_TO_SEND => $value ? json_decode($value, true) : [],
        Settings::ENABLE_LOG => (bool) $value,
        Settings::OBJECT_PAGES_TO_SHOW_REPORTS => $value,
        Settings::WP_EVO_ENABLE => (bool) $value,
        Settings::CW_LIVE_CHAT_SCRIPT => htmlspecialchars_decode($value),
        Settings::WP_USE_TICKET_WHATSAPP_CF_WHEN_SET => $value ?? null,
        Settings::CW_WP_CUSTOM_FIELD_ID => $value ?? null,
        Settings::TICKET_WP_CUSTOM_FIELD_ID => $value ? intval($value) : null,
        Settings::BULK_ENABLE => (bool) $value,
        default => $value
    };

    if (
        in_array($setting, [
            Settings::WP_EVO_WP_NUMBER_CUSTOM_FIELD_ID,
            Settings::WP_CUSTOM_FIELD_ID,
            Settings::BAILEYS_WP_CUSTOM_FIELD_ID,
            Settings::CW_WP_CUSTOM_FIELD_ID,
            Settings::WP_USE_TICKET_WHATSAPP_CF_WHEN_SET,
        ])
    ) {
        $parsed_value = is_numeric($parsed_value) ? $parsed_value : null;
    }

    return $parsed_value;
}

function lkn_hn_config_set(Platforms $platform, Settings $setting, $value)
{
    $result = Capsule::table('mod_lkn_hook_notification_configs')
        ->updateOrInsert(
            ['platform' => $platform->value, 'setting' => $setting->value],
            ['value' => $value]
        );

    lkn_hn_log(
        'Upsert setting',
        ['setting' => $setting->name, 'value' => $value],
        ['result' > $result]
    );
}

function lkn_hn_result(
    string $code,
    mixed $data = null,
    ?string $msg = null,
    array $errors = []
): Result {
    return new Result($code, $data, $msg, $errors);
}

function lkn_hn_get_system_locale(): string
{
    $systemLang = Capsule::table('tblconfiguration')
        ->where('setting', 'Language')
        ->first('value')
        ->value;

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

    $parsedClientLang = current(
        array_filter(
            $clientLocalesList,
            fn(array $item): bool =>
            $item['language'] === $systemLang
        )
    );

    return $parsedClientLang['locale'];
}

function lkn_hn_get_admin_root_url(string $resource = ''): string
{
    /**  @var \WHMCS\Config\Application $whmcsConfig */
    $whmcsConfig = $GLOBALS['whmcsAppConfig'];
    $siteRootUrl = rtrim($GLOBALS['CONFIG']['Domain'], '/');

    return rtrim("$siteRootUrl/" . $whmcsConfig->OffsetGet('customadminpath') . "/$resource", '/');
}

function lkn_hn_normalize_person_name(string $name): string
{
    $normalizedName = preg_replace_callback(
        '/\b(\w)(\w*)\b/',
        function ($matches) {
            return ucfirst(strtolower($matches[1])) . strtolower($matches[2]);
        },
        $name
    );

    return trim($normalizedName);
}

function lkn_hn_remove_phone_number(string $value): string
{
    return preg_replace('/[^0-9]/', '', $value);
}

function lkn_hn_safe_json_encode(array $json, int $additionlFlags = 0)
{
    return json_encode(
        $json,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES | $additionlFlags
    );
}

function lkn_hn_redirect_to_404(): void
{
    header(
        'Location: addonmodules.php?module=lknhooknotification&page=404'
    );
}

function lkn_hn_mask_value(string $contact): string
{
    if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
        [$local, $domain] = explode('@', $contact);
        $localLength      = strlen($local);
        /** @var int $maskLength */
        $maskLength  = max(1, floor($localLength / 2));
        $maskedLocal = substr($local, 0, $localLength - $maskLength) . str_repeat('*', $maskLength);

        return $maskedLocal . '@' . $domain;
    } elseif (preg_match('/^\+?[0-9]{10,}$/', $contact)) {
        $visibleDigits = 4;
        $maskedLength  = strlen($contact) - $visibleDigits;
        return str_repeat('*', $maskedLength) . substr($contact, -$visibleDigits);
    } else {
        return str_repeat('*', strlen($contact) - 4) . substr($contact, -4);
    }
};

function lkn_hn_is_post_request(): bool
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
}

/**
 * CSRF token bound to the current admin session.
 *
 * Generated once per session so several admin tabs can share it.
 */
function lkn_hn_admin_csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }

    if (
        !isset($_SESSION['lkn_hn_admin_csrf'])
        || !is_string($_SESSION['lkn_hn_admin_csrf'])
        || $_SESSION['lkn_hn_admin_csrf'] === ''
    ) {
        $_SESSION['lkn_hn_admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['lkn_hn_admin_csrf'];
}

function lkn_hn_admin_csrf_token_is_valid($token): bool
{
    if (!is_string($token) || $token === '') {
        return false;
    }

    $expected = $_SESSION['lkn_hn_admin_csrf'] ?? null;

    return is_string($expected) && $expected !== '' && hash_equals($expected, $token);
}

/**
 * Defense in depth for state-changing admin requests.
 *
 * When the browser sends Origin/Referer, its host must match the WHMCS
 * SystemURL host. Requests without either header still require a valid
 * CSRF token, which is checked by the caller.
 */
function lkn_hn_request_is_same_origin(): bool
{
    $origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    $requestHost = '';

    if (is_string($origin) && $origin !== '') {
        $requestHost = (string) parse_url($origin, PHP_URL_HOST);
    } elseif (is_string($referer) && $referer !== '') {
        $requestHost = (string) parse_url($referer, PHP_URL_HOST);
    }

    if ($requestHost === '') {
        return true;
    }

    $systemUrl  = Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value');
    $systemHost = is_string($systemUrl) && $systemUrl !== ''
        ? (string) parse_url($systemUrl, PHP_URL_HOST)
        : '';

    if ($systemHost === '') {
        return true;
    }

    return strcasecmp($requestHost, $systemHost) === 0;
}
