<?php

namespace Lkn\HookNotification\Core\AdminUI\Application\Services;

use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;

class VersionUpgradeWarningService
{
    final public static function setLatestVersion(string $version): void
    {
        lkn_hn_config_set(Platforms::MODULE, Settings::LATEST_VERSION, $version);
    }

    final public static function getNewVersion(): ?string
    {
        return lkn_hn_config(Settings::LATEST_VERSION);
    }

    final public static function setLatestVersionBody(?string $body): void
    {
        lkn_hn_config_set(Platforms::MODULE, Settings::LATEST_VERSION_BODY, $body);
    }

    final public static function getLatestVersionBody(): ?string
    {
        return lkn_hn_config(Settings::LATEST_VERSION_BODY);
    }

    final public static function setLatestVersionDate(?string $date): void
    {
        lkn_hn_config_set(Platforms::MODULE, Settings::LATEST_VERSION_DATE, $date);
    }

    final public static function getLatestVersionDate(): ?string
    {
        return lkn_hn_config(Settings::LATEST_VERSION_DATE);
    }

    final public static function setDismissedVersion(string $version): void
    {
        lkn_hn_config_set(Platforms::MODULE, Settings::NEW_VERSION_DISMISSED, $version);
    }

    final public static function getDismissedVersion(): ?string
    {
        return lkn_hn_config(Settings::NEW_VERSION_DISMISSED);
    }

    final public static function setLastVersionCheck(int $timestamp): void
    {
        lkn_hn_config_set(Platforms::MODULE, Settings::LAST_VERSION_CHECK, $timestamp);
    }

    final public static function getLastVersionCheck(): ?int
    {
        $value = lkn_hn_config(Settings::LAST_VERSION_CHECK);

        return $value === null ? null : (int) $value;
    }

    // --- compatibilidade (legado) ---
    final public static function setDismissOnAdminHome(bool $dismiss): void
    {
        lkn_hn_config_set(Platforms::MODULE, Settings::NEW_VERSION_DISMISS_ON_ADMIN_HOME, $dismiss);
    }

    final public static function getDismissNewVersionAlert(): ?bool
    {
        return lkn_hn_config(Settings::NEW_VERSION_DISMISS_ON_ADMIN_HOME);
    }
}
