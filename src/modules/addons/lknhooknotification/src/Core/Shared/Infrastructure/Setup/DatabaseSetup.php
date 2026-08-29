<?php

namespace Lkn\HookNotification\Core\Shared\Infrastructure\Setup;

use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Platforms;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use Throwable;
use WHMCS\Database\Capsule;

final class DatabaseSetup
{
    public static function activate(): array
    {
        $pdo = Capsule::connection()->getPdo();
        $pdo->beginTransaction();

        try {
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

            $statement = $pdo->prepare(
                'CREATE TABLE IF NOT EXISTS `mod_lkn_hook_notification_reports` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `client_id` int DEFAULT NULL,
                    `category_id` bigint unsigned DEFAULT NULL,
                    `queue_id` int DEFAULT NULL,
                    `category` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `target` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `msg` text COLLATE utf8mb4_unicode_ci,
                    `platform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `channel` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `notification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `hook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `fk_queue_id` (`queue_id`),
                    CONSTRAINT `fk_queue_id` FOREIGN KEY (`queue_id`) REFERENCES `mod_lkn_hook_notification_notif_queue` (`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );

            $statement->execute();

            $statement = $pdo->prepare(
                'CREATE TABLE IF NOT EXISTS mod_lkn_hook_notification_configs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    platform VARCHAR(255) NOT NULL,
                    setting VARCHAR(255) NOT NULL,
                    value LONGTEXT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );

            $statement->execute();

            $statement = $pdo->prepare(
                'CREATE TABLE IF NOT EXISTS mod_lkn_hook_notification_localized_tpls (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    notif_code VARCHAR(255) NOT NULL,
                    platform VARCHAR(255) NOT NULL,
                    platform_payload TEXT NULL,
                    lang VARCHAR(255) NOT NULL,
                    tpl LONGTEXT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
            );

            $statement->execute();

            // Fase 1 (hardening): dedicated table for per-IP rate limiting of the
            // password reset endpoint. Idempotent (IF NOT EXISTS), no destructive migration.
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

            $newIdentifierHash         = bin2hex(random_bytes((24 - (24 % 2)) / 2));
            $chatwootModIdentifierHash = null;

            if (Capsule::schema()->hasTable('mod_chatwoot')) {
                $modChatwootSigningHash = Capsule::table('mod_chatwoot')->where('setting', 'signing_hash')->first('value')->value;

                if (!is_null($modChatwootSigningHash)) {
                    $chatwootModIdentifierHash = $modChatwootSigningHash;
                }
            }

            $identifierHash = $chatwootModIdentifierHash ?? $newIdentifierHash;

            lkn_hn_config_set(
                Platforms::CHATWOOT,
                Settings::CW_CLIENT_IDENTIFIER_KEY,
                $identifierHash
            );

            return ['status' => 'success'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            lkn_hn_log('mod: database table creation', [], $e->getMessage());

            return [
                'status' => 'error',
                'description' => "Unable to create database table: {$e->__toString()}",
            ];
        }
    }

    public static function deactivate(): array
    {
        try {
            return [
                'status' => 'success',
                'description' => 'Module deactivated. This module does not delete its database tables after deactivation.',
            ];
        } catch (Throwable $e) {
            lkn_hn_log('mod: deactivation error', [], $e->getMessage());

            return [
                'status' => 'error',
                'description' => "Unable to deactivate module: {$e->__toString()}",
            ];
        }
    }
}
