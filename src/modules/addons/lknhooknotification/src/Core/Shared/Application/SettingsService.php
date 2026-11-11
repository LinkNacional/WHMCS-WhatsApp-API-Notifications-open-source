<?php

namespace Lkn\HookNotification\Core\Shared\Application;

use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Repository\SettingsRepository;
use Lkn\HookNotification\Core\Shared\Infrastructure\Result;

/**
 * This class should use {Platform}SetupService when a platform settings changes.
 */
final class SettingsService
{
    private readonly SettingsRepository $settingsRepository;

    public function __construct()
    {
        $this->settingsRepository = new SettingsRepository();
    }

    /**
     * @param  Platforms   $platform
     * @param  string|null $subpage
     * @return array
     */
    public function getSettingsForView(Platforms $platform, ?string $subpage = null): array
    {
        $platformFolder = match ($platform) {
            Platforms::BAILEYS => 'Baileys',
            Platforms::WP_EVO => 'EvolutionApi',
            Platforms::WHATSAPP => 'MetaWhatsApp',
            Platforms::CHATWOOT => 'Chatwoot',
            Platforms::TELEGRAM => 'Telegram',
            Platforms::MODULE => 'Module',
            Platforms::BULK_MESSAGING => '../BulkMessaging',
        };

        $settingsDefPath = __DIR__ . "/../../Platforms/{$platformFolder}/Infrastructure/";

        if ($subpage) {
            $settingsDefPath .= str_replace('-', '_', $subpage) . '_settings.php';
        } else {
            $settingsDefPath .= 'settings.php';
        }

        $settingsDef = require $settingsDefPath;

        $filledSettingsDef = array_map(
            function ($settingDef) {
                if (isset($settingDef['separator'])) {
                    return $settingDef;
                }

                $settingDef['id']      = $settingDef['setting']->value;
                $settingDef['current'] = lkn_hn_config($settingDef['setting']);

                return $settingDef;
            },
            $settingsDef
        );

        return $filledSettingsDef;
    }

    /**
     * @param  Platforms                           $platform
     * @param  string|null                         $subpage
     * @param  array<string, string|array<string>> $incomingSettings
     *
     * @return \Lkn\HookNotification\Core\Shared\Infrastructure\Result
     */
    public function updateSettings(Platforms $platform, ?string $subpage, array $incomingSettings): Result
    {
        $settingsDef     = $this->getSettingsForView($platform, $subpage);
        $validSettingIds = array_column($settingsDef, 'id');

        $filteredSettings = [];

        foreach ($validSettingIds as $settingId) {
            if (!isset($incomingSettings[$settingId])) {
                $filteredSettings[$settingId] = '';

                continue;
            }

            $newValue = $incomingSettings[$settingId];

            if (is_string($newValue)) {
                $newValue = rtrim(trim($newValue), '/');
            }

            $filteredSettings[$settingId] = $newValue;
        }

        return $this->settingsRepository->massUpsert($platform, $filteredSettings);
    }
}
