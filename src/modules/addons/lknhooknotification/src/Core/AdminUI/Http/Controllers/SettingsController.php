<?php

namespace Lkn\HookNotification\Core\AdminUI\Http\Controllers;

use Lkn\HookNotification\Core\Platforms\Chatwoot\Http\Controllers\ChatwootSettingsController;
use Lkn\HookNotification\Core\Platforms\EvolutionApi\Http\Controllers\EvolutionApiSettingsController;
use Lkn\HookNotification\Core\Platforms\MetaWhatsApp\Http\Controllers\MetaWhatsAppSettingsController;
use Lkn\HookNotification\Core\Platforms\Telegram\Http\Controllers\TelegramSettingsController;
use Lkn\HookNotification\Core\Shared\Application\SettingsService;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Interfaces\BaseController;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;

final class SettingsController extends BaseController
{
    private SettingsService $settingsService;

    public function __construct(View $view)
    {
        $this->settingsService = new SettingsService();

        parent::__construct($view);
    }

    public function viewSettings(string $platform, array $request)
    {
        return $this->renderSettingsPage($platform, null, $request);
    }

    public function viewSubPageSettings(string $platform, string $subpage, array $request)
    {
        return $this->renderSettingsPage($platform, $subpage, $request);
    }

    private function renderSettingsPage(string $platformStr, ?string $subpage, array $request): void
    {
        $platform = Platforms::from($platformStr);
        $this->handleSettingUpdate($platform, $subpage, $request);

        $settingsDef = $this->callGetSettings($platform, $subpage);

        $viewParams = [
            'platform' => $platform->value,
            'platform_title' => $platform->label(),
            'settings_df' => $settingsDef,
        ];

        $this->view->view('pages/settings', $viewParams);
    }

    private function handleSettingUpdate(
        Platforms $platform,
        ?string $subpage,
        array $request
    ) {
        if (!empty($request) && lkn_hn_is_post_request() && !isset($request['telegram-action'])) {
            $result  = $this->settingsService->updateSettings($platform, $subpage, $request);
            $success = $result->code === 'success';

            $alert = [
                'type' => $success ? 'success' : 'danger',
                'msg' => $success
                    ? lkn_hn_lang('The settings were saved.')
                    : lkn_hn_lang(
                        'An error ocurred. The settings were not saved. [1] Go to the module logs for more information.',
                        ['<pre>' . $result->errors['exception'] . '</pre>']
                    ),
            ];

            $this->view->alert(...$alert);

            if ($platform === Platforms::MODULE) {
                header('Refresh: 2');
            }
        }

        $platformSettingsController = $this->platformSettingsControllerFactory($platform);

        if ($platformSettingsController) {
            $output = $platformSettingsController->handle($request);

            if (empty($output)) {
                return;
            }

            $this->view->assign('platform_settings_controller_output', $output);
        }
    }

    private function callGetSettings(
        Platforms $platform,
        ?string $subpage = null
    ) {
        $settingsDef = $this->settingsService->getSettingsForView($platform, $subpage);

        return $settingsDef;
    }

    /**
     * @param  Platforms $platform
     *
     * @return BaseController|null
     */
    private function platformSettingsControllerFactory(Platforms $platform): ?BaseController
    {
        if ($platform === Platforms::WP_EVO) {
            return new EvolutionApiSettingsController();
        } elseif ($platform === Platforms::WHATSAPP) {
            return new MetaWhatsAppSettingsController();
        } elseif ($platform === Platforms::CHATWOOT) {
            return new ChatwootSettingsController();
        } elseif ($platform === Platforms::TELEGRAM) {
            return new TelegramSettingsController();
        }

        return null;
    }
}
