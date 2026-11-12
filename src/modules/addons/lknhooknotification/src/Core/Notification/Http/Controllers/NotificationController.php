<?php

namespace Lkn\HookNotification\Core\Notification\Http\Controllers;

use Lkn\HookNotification\Core\AdminUI\Application\Services\LicenseService;
use Lkn\HookNotification\Core\Notification\Application\Services\NotificationService;
use Lkn\HookNotification\Core\Notification\Application\Services\NotificationViewService;
use Lkn\HookNotification\Core\Notification\Domain\NotificationTemplate;
use Lkn\HookNotification\Core\Platforms\Common\Application\PlatformService;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Interfaces\BaseController;
use Lkn\HookNotification\Core\Shared\Infrastructure\View\View;

final class NotificationController extends BaseController
{
    private NotificationService $notificationService;
    private NotificationViewService $notificationViewService;

    private PlatformService $platformService;

    public function __construct(View $view)
    {
        parent::__construct($view);

        $this->notificationService     = new NotificationService();
        $this->platformService         = new PlatformService();
        $this->notificationViewService = new NotificationViewService($this->view);
    }

    public function viewNotification(
        string $notificationCode,
        string $editingLocale,
        array $request
    ): void {
        $editingNotification = $this->notificationService->buildNotification($notificationCode);

        if (!$editingNotification) {
            lkn_hn_redirect_to_404();

            exit;
        }

        $viewParams = [
            'editing_notification' => $editingNotification,
            'platform_list' => $this->platformService->getEnabledPlatforms(),
        ];

        $requestedPlatform = Platforms::tryFrom((string) ($request['platform'] ?? ''));

        if ($editingLocale === 'new') {
            if (!empty($request['locale'])) {
                header(
                    'Location: addonmodules.php?module=lknhooknotification&page=notifications/'
                    . $editingNotification->code
                    . '/templates/'
                    . $request['locale']
                );

                exit;
            }

            $this->view->view(
                'create_edit_notification',
                [
                    ...$viewParams,
                    'request_locale_selection' => true,
                ]
            );

            return;
        } elseif (
                !empty($request['locale']) &&
                $request['locale'] !== $editingLocale
            ) {
            header(
                'Location: addonmodules.php?module=lknhooknotification&page=notifications/'
                . $editingNotification->code
                . '/templates/'
                . $request['locale']
            );

            exit;
        } elseif ($editingLocale === 'first' && $editingNotification) {
            if (count($editingNotification->templates) === 0) {
                lkn_hn_redirect_to_404();

                exit;
            }

            $firstTemplateLang = $editingNotification->templates[0]->lang;

            header(
                'Location: addonmodules.php?module=lknhooknotification&page=notifications/'
                . $editingNotification->code
                . '/templates/'
                . $firstTemplateLang
            );

            exit;
        }

        $foundExistingTemplate = $requestedPlatform
            ? $this->notificationViewService->findTemplateByLangAndPlatform(
                $editingNotification,
                $editingLocale,
                $requestedPlatform
            )
            : $this->notificationViewService->findTemplateByLang(
                $editingNotification,
                $editingLocale
            );

        if (
            lkn_hn_is_post_request() &&
            !empty($request) &&
            (
                (
                    // For WhatsApp, requires selecting the message-template before saving.
                    $request['platform'] === 'wp' && (
                        !empty($request['message-template'])
                        || $foundExistingTemplate && $foundExistingTemplate->platform !== $request['platform']
                    )
                )
                ||
                (
                    // For other platforms, requires just selecting the platform. Template is just a textarea.
                    $request['platform'] !== 'wp'
                )
            )
        ) {
            $templateUpdateResult = $this->notificationService->handleUpdate($notificationCode, $request);


            if ($templateUpdateResult->code === 'success') {
                $this->view->alert(
                    'success',
                    lkn_hn_lang('The notification was saved.')
                );

                $editingNotification = $this->notificationService->buildNotification($notificationCode);
            } else {
                $this->view->alert(
                    'danger',
                    lkn_hn_lang(
                        'An error ocurred. The template was not updated. [1]. Go to the module logs for more information.',
                        ['<pre>' . $templateUpdateResult->errors['exception'] . '</pre>']
                    )
                );
            }
        }

        $foundExistingTemplate = $requestedPlatform
            ? $this->notificationViewService->findTemplateByLangAndPlatform(
                $editingNotification,
                $editingLocale,
                $requestedPlatform
            )
            : $this->notificationViewService->findTemplateByLang(
                $editingNotification,
                $editingLocale
            );

        $editingTemplate = null;

        if (!$foundExistingTemplate) {
            $editingTemplate = new NotificationTemplate(
                $requestedPlatform,
                $editingLocale,
                '',
                []
            );
        } else {
            $editingTemplate = $foundExistingTemplate;
        }

        if (!$editingTemplate->platform) {
            $this->view->view(
                'create_edit_notification',
                [
                    ...$viewParams,
                    'request_platform_selection' => true,
                    'editing_locale' => $editingLocale,
                ]
            );

            return;
        }

        $this->view->view(
            'create_edit_notification',
            [
                ...$viewParams,
                'editing_locale' => $editingLocale,
                'editing_template' => $editingTemplate,
                'editing_notification' => $editingNotification,
                'template_editor_view' => $this->notificationViewService->getTemplateEditorForPlatform(
                    $editingNotification,
                    $editingTemplate
                ),
            ]
        );
    }

    /**
     * @param  array<mixed> $request
     *
     * @return void
     */
    public function viewNotificationsTable(array $request): void
    {
        /** @var string $notificationCode  */
        $notificationCode = $request['notification-code'];
        /** @var string $templateLocale  */
        $templateLocale = $request['template-locale'];

        if (lkn_hn_is_post_request() && isset($request['delete-template'])) {
            $deletePlatform = Platforms::tryFrom((string) ($request['platform'] ?? ''));

            $templateDeletionResult = $this->notificationService->handleTemplateDelete(
                $notificationCode,
                $templateLocale,
                $deletePlatform,
            );

            $alert = [
                'type' => $templateDeletionResult->code === 'success' ? 'success' : 'danger',
                'msg' => $templateDeletionResult->code === 'success'
                    ? lkn_hn_lang('The template was deleted.')
                    : lkn_hn_lang(
                        'An error ocurred. The template was not deleted. [1]. Go to the module logs for more information.',
                        ['<pre>' . $templateDeletionResult->errors['exception'] . '</pre>']
                    ),
            ];

            $this->view->alert(...$alert);
        }

        $notifications = $this->notificationService->getNotificationsForView();

        $this->view->view('notifications_table', [
            'notifications' => $notifications,
            'must_block_add_other_notifications' => LicenseService::getInstance()->mustBlockNotificationEdit(),
            'must_block_edit_notification' => LicenseService::getInstance()->mustBlockNotificationEdit(),
            'platform_list' => $this->platformService->getEnabledPlatforms(),
        ]);
    }
}
