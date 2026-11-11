<?php

namespace Lkn\HookNotification\Core\Notification\Infrastructure\Repositories;

use Lkn\HookNotification\Core\Shared\Infrastructure\Repository\BaseRepository;

class NotificationRepository extends BaseRepository
{
    public function upsertNotification(
        string $notiifcationCode,
        string $platform,
        string $locale,
        string $template,
        array $platformPayload
    ) {
        $result = $this->query
            ->table('mod_lkn_hook_notification_localized_tpls')
            ->updateOrInsert(
                [
                    'notif_code' => $notiifcationCode,
                    'lang' => $locale,
                    'platform' => $platform,
                ],
                [
                    'platform' => $platform,
                    'tpl' => $template,
                    'platform_payload' => json_encode($platformPayload),
                ]
            );
        ;

        return $result;
    }

    /**
     * @return array<string, array{
     *     lang: string,
     *     tpl: string,
     *     platform: string,
     *     platform_payload: array{
     *         header: array<int, array{
     *             key: string,
     *             value: string,
     *             type: string
     *         }>,
     *         body: array<int, array{
     *             key: string,
     *             value: string,
     *             type: string
     *         }>,
     *         button: array<int, array{
     *             index: string,
     *             type: string,
     *             params: array<int, array{
     *                 key: string,
     *                 value: string
     *             }>
     *         }>
     *     }
     * }>
     */
    public function getEnabledNotifications()
    {
        // For passing WhatsApp assoc to Notification, see the last message here: https://chatgpt.com/c/67f01062-776c-8000-a013-c25b1f937109

        $standardNotifs = $this->query
            ->table('mod_lkn_hook_notification_localized_tpls')
            ->select('notif_code', 'platform', 'platform_payload', 'lang', 'tpl')
            ->get();

        $groupedByNotifCode = $standardNotifs->groupBy('notif_code')
            ->map(function ($group) {
                return $group->map(fn($item) => [
                    'lang' => $item->lang,
                    'tpl'  => $item->tpl,
                    'platform'  => $item->platform,
                    'platform_payload'  => json_decode($item->platform_payload, true),
                ])->values();
            })->toArray();

        // $wpNotifs = lkn_hn_config(Settings::WP_MSG_TEMPLATE_ASSOCS);

        // foreach ($wpNotifs as $item) {
        //     $groupedByNotifCode[$item['notification']][] = [
        //         'lang' => $item['language'],
        //         'tpl'  => $item['template'],
        //         'platform'  => 'wp',
        //         'platform_payload'=> $item['components'],
        //     ];
        // }

        return $groupedByNotifCode;
    }

    public function createNotificationTemplate(
        string $notificationCode,
        string $platform,
        string $locale,
        string $template,
        ?array $platformPayload = null,
    ) {
        $platformPayload = $platformPayload ? json_encode(
            $platformPayload,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE
        ) : null;

        $insertResult = $this->query
            ->table('mod_lkn_hook_notification_localized_tpls')
            ->insert([
                'notif_code' => $notificationCode,
                'platform' => $platform,
                'lang' => $locale,
                'tpl' => $template,
                'platform_payload' => $platformPayload,
            ]);

        return $insertResult;
    }

    // public function getEnabledNotifications()
    // {
    //     $standardNotifs = array_column(
    //         $this->query
    //             ->table('mod_lkn_hook_notification_localized_tpls')
    //             ->select('notif_code')
    //             ->distinct()
    //             ->get()
    //             ->toArray(),
    //         'notif_code'
    //     );

    //     $wpNotifs = array_column(
    //         lkn_hn_config(Settings::WP_MSG_TEMPLATE_ASSOCS),
    //         'notification'
    //     );

    //     return array_merge($standardNotifs, $wpNotifs);
    // }

    public function deleteNotificationTemplate(
        string $notificationCode,
        string $templateLocale,
        ?string $platform = null
    ): bool {
        $query = $this->query
            ->table('mod_lkn_hook_notification_localized_tpls')
            ->where('notif_code', $notificationCode)
            ->where('lang', $templateLocale);

        if ($platform !== null) {
            $query = $query->where('platform', $platform);
        }

        return $query->limit(1)->delete();
    }
}
