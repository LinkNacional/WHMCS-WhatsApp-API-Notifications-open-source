<?php

namespace Lkn\HookNotification\Core\AdminUI\Http\Controllers;

use Lkn\HookNotification\Core\AdminUI\Application\Services\LicenseService;
use Lkn\HookNotification\Core\AdminUI\Application\Services\VersionUpgradeWarningService;
use Lkn\HookNotification\Core\NotificationReport\Application\NotificationReportService;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\ModuleInfo;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use Lkn\HookNotification\Core\Shared\Infrastructure\Interfaces\BaseController;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;

final class HomepageController extends BaseController
{
    private readonly NotificationReportService $notificationReportService;

    public function __construct(View $view)
    {
        $this->notificationReportService = new NotificationReportService();

        parent::__construct($view);
    }

    public function viewHomepage(array $request): void
    {
        $licenseService = LicenseService::getInstance();

        $licenseCheckRes = $licenseService->isLicenseActive();

        $statistics = $this->notificationReportService->getStatistics();

        $viewParams = [
            ...$statistics,
            'license_status' => $licenseCheckRes->code,
            'new_version_alert' => $this->newVersion(),
            'dismiss_v400_alert' => true,
        ];

        if (isset($request['dimisv400-alert'])) {
            lkn_hn_config_set(Platforms::MODULE, Settings::MODULE_DISMISS_V400_ALERT, true);

            header('Location: ?module=lknhooknotification&page=home');

            exit;
        }

        if (!lkn_hn_config(Settings::MODULE_DISMISS_V400_ALERT)) {
            /** @var string $previousVersion */
            $previousVersion = lkn_hn_config(Settings::MODULE_PREVIOUS_VERSION);

            if (
                version_compare(
                    $previousVersion,
                    '4.0.0',
                    '<',
                )
             ) {
                $viewParams['dismiss_v400_alert'] = false;
            }
        }

        $this->view->view(
            'pages/homepage',
            $viewParams,
        );
    }

    public function viewChangelog(array $request): void
    {
        $statistics = $this->notificationReportService->getStatistics();

        $changelog = require_once __DIR__ . '/../../Infrastructure/changelog.php';

        $this->view->view(
            'pages/changelog',
            [
                ...$statistics,
                'changelog' => $changelog,
            ],
        );
    }

    public function newVersion(): ?string
    {
        if (isset($_GET['new-version-dismiss-on-admin-home'])) {
            VersionUpgradeWarningService::setDismissOnAdminHome(true);
        }

        $mustDismissAlert = VersionUpgradeWarningService::getDismissNewVersionAlert();

        if ($mustDismissAlert) {
            return null;
        }

        $currentAdminDetails = localAPI('GetAdminDetails');
        $adminPermissons     = $currentAdminDetails['allowedpermissions'];

        if (!str_contains($adminPermissons, 'Configure Addon Modules')) {
            return null;
        }

        $newVersion = VersionUpgradeWarningService::getNewVersion();

        $currentVersion = ModuleInfo::VERSION;

        if (version_compare($newVersion, $currentVersion, '>')) {
            return $this->view->view(
                'components/new_version_pop_up',
                ['new_version' => $newVersion]
            )->render();
        }

        return null;
    }

    public function handleNewVersionCheck(): void
    {
        return;
    }

    /**
     * @param  array<mixed> $request
     *
     * @return void
     */
    public function notFound404(array $request): void
    {
        $this->view->view('pages/404');
    }
}
