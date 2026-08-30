<?php

use Lkn\HookNotification\Core\AdminUI\Application\Services\LicenseService;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\ModuleInfo;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;

return [
    'left' => [
        [
            'label' => lkn_hn_lang('Home'),
            'endpoint' => 'home',
            'icon' => 'far fa-home-alt',
        ],
        [
            'label' => lkn_hn_lang('Notifications'),
            'endpoint' => 'notifications',
            'icon' => 'far fa-bell',
        ],
        [
            'label' => lkn_hn_lang('Reports'),
            'endpoint' => 'notification-reports',
            'icon' => 'fal fa-table',
        ],
        [
            'label' => lkn_hn_lang('Bulk Messages'),
            'icon' => 'far fa-mail-bulk',
            'endpoint' => 'bulk/list',
            'show' => lkn_hn_config(Settings::BULK_ENABLE),
        ],
        [
            'label' => lkn_hn_lang('Settings'),
            'icon' => 'far fa-cog',
            'items' => [
                ['divisor' => true, 'title' => lkn_hn_lang('Module'), 'icon' => 'fal fa-comment'],
                [
                    'label' => lkn_hn_lang('General'),
                    'endpoint' => 'platforms/mod/settings',
                    'icon' => 'fal fa-cog',
                ],
                [
                    'label' => lkn_hn_lang('Bulk Message'),
                    'endpoint' => 'platforms/bulk/settings',
                    'icon' => 'fal fa-cog',
                ],
                ['divisor' => true, 'title' => lkn_hn_lang('Platforms'), 'icon' => 'fal fa-comment'],
                [
                    'label' => lkn_hn_lang('Evolution API'),
                    'endpoint' => 'platforms/wp-evo/settings',
                    'icon' => 'fal fa-cog',
                    'block' => LicenseService::getInstance()->mustBlockProFeatures(),
                ],
                [
                    'label' => lkn_hn_lang('Baileys'),
                    'endpoint' => 'platforms/baileys/settings',
                    'icon' => 'fal fa-cog',
                    'block' => LicenseService::getInstance()->mustBlockProFeatures(),
                ],
                [
                    'label' => lkn_hn_lang('WhatsApp Meta'),
                    'endpoint' => 'platforms/wp/settings',
                    'icon' => 'fal fa-cog',
                ],
                ['divisor' => true, 'title' => lkn_hn_lang('Chatwoot'), 'icon' => 'fal fa-comment'],
                [
                    'label' => lkn_hn_lang('Settings'),
                    'endpoint' => 'platforms/cw/settings',
                    'icon' => 'fal fa-cog',
                ],
                [
                    'label' => lkn_hn_lang('Integration'),
                    'endpoint' => 'platforms/cw/live-chat/settings',
                    'icon' => 'fal fa-headset',
                ],
            ],
        ]
    ],
    'right' => [
        [
            'label' => lkn_hn_lang('Help'),
            'icon' => 'far fa-question-circle',
            'items' => [
                [
                    'label' => lkn_hn_lang('Report Error'),
                    'url' => 'https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/issues/new?assignees=&labels=bug%2C+help+wanted&projects=&template=bug_report.md&title=',
                    'external' => true,
                    'icon' => 'far fa-exclamation-triangle',
                ],
                [
                    'label' => lkn_hn_lang('Logs'),
                    'icon' => 'fal fa-bug',
                    'endpoint' => 'logs'
                ],
                [
                    'icon' => 'glyphicon glyphicon-download',
                    'label' => 'v' . ModuleInfo::VERSION,
                    'external' => true,
                    'url' => 'https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/releases'
                ]
            ],
        ],
    ],
];
