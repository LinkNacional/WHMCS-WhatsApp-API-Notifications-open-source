<?php

namespace Lkn\HookNotification\Core\Platforms\MetaWhatsApp\Domain;

use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportStatus;
use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Domain\AbstractNotificationParser;
use Lkn\HookNotification\Core\Notification\Domain\NotificationTemplate;
use Lkn\HookNotification\Core\Platforms\Common\AbstractPlatform;
use Lkn\HookNotification\Core\Platforms\Common\AbstractPlatformSettings;
use Lkn\HookNotification\Core\Platforms\Common\PlatformNotificationSendResult;
use Lkn\HookNotification\Core\Shared\Infrastructure\BaseApiClient;

final class MetaWhatsAppPlatform extends AbstractPlatform
{
    /**
     * @var MetaWhatsAppSettings
     */
    public readonly AbstractPlatformSettings $platformSettings;

    /**
     * @var MetaWhatsAppNotificationParser
     */
    public readonly AbstractNotificationParser $notificationParser;

    /**
     * @var \Lkn\HookNotification\Core\Platforms\MetaWhatsApp\Infrastructure\MetaWhatsAppApiClient
     */
    protected readonly BaseApiClient $apiClient;

    public function sendNotification(
        AbstractNotification $notification,
        NotificationTemplate $template,
    ): PlatformNotificationSendResult {
        if (!$this->platformSettings->enabled) {
            return new PlatformNotificationSendResult(
                NotificationReportStatus::NOT_SENT,
                'The platform is disabled.'
            );
        }

        $phoneNumber = $this->getPhoneNumber($notification);

        if (!$phoneNumber) {
            return new PlatformNotificationSendResult(
                NotificationReportStatus::NOT_SENT,
                'Client has no valid phone number.'
            );
        }

        // Meta exige 'to' em E.164 SEM '+'.
        $phoneNumber = str_replace('+', '', (string) $phoneNumber);

        if (empty($template->platformPayload['msgTemplateLang'])) {
            return new PlatformNotificationSendResult(
                NotificationReportStatus::NOT_SENT,
                'Please, click save inside the notification page to update it to the new format.'
            );
        }

        $filledTemplate = $this->notificationParser->parse($notification, $template);

        $apiResponse = $this->apiClient->sendMessageTemplate(
            $phoneNumber,
            $template->template,
            $filledTemplate,
            $template->platformPayload['msgTemplateLang']
        );

        if (
            !isset($apiResponse->body['messages'][0]['message_status']) ||
            $apiResponse->body['messages'][0]['message_status'] !== 'accepted'
        ) {
            lkn_hn_log(
                "{$template->platform->value}: api error",
                [
                    'phoneNumber' => $phoneNumber,
                    'notification' => $notification,
                    'template' => $template,
                ],
                [
                    'api_response' => $apiResponse,
                ]
            );

            return new PlatformNotificationSendResult(
                NotificationReportStatus::ERROR,
                isset($apiResponse->body['error']['message'])
                    ? $apiResponse->body['error']['message']
                    : 'API Error'
            );
        }

        return new PlatformNotificationSendResult(
            NotificationReportStatus::SENT,
            'The notification was sent.',
            $phoneNumber,
        );
    }
}
