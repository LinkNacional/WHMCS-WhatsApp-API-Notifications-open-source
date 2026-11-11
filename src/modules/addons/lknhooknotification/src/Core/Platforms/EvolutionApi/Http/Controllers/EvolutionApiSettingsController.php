<?php

namespace Lkn\HookNotification\Core\Platforms\EvolutionApi\Http\Controllers;

use Lkn\HookNotification\Core\Platforms\EvolutionApi\Application\EvolutionApiSetupService;
use Lkn\HookNotification\Core\Shared\Infrastructure\Interfaces\BaseController;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;

final class EvolutionApiSettingsController extends BaseController
{
    private EvolutionApiSetupService $evolutionApiSetupService;

    public function __construct()
    {
        parent::__construct(new View());
        $this->evolutionApiSetupService = new EvolutionApiSetupService();
    }

    public function handle(array $request): string
    {
        $isDisconnectInstanceOperation = lkn_hn_is_post_request()
            && isset($request['disconnect-instance']);

        if ($isDisconnectInstanceOperation) {
            $result = $this->evolutionApiSetupService->disconnectInstance();
        }

        $serviceRes = $this->evolutionApiSetupService->setup();

        if ($serviceRes->code === 'empty-api-credentials') {
            return $this->view->view('setup_1_fill_settings')->render();
        } elseif ($serviceRes->code === 'step-2-read-qr-code') {
            return $this->view->view(
                'setup_2_qr_code',
                [
                    'qr_code_base64' => $serviceRes->data['qr_code_base64'],
                ]
            )->render();
        } elseif ($serviceRes->code === 'step-3-connected') {
            return $this->view->view(
                'setup_3_connected',
                $serviceRes->data
            )->render();
        } else {
            $this->view->alert(
                'danger',
                lkn_hn_lang(
                    'An error ocurred. The settings were not saved. [1] Go to the module logs for more information.',
                    ['<pre>' . $serviceRes->errors['exception'] . '</pre>']
                )
            );

            return $this->view->view(
                'error',
                ['error' => lkn_hn_safe_json_encode($serviceRes->toArray())]
            )->render();
        }
    }
}
