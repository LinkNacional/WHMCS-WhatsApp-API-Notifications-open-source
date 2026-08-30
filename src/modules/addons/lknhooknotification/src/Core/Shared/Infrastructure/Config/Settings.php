<?php

namespace Lkn\HookNotification\Core\Shared\Infrastructure\Config;

enum Settings: string
{
    case WP_MSG_TEMPLATE_ASSOCS                 = 'msg_templates_assoc';
    case WP_MESSAGE_TEMPLATES                   = 'message_templates';
    case WP_CUSTOM_FIELD_ID_OLD                 = 'custom_field_id';
    case WP_BUSINESS_ACCOUNT_ID                 = 'business_account_id';
    case WP_PHONE_NUMBER_ID                     = 'business_phone_number_id';
    case WP_USER_ACCESS_TOKEN                   = 'user_access_token';
    case WP_SHOW_INVOICE_REMINDER_BTN_WHEN_PAID = 'show_invoice_reminder_btn';
    case WP_USE_TICKET_WHATSAPP_CF_WHEN_SET     = 'wp_use_ticket_whatsapp_cf_when_set';
    case WP_MSG_TEMPLATE_LANG                   = 'wp_msg_template_lang';
    case WP_VERSION                             = 'wp_api_version';
    case WP_META_ENABLE                         = 'wp_meta_enable';

    case CW_ACCOUNT_ID         = 'account_id';
    case CW_URL                = 'url';
    case CW_WHATSAPP_INBOX_ID  = 'wp_inbox_id';
    case CW_FACEBOOK_INBOX_ID  = 'fb_inbox_id';
    case CW_API_ACCESS_TOKEN   = 'api_access_token';
    case CW_LISTEN_WHATSAPP    = 'listen_wp';
    case CW_ACTIVE_NOTIFS      = 'active_notifs';
    case CW_ENABLE_LIVE_CHAT   = 'cw_enable_live_chat';
    case CW_LIVE_CHAT_SCRIPT   = 'cw_live_chat_script';
    case CW_WP_CUSTOM_FIELD_ID = 'cw_wp_custom_field_id';
    /**
     * This one is generated on DatabaseSetup.
     */
    case CW_CLIENT_IDENTIFIER_KEY          = 'cw_client_identifier_key';
    case CW_LIVE_CHAT_USER_IDENTITY_TOKEN  = 'cw_live_chat_user_identity_token';
    case CW_CLIENT_STATS_TO_SEND           = 'cw_live_chat_client_stats_to_send';
    case CW_CUSTOM_FIELDS_TO_SEND          = 'cw_live_chat_custom_fields_to_send';
    case CW_LIVE_CHAT_MODULE_ATTRS_TO_SEND = 'cw_live_chat_modules_attrs_to_send';
    case CW_ENABLED                        = 'enable_chatwoot';
    case CW_PRIVATE_NOTE_MODE              = 'cw_private_note_mode';

    case WP_EVO_ENABLE                    = 'enable_wp_evo';
    case WP_EVO_INSTANCE_NAME             = 'wp_evo_instance_name';
    case WP_EVO_API_URL                   = 'api_url';
    case WP_EVO_API_KEY                   = 'api_key';
    case WP_EVO_WP_NUMBER_CUSTOM_FIELD_ID = 'wp_number_custom_field_id';
    case WP_EVO_ACTIVE_NOTIFS             = 'active_wp_evo_notifs';

    case BAILEYS_ENABLE             = 'enable_baileys';
    case BAILEYS_ENDPOINT_URL       = 'baileys_endpoint_url';
    case BAILEYS_API_KEY            = 'baileys_api_key';
    case BAILEYS_WP_CUSTOM_FIELD_ID = 'baileys_wp_custom_field_id';

    case ENABLE_LOG                        = 'enable_log';
    case LKN_LICENSE                       = 'lkn_license';
    case DEFAULT_CLIENT_NAME               = 'default_client_name';
    case LATEST_VERSION                    = 'latest_version';
    case NEW_VERSION_DISMISS_ON_ADMIN_HOME = 'new_version_dismiss_on_admin_home';
    case DISMISS_INSTALLATION_WELCOME      = 'dismiss_installation_welcome';
    case OBJECT_PAGES_TO_SHOW_REPORTS      = 'object_pages_to_show_reports';
    case MODULE_PREVIOUS_VERSION           = 'mod_previous_version';
    case MODULE_DISMISS_V400_ALERT         = 'mod_dimiss_v400_alert';

    case TICKET_WP_CUSTOM_FIELD_ID = 'ticket_wp_custom_field_id';
    case WP_CUSTOM_FIELD_ID        = 'wp_custom_field_id';

    case LANGUAGE = 'language';

    case BULK_ENABLE = 'bulk_enable';

    case BD_CUSTOM_FIELD_ID = 'bd_custom_field_id';

    case LOGIN_OTP_EXPIRY_MINUTES       = 'login_otp_expiry_minutes';
    case LOGIN_MAGIC_LINK_ENABLED       = 'login_magic_link_enabled';
    case LOGIN_OTP_MAX_SENDS_PER_PHONE  = 'login_otp_max_sends_per_phone';
    case LOGIN_OTP_SENDS_WINDOW_MINUTES = 'login_otp_sends_window_minutes';
    case LOGIN_OTP_MAX_ATTEMPTS         = 'login_otp_max_attempts';
    case LOGIN_OTP_COOLDOWN_MINUTES     = 'login_otp_cooldown_minutes';
    case LOGIN_OTP_BLOCK_MINUTES        = 'login_otp_block_minutes';
}
