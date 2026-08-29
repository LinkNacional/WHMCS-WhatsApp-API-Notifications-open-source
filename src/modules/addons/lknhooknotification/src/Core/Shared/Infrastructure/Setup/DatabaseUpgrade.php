<?php

namespace Lkn\HookNotification\Core\Shared\Infrastructure\Setup;

use Lkn\HookNotification\Core\Notification\Infrastructure\Repositories\NotificationRepository;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use Lkn\HookNotification\Core\Shared\Infrastructure\Hooks;
use Throwable;
use WHMCS\Database\Capsule;

final class DatabaseUpgrade
{
    public static function v230(): void
    {
        $query = Capsule::table('mod_lkn_hook_notification_configs')
            ->where('platform', Platforms::WHATSAPP->value)
            ->where('setting', 'send-to-chatwoot');

        if ($query->exists()) {
            $query = $query->update([
                'platform' => Platforms::CHATWOOT->value,
                'setting' => Settings::CW_LISTEN_WHATSAPP->value,
            ]);
        }
    }

    public static function v200(): void
    {
        // 1. Renammes table
        Capsule::schema()->rename(
            'mod_lkn_hook_notification_platform_settings',
            'mod_lkn_hook_notification_configs'
        );

        // 2. migrates module settings to the table
        $oldConfigs = (array) Capsule::table('tbladdonmodules')
            ->where('module', 'lknhooknotification')
            ->get(['setting', 'value']);

        $updateConfig = function ($setting, $platform, $value): void {
            Capsule::table('mod_lkn_hook_notification_configs')
                ->insert([
                    'platform' => $platform,
                    'setting' => $setting,
                    'value' => $value,
                ]);
        };

        foreach ($oldConfigs as $data) {
            $newSettingName = match ($data->setting) {
                'custom_field_id_whatsapp' => [
                    'setting' => Settings::WP_CUSTOM_FIELD_ID,
                    'platform' => Platforms::WHATSAPP,
                ],
                'whatsapp_user_access_token' => [
                    'setting' => Settings::WP_USER_ACCESS_TOKEN,
                    'platform' => Platforms::WHATSAPP,
                ],
                'whatsapp_phone_number_id' => [
                    'setting' => Settings::WP_PHONE_NUMBER_ID,
                    'platform' => Platforms::WHATSAPP,
                ],
                'chatwoot_url' => [
                    'setting' => Settings::CW_URL,
                    'platform' => Platforms::CHATWOOT,
                ],
                'chatwoot_api_access_token' => [
                    'setting' => Settings::CW_API_ACCESS_TOKEN,
                    'platform' => Platforms::CHATWOOT,
                ],
                'chatwoot_account_id' => [
                    'setting' => Settings::CW_ACCOUNT_ID,
                    'platform' => Platforms::CHATWOOT,
                ],
                'chatwoot_whatsapp_inbox_id' => [
                    'setting' => Settings::CW_WHATSAPP_INBOX_ID,
                    'platform' => Platforms::CHATWOOT,
                ],
                'enable_debug' => [
                    'setting' => Settings::ENABLE_LOG,
                    'platform' => Platforms::MODULE,
                ],
                default => false
            };

            if (is_array($newSettingName)) {
                $updateConfig(
                    $newSettingName['setting']->value,
                    $newSettingName['platform']->value,
                    $data->value
                );
            }
        }

        // 3. Migrates assocs saving format
        $assocs = Capsule::table('mod_lkn_hook_notification_configs')
            ->where('platform', 'whatsapp')
            ->where('setting', 'msg_templates_assoc')
            ->first('value')
            ->value;

        $newAssocs = array_map(function ($assoc): array {
            if ($assoc['hook_id'] === 4) {
                $hook = 'InvoiceReminder';
            } elseif ($assoc['hook_id'] === 5) {
                $hook = 'InvoiceReminderPdf';
            } else {
                $hook = 'OrderCreated';
            }

            $body = array_map(function ($param): array {
                return [
                    'key' => $param['key'],
                    'value' => $param['replace'],
                ];
            }, $assoc['components']['body']);

            if (!empty($assoc['components']['header'])) {
                $header = [
                    'type' => $assoc['components']['header']['type'],
                    'value' => $assoc['components']['header']['replace'],
                ];
            }

            if (!empty($assoc['components']['btn'])) {
                $assocBtn = $assoc['components']['btn'];

                $button = [
                    [
                        'index' => 1,
                        'type' => $assocBtn['type'],
                        'params' => [
                            [
                                'key' => 1,
                                'type' => $assocBtn['type'],
                                'value' => $assocBtn['paramReplace'],
                            ],
                        ],
                    ],
                ];
            }

            $newAssocs = [
                'hook' => $hook,
                'template' => $assoc['tpl_name'],
                'components' => [],
            ];

            if (isset($header)) {
                $newAssocs['components']['header'] = $header;
            }

            $newAssocs['components']['body'] = $body;

            if (isset($button)) {
                $newAssocs['components']['button'] = $button;
            }
            return $newAssocs;
        }, json_decode($assocs, true));

        Capsule::table('mod_lkn_hook_notification_configs')
            ->where('platform', 'whatsapp')
            ->where('setting', 'msg_templates_assoc')
            ->update(['value' => json_encode($newAssocs)]);
    }

