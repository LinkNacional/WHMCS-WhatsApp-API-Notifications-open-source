<?php

namespace Lkn\HookNotification\Core\Notification\Application;

use Lkn\HookNotification\Core\Notification\Domain\AbstractNotification;
use Lkn\HookNotification\Core\Notification\Domain\NotificationTemplate;
use Lkn\HookNotification\Core\Platforms\Common\Infrastructure\PlatformFactory;
use Lkn\HookNotification\Core\Shared\Infrastructure\Result;

final class NotificationPlatformResolver
{
    private PlatformFactory $platformFactory;

    public function __construct()
    {
        $this->platformFactory = new PlatformFactory();
    }

    /**
     * @return array<int, array{0: \Lkn\HookNotification\Core\Platforms\Common\AbstractPlatform, 1: NotificationTemplate}>|Result
     */
    public function resolveAll(AbstractNotification $notification): array|Result
    {
        $templates = array_values(array_filter(
            $notification->templates,
            fn (NotificationTemplate $template) =>
                $template->lang === $notification->client->locale
        ));

        if (!$templates) {
            $systemLocale = lkn_hn_get_system_locale();

            $templates = array_values(array_filter(
                $notification->templates,
                fn (NotificationTemplate $template) =>
                    $template->lang === $systemLocale
            ));
        }

        if (!$templates) {
            if (isset($notification->templates[0]) && is_null($notification->templates[0]->lang)) {
                $templates = [$notification->templates[0]];
            } else {
                $result = new Result(
                    'no-template-found-for-client-lang',
                    msg: 'No template found for client language.'
                );

                lkn_hn_log(
                    'Send notification',
                    ['whmcsHookParams' => $notification->whmcsHookParams],
                    ['resultCode' => $result->code]
                );

                return $result;
            }
        }

        $resolved = [];
        $seen     = [];

        foreach ($templates as $template) {
            if (!$template->platform) {
                continue;
            }

            $platformKey = $template->platform->value;

            if (isset($seen[$platformKey])) {
                continue;
            }

            $seen[$platformKey] = true;
            $platform = $this->platformFactory->make($template->platform);

            if (
                isset($platform->platformSettings)
                && isset($platform->platformSettings->enabled)
                && !$platform->platformSettings->enabled
            ) {
                continue;
            }

            $resolved[] = [$platform, $template];
        }

        if ($resolved === []) {
            $result = new Result(
                'platform-is-disabled',
                msg: 'The platform is disabled.'
            );

            lkn_hn_log(
                'Platform is disabled',
                ['whmcsHookParams' => $notification->whmcsHookParams],
                ['resultCode' => $result->code]
            );

            return $result;
        }

        return $resolved;
    }

    /**
     * Backwards-compatible single-platform resolution.
     *
     * @return array{0: \Lkn\HookNotification\Core\Platforms\Common\AbstractPlatform, 1: NotificationTemplate}|Result
     */
    public function resolve(AbstractNotification $notification)
    {
        $resolved = $this->resolveAll($notification);

        if ($resolved instanceof Result) {
            return $resolved;
        }

        return $resolved[0];
    }
}
