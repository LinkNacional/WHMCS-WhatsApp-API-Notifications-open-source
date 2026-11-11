<?php

namespace Lkn\HookNotification\Core\Notification\Application\Services;

use Lkn\HookNotification\Core\NotificationReport\Application\NotificationReportService;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportStatus;
use Lkn\HookNotification\Core\Notification\Application\NotificationPlatformResolver;
use Lkn\HookNotification\Core\Notification\Domain\AbstractCronNotification;
use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Infrastructure\Observers\NotificationObserverFactory;
use Lkn\HookNotification\Core\Platforms\Common\PlatformNotificationSendResult;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;
use Lkn\HookNotification\Core\Shared\Infrastructure\Result;
use Lkn\HookNotification\Core\Shared\Infrastructure\Singleton;
use Throwable;

final class NotificationSender extends Singleton {
    private readonly NotificationReportService $notificationReportService;
    private readonly NotificationPlatformResolver $notificationPlatformResolver;

    /**
     * @var array<\Lkn\HookNotification\Core\Notification\Domain\NotificationObserverInterface>
     */
    private readonly array $notificationObservers;

    protected function __construct()
    {
        $this->notificationReportService    = new NotificationReportService();
        $this->notificationPlatformResolver = new NotificationPlatformResolver();
        $this->notificationObservers        = NotificationObserverFactory::make();
    }

    /**
     * @param  AbstractNotification $notification
     * @param  array<mixed>|null    $whmcsHookParams
     *
     * @return null|Result|PlatformNotificationSendResult
     */
    public function dispatchNotification(
        AbstractNotification $notification,
        ?array $whmcsHookParams,
    ): null|Result|PlatformNotificationSendResult {
        try {
            lkn_hn_log(
                'Dispatching notification',
                [
                    'notification' => $notification,
                    'whmcs_hook_params' => $whmcsHookParams,
                ],
            );

            $isCronNotification = in_array($notification->hook, [Hooks::DAILY_CRON_JOB]);

            if (
                $isCronNotification
                && is_subclass_of($notification, AbstractCronNotification::class)
            ) {
                $this->handleCronJobNotification($notification);

                return null;
            }

            return $this->send($notification, $whmcsHookParams);
        } catch (Throwable $th) {
            lkn_hn_log(
                'Dispatch error: ' . $th->getMessage(),
                [
                    'notification' => $notification,
                    'whmcs_hook_params' => $whmcsHookParams,
                ],
                [
                    'exception' => $th->__toString(),
                ]
            );

            return null;
        }
    }

    /**
     * @param  AbstractCronNotification $notification
     *
     * @return void
     */
    private function handleCronJobNotification(
        AbstractCronNotification $notification,
    ): void {
        $payloadsForEachClient = $notification->getPayload();

        foreach ($payloadsForEachClient as $payload) {
            /** @var array<mixed> $payload */

            $this->send(
                $notification,
                $payload
            );
        }
    }

    /**
     * @param  AbstractNotification $notification
     * @param  null|array<mixed>    $whmcsHookParams
     * @param null|integer         $queueId
     *
     * @return Result|PlatformNotificationSendResult
     */
    public function send(
        AbstractNotification $notification,
        ?array $whmcsHookParams,
        ?int $queueId = null,
    ): Result|PlatformNotificationSendResult {
        $notification->finishInit($whmcsHookParams);

        if (!$notification->shouldRun()) {
            return new Result(code: 'aborted-by-notification-custom-condition');
        }

        $platformResolverResult = $this->notificationPlatformResolver->resolve(
            $notification
        );

        if ($platformResolverResult instanceof Result) {
            $this->notificationReportService->createReport(
                $notification->client->id,
                $notification->categoryId,
                $notification->category,
                NotificationReportStatus::NOT_SENT,
                $platformResolverResult->code,
                null,
                $notification->code,
                $notification->hook
            );

            return $platformResolverResult;
        }

        /** @var AbstractPlatform $platform */
        /** @var NotificationTemplate $template */
        [$platform, $template] = $platformResolverResult;


        $platformResponse = $platform->sendNotification(
            $notification,
            $template
        );

        if ($platformResponse->status === NotificationReportStatus::SENT) {
            foreach ($this->notificationObservers as $obsever) {
                $obsever->onNotificationSent($notification, $template, $platform);
            }
        }

        $this->notificationReportService->createReport(
            $notification->client->id,
            $notification->categoryId,
            $notification->category,
            $platformResponse->status,
            $platformResponse->msg,
            $template->platform,
            $notification->code,
            $notification->hook,
            target: $platformResponse->target,
            queueId: $queueId,
        );

        return $platformResponse;
    }
}
