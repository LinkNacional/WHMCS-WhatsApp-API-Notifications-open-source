<?php

namespace Lkn\HookNotification\Core\AdminUI\Application\Services;

use Lkn\HookNotification\Core\Shared\Infrastructure\Config\ModuleInfo;

final class GitHubReleaseService
{
    private const RELEASES_URL = 'https://api.github.com/repos/LinkNacional/WHMCS-WhatsApp-API-Notifications-open-source/releases/latest';

    public function checkLatestRelease(): void
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::RELEASES_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.github+json',
                'User-Agent: LinkNacional-WHMCS-Module/' . ModuleInfo::VERSION,
            ],
        ]);

        $response  = curl_exec($curl);
        $httpCode  = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);

        curl_close($curl);

        if ($response === false || $httpCode !== 200) {
            lkn_hn_log(
                'GitHub release check failed',
                ['http_code' => $httpCode, 'error' => (string) $curlError]
            );

            return;
        }

        $payload = json_decode((string) $response, true);

        if (!is_array($payload)) {
            lkn_hn_log('GitHub release check: invalid JSON');

            return;
        }

        $tagName = (string) ($payload['tag_name'] ?? '');

        $normalized = ltrim($tagName, 'vV');

        if (!preg_match('/^\d+\.\d+\.\d+$/', $normalized)) {
            lkn_hn_log(
                'GitHub release check: invalid semver tag',
                ['tag_name' => $tagName]
            );

            return;
        }

        $body        = isset($payload['body']) && is_string($payload['body']) ? $payload['body'] : null;
        $publishedAt = isset($payload['published_at']) && is_string($payload['published_at']) ? $payload['published_at'] : null;

        VersionUpgradeWarningService::setLatestVersion($normalized);
        VersionUpgradeWarningService::setLatestVersionBody($body);
        VersionUpgradeWarningService::setLatestVersionDate($publishedAt);
        VersionUpgradeWarningService::setLastVersionCheck(time());

        lkn_hn_log(
            'GitHub release check: updated',
            ['version' => $normalized]
        );
    }
}