    public static function v310(): void
    {
        $assocs = lkn_hn_config(Settings::WP_MSG_TEMPLATE_ASSOCS);

        $newAssocsFormat = array_map(
            function ($assoc): array {
                if (empty($assoc['components']['header'])) {
                    $header = [];
                } else {
                    $header = [
                        [
                            'key' => '1',
                            'type' => $assoc['components']['header']['type'] === 'doc' ? 'document' : 'text',
                            'value' => $assoc['components']['header']['value'],
                        ],
                    ];
                }

                if (empty($assoc['components']['body'])) {
                    $body = [];
                } else {
                    $body = array_map(
                        function (array $assoc): array {
                            return [
                                'key' => $assoc['key'],
                                'value' => $assoc['value'],
                                'type' => 'text',
                            ];
                        },
                        $assoc['components']['body']
                    );
                }

                if (empty($assoc['components']['button'])) {
                    $buttons = [];
                } else {
                    $buttons = array_map(
                        function (array $btn): array {
                            $params = array_map(
                                function (array $param): array {
                                    return [
                                        'key' => $param['key'],
                                        'value' => $param['value'],
                                    ];
                                },
                                $btn['params']
                            );

                            return [
                                'index' => $btn['index'],
                                'type' => $btn['type'],
                                'params' => $params,
                            ];
                        },
                        $assoc['components']['button']
                    );
                }

                return [
                    'notification' => $assoc['notification'],
                    'template' => $assoc['template'],
                    'components' => [
                        'header' => $header,
                        'body' => $body,
                        'button' => $buttons,
                    ],
                ];
            },
            $assocs
        );

        lkn_hn_config_set(
            Platforms::WHATSAPP,
            Settings::WP_MSG_TEMPLATE_ASSOCS,
            json_encode($newAssocsFormat, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    public static function v320(): void
    {
        Capsule::connection()->statement('ALTER TABLE mod_lkn_hook_notification_reports MODIFY client_id INT(10) NULL');
        Capsule::connection()->statement('ALTER TABLE mod_lkn_hook_notification_reports MODIFY category VARCHAR(20) NULL');
        Capsule::connection()->statement('ALTER TABLE mod_lkn_hook_notification_reports MODIFY category_id BIGINT UNSIGNED NULL');

        $newIdentifierHash         = md5(time());
        $chatwootModIdentifierHash = null;

        if (Capsule::schema()->hasTable('mod_chatwoot')) {
            $modChatwootSigningHash = Capsule::table('mod_chatwoot')->where('setting', 'signing_hash')->first('value')->value;

            if (!is_null($modChatwootSigningHash)) {
                $chatwootModIdentifierHash = $modChatwootSigningHash;
            }
        }

        $identifierHash = $chatwootModIdentifierHash ?? $newIdentifierHash;

        lkn_hn_config_set(Platforms::CHATWOOT, Settings::CW_CLIENT_IDENTIFIER_KEY, $identifierHash);
    }

    public static function v330(): void
    {
        $activeChatwootNotifs = json_decode(Capsule::table('mod_lkn_hook_notification_configs')
            ->where('platform', Platforms::CHATWOOT->value)
            ->where('setting', Settings::CW_ACTIVE_NOTIFS->value)
            ->value('value'), true);

        if (!is_array($activeChatwootNotifs)) {
            return;
        }

        $activeChatwootNotifs = array_map(function (string $item) {
            return [
                'code' => $item,
                'settings' => [],
            ];
        }, $activeChatwootNotifs);

        Capsule::table('mod_lkn_hook_notification_configs')
            ->where('platform', Platforms::CHATWOOT->value)
            ->where('setting', Settings::CW_ACTIVE_NOTIFS->value)
            ->update(['value' => json_encode($activeChatwootNotifs)]);
    }

    public static function v370(): void
    {
        $assocs      = lkn_hn_config(Settings::WP_MSG_TEMPLATE_ASSOCS);
        $defaultLang = lkn_hn_config(Settings::WP_MSG_TEMPLATE_LANG) ?? 'pt_BR';

        $newAssocsFormat = array_map(
            function ($assoc) use ($defaultLang): array {
                return [
                    'notification' => $assoc['notification'],
                    'language' => $defaultLang,
                    'template' => $assoc['template'],
                    'components' => [
                        'header' => $assoc['components']['header'],
                        'body' => $assoc['components']['body'],
                        'button' => $assoc['components']['button'],
                    ],
                ];
            },
            $assocs
        );

        lkn_hn_config_set(Platforms::WHATSAPP, Settings::WP_MSG_TEMPLATE_ASSOCS, $newAssocsFormat);
    }

    public static function v380(): void
    {
        $pdo = Capsule::connection()->getPdo();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'CREATE TABLE IF NOT EXISTS mod_lkn_hook_notification_localized_tpls (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    notif_code VARCHAR(255) NOT NULL,
                    lang VARCHAR(255) NOT NULL,
                    tpl LONGTEXT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );

        $statement->execute();
    }

    public static function v390(): void
    {
        $pdo = Capsule::connection()->getPdo();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'ALTER TABLE `mod_lkn_hook_notification_localized_tpls` ADD `platform` VARCHAR(255) NOT NULL AFTER `notif_code`;'
        );

        $statement->execute();
    }

    public static function v400()
    {
        try {
            $pdo = Capsule::connection()->getPdo();
            $pdo->beginTransaction();

            $statement = $pdo->prepare(
                'CREATE TABLE IF NOT EXISTS mod_lkn_hook_notification_bulks (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    `status` VARCHAR(255) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    `description` TEXT,
                    platform VARCHAR(255) NULL,
                    template TEXT NOT NULL,
                    start_at DATETIME NOT NULL,
                    max_concurrency INT NOT NULL,
                    filters TEXT null,
                    progress FLOAT DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    completed_at DATETIME NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );

            $statement->execute();

            $statement = $pdo->prepare(
                'CREATE TABLE IF NOT EXISTS mod_lkn_hook_notification_notif_queue (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    bulk_id INT NULL,
                    client_id INT NOT NULL,
                    `status` VARCHAR(255) NOT NULL,
                    notif_code VARCHAR(255) NULL,
                    FOREIGN KEY (bulk_id) REFERENCES mod_lkn_hook_notification_bulks(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );

            $statement->execute();

            Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_reports
                ADD COLUMN target VARCHAR(255) NULL AFTER category
            ');

            Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_reports
                ADD COLUMN msg TEXT NULL AFTER status
            ');

            Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_reports
                MODIFY COLUMN platform VARCHAR(255) NULL
            ');

            Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_reports
                ADD COLUMN queue_id INT NULL AFTER category_id
            ');

            Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_reports
                ADD CONSTRAINT fk_queue_id
                FOREIGN KEY (queue_id) REFERENCES mod_lkn_hook_notification_notif_queue(id)
                ON DELETE SET NULL
            ');

            Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_localized_tpls
                MODIFY COLUMN platform VARCHAR(255) NULL
            ');

            Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_localized_tpls
                ADD COLUMN platform_payload TEXT NULL AFTER platform
            ');

            Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_localized_tpls
                MODIFY COLUMN tpl LONGTEXT NULL
            ');

            Capsule::table('mod_lkn_hook_notification_configs')
                ->limit(1)
                ->insert([
                    'platform' => 'wp',
                    'setting' => Settings::WP_META_ENABLE->value,
                    'value' => 'on',
                ]);

            Capsule::table('mod_lkn_hook_notification_configs')
                ->where('setting', 'phone_number_id')
                ->limit(1)
                ->update(['setting' => 'business_phone_number_id']);

            Capsule::table('mod_lkn_hook_notification_localized_tpls')
                ->where('lang', 'en_GB')
                ->update(['lang' => 'en_001']);

            Capsule::table('mod_lkn_hook_notification_localized_tpls')
                ->where('lang', 'en')
                ->update(['lang' => 'en_001']);

            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            $wpLegacyAssoc   = lkn_hn_config(Settings::WP_MSG_TEMPLATE_ASSOCS);
            $newFormatAssocs = [];

            foreach ($wpLegacyAssoc as $assoc) {
                $hookExists = Hooks::tryFrom($assoc['notification']);

                $notificationCode = $hookExists
                    ? 'Default' . strtoupper($hookExists->value)
                    : $assoc['notification'];

                $newFormatAssocs[] = [
                    'notif_code' => $notificationCode,
                    'platform' => Platforms::WHATSAPP->value,
                    'lang' => $assoc['language'],
                    'tpl' => $assoc['template'],
                    'platform_payload' => $assoc['components'],
                ];
            }

            $notificationRepository = new NotificationRepository();
            $results                = [];

            foreach ($newFormatAssocs as $newFormat) {
                $results[] = $notificationRepository->createNotificationTemplate(
                    $newFormat['notif_code'],
                    $newFormat['platform'],
                    $newFormat['lang'],
                    $newFormat['tpl'],
                    $newFormat['platform_payload'],
                );
            }

            lkn_hn_log('Database 4.0.0 upgrade', null, $results);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            lkn_hn_log('Database 4.0.0 upgrade failed', null, $e->__toString());
        }
    }

    public static function v412(): void
    {
        try {
            $result = Capsule::connection()->statement('
                ALTER TABLE `mod_lkn_hook_notification_reports` CHANGE `category_id` `category_id` BIGINT UNSIGNED NULL DEFAULT NULL;
            ');

            $result2 = Capsule::connection()->statement('
                ALTER TABLE `mod_lkn_hook_notification_reports` CHANGE `category` `category` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
            ');

            lkn_hn_log('Database 4.1.2 success', null, ['result' => $result, 'result2'=> $result2]);
        } catch (Throwable $th) {
            lkn_hn_log('Database 4.1.2 upgrade failed', null, $th->__toString());
        }
    }

    public static function v430(): void
    {
        try {
            $result1 = Capsule::connection()->statement('
                ALTER TABLE mod_lkn_hook_notification_bulks
                ADD COLUMN platform_payload TEXT NULL AFTER platform
            ');

            lkn_hn_log('Database 4.3.0 success', null, ['result1' => $result1]);
        } catch (Throwable $th) {
            lkn_hn_log('Database 4.3.0 upgrade failed', null, $th->__toString());
        }
    }

    // Fase 1 (hardening): dedicated table for per-IP rate limiting of the
    // password reset endpoint. Idempotent (IF NOT EXISTS), no destructive migration.
    public static function v451(): void
    {
        try {
            $pdo = Capsule::connection()->getPdo();
            $pdo->beginTransaction();

            $statement = $pdo->prepare(
                'CREATE TABLE IF NOT EXISTS mod_lkn_hook_notification_password_reset_attempts (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    ip VARCHAR(45) NOT NULL,
                    client_id INT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ip_created (ip, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );

            $statement->execute();

            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            lkn_hn_log('Database 4.5.1 success', null, ['password_reset_attempts' => true]);
        } catch (Throwable $th) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            lkn_hn_log('Database 4.5.1 upgrade failed', null, $th->__toString());
        }
    }
}
