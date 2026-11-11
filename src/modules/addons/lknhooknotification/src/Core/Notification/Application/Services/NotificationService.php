<?php

namespace Lkn\HookNotification\Core\Notification\Application\Services;

use Exception;
use Lkn\HookNotification\Core\Notification\Application\NotificationFactory;
use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Infrastructure\Repositories\NotificationRepository;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Result;

final class NotificationService
{
    private NotificationFactory $notificationFactory;
    private NotificationRepository $notificationRepository;

    public function __construct()
    {
        $this->notificationFactory    = NotificationFactory::getInstance();
        $this->notificationRepository = new NotificationRepository();
    }

    public function handleUpdate(
        string $notificationCode,
        array $request
    ): Result {
        try {
            $platform        = Platforms::from($request['platform']);
            $platformPayload = [];
            $template        = '';

            if ($platform === Platforms::WHATSAPP) {
                $result = $this->handleWhatsAppPlatformPayloadForm($request);

                if ($result->code !== 'success') {
                    return $result;
                }

                $template        = $result->data['template'] ?? '';
                $platformPayload = $result->data['platformPayload'];
            } else {
                $template        = $request['template'] ?? '';
                $platformPayload = [];
            }

            $this->notificationRepository->upsertNotification(
                notiifcationCode: $notificationCode,
                platform: $platform->value,
                locale: $request['locale'],
                template: $template,
                platformPayload: $platformPayload,
            );

            return lkn_hn_result('success');
        } catch (Exception $e) {
            return lkn_hn_result(
                'error',
                errors: ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * This method is coupled to Core/Notification/Http/Views/template_editors/meta_wp_template_editor.tpl.
     *
     * @param array{
     *     header-parameter?: string,
     *     body-parameters: array<int, string>,
     *     button-parameters: array<int, string>,
     *     message-template-lang: string,
     *     message-template: string,
     *     header-format: string
     * } $request
     *
     * @return Result<array{
     *     template: string,
     *     platformPayload: array{
     *         msgTemplateLang: string,
     *         header?: array<int, array{key: int, value: string, type: 'TEXT'|'text'}>,
     *         body: array<int, array{key: int, value: string, type: 'text'}>,
     *         button?: array<int, array{
     *             index: int,
     *             type: 'url',
     *             params: array<int, array{key: int, value: string}>
     *         }>
     *     }
     * }>
     */
    public function handleWhatsAppPlatformPayloadForm(array $request): Result
    {
        /**
         * @var array{
         *     msgTemplateLang: string,
         *     header: array<int, array{key: string, value: string, type: string}>,
         *     body?: array<int, array{key: string, value: string, type: string}>,
         *     button?: array<int, array{
         *         index: string,
         *         type: string,
         *         params: array<int, array{key: string, value: string}>
         *     }>
         * } $platformPayload
         */
        $platformPayload = [
            'msgTemplateLang' => $request['message-template-lang'],
        ];

        $template = $request['message-template'];

        if (!empty($request['body-parameters'])) {
            $hasEmptyInBody = !empty(array_filter($request['body-parameters'], fn($v) => empty($v)));
        }

        if (!empty($request['button-parameters'])) {
            $hasEmptyInButton = !empty(array_filter($request['button-parameters'], fn($v) => empty($v)));
        }

        if (
            isset($request['header-parameter'])

        ) {
            $platformPayload['header'][] = [
                'key' => strval(1),
                'value' => $request['header-parameter'],
                'type' => $request['header-format'],
            ];
        } else {
            $platformPayload['header'] = [];
        }

        foreach ($request['body-parameters'] as $key => $param) {
            $platformPayload['body'][] = [
                'key' => strval($key + 1),
                'value' => $param,
                'type' => 'text',
            ];
        }

        foreach ($request['button-parameters'] as $key => $param) {
            $platformPayload['button'][] = [
                'index' => strval($key),
                'type' => 'url',
                'params' => [
                    [
                        'key' => strval($key + 1),
                        'value' => $param,
                    ],
                ],
            ];
        }

        return lkn_hn_result(
            code: 'success',
            data: [
                'template' => $template,
                'platformPayload' => $platformPayload,
            ]
        );
    }

    public function getNotificationsForView()
    {
        return $this->notificationFactory->makeAll(true);
    }

    public function buildNotification(string $notificationCode): ?AbstractNotification
    {
        return $this->notificationFactory->makeByCode($notificationCode);
    }

    public function handleTemplateDelete(
        string $notificationCode,
        string $templateLocale,
        ?Platforms $platform = null
    ): Result {
        try {
            $result = $this->notificationRepository
                ->deleteNotificationTemplate(
                    $notificationCode,
                    $templateLocale,
                    $platform?->value
                );

            return lkn_hn_result('success');
        } catch (Exception $e) {
            return lkn_hn_result(
                'error',
                errors: ['exception' => $e->getMessage()]
            );
        }
    }

    public function isNotificationEnabled(string $notificationCode): bool
    {
        $enabledNotifications = $this->notificationRepository->getEnabledNotifications();

        $isEnabled = !empty($enabledNotifications[$notificationCode]);

        return $isEnabled;
    }
}
