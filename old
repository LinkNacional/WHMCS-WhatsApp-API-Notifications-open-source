# 🚀 WHMCS WhatsApp Notifications Module (Meta Cloud API, Evolution, Baileys) - Now 100% FREE and Open Source!

Free Addon for [WHMCS WhatsApp Notifications](https://www.linknacional.com.br/whmcs/whatsapp/) Module for Meta, Evolution API and Baileys. Send Free WhatsApp Message Automatic by Hook, Manual or Custom.

![Static Badge](https://img.shields.io/badge/version-v4.3.3-blue)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
![Static Badge](https://img.shields.io/badge/made_with-PHP-purple)
![Static Badge](https://img.shields.io/badge/made_with-smarty-yellow)


## ✅ Requirements

- PHP: 8.1+;
- WHMCS: 8.6+
- Data Base SQL with permissions
  - ALTER
  - CREATE
  - DELETE
  - INDEX
  - LOCK TABLES
  - SELECT
  - DROP
  - INSERT
  - REFERENCES
  - UPDATE



## 💻 Installation Mode

### 📥 Download

1. Download the module `notifications.zip` file [here](https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/releases).
2. Make sure to keep the file intact until you're ready to upload it to your WHMCS installation.


### 📤 Upload

1. Upload the file `notifications.zip` to the root installation directory of your WHMCS.
    - For example, if WHMCS is installed in `public_html`, upload the file `notifications.zip` to `public_html`.
2. Extract the file `notifications.zip`.
    -  it will add a folder in WHMCS/modules/addons/lknhooknotification.
3. Remove the remaining notifications.zip.
4. Go to the WHMCS admin panel, select Options > Addon Modules, find the module and, once found, click "Activate".

> 🔎 Tip: For more information on how to install Addon Modules in WHMCS, see: https://docs.whmcs.com/8-13/addon-modules/addon-modules/


## 🔁 Update Mode

1. Before starting, backup your WHMCS and database — this ensures safety in case something goes wrong.
2. Access the WHMCS admin panel and deactivate the module, it not going to erase your database or module configuration, but will make sure that the module do not run and you area updating.
3. Go to this [link](https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/releases) and download the `notifications.zip` file of the latest module release.
4. Extract the file `notifications.zip`.
    -  it will add a folder in WHMCS/modules/addons.
5. Remove the remaining .zip.
6. Go to the WHMCS admin panel, select Options > Addon Modules, find the module and, once found, click "Activate".

## 📖 Usage and Configuration

1. Access this [link](https://github.com/LinkNacional/WHMCS-WhatsApp-API-Notifications-open-source/wiki) for more information on how to use and configure the notifications.


## ❌ Uninstalling the module

1. Go to your WHMCS admin panel, navigate to Options > Addon Modules, find the WhatsApp and Chatwoot module and deactivate it.
2. Access the root directory of your WHMCS, and go to the folder: /modules/addons.
3. Inside the Addons folder, locate and delete the lknhooknotification folder.
4. Delete the table created by the module in your WHMCS database; look for tables with names similar to the module, such as: mod_lkn_hook_notification\*.
    - SQL: BE CAREFULL. Run SQL commands:

``` bash
DROP TABLE IF EXISTS `mod_lkn_hook_notification_bulks`;
DROP TABLE IF EXISTS `mod_lkn_hook_notification_localized_tpls`;
DROP TABLE IF EXISTS `mod_lkn_hook_notification_notif_queue`;
DROP TABLE IF EXISTS `mod_lkn_hook_notification_reports`;
DROP TABLE IF EXISTS `mod_lkn_hook_notification_configs`;
DROP TABLE IF EXISTS `mod_lkn_hook_notification_config`;
```

## 📁 Documentation For Developers

For types documentation, follow https://phpstan.org/writing-php-code/phpdoc-types.

### New Version Release Process

1. Generate stubs
2. Update the hardcoded current version
3. Generate documentation

### Manual Stub Generation

You need to run php stubs_generator.php to generate the code stubs, then copy the contents of `stubs.php` and paste them into the [stubs.php](https://github.com/LinkNacional/whmcs-whatsapp-api-notifications-open-source/blob/refactor-3.0.0/stubs.php) file in the notifications repository.

Additionally, the dependency used to generate stubs does not support Enums, so it's necessary to manually copy the contents of the files under the /Config directory and paste them into `stubs.php`.

### Documentation Generation

By running the command below, both internal and public documentation will be generated:

`php phpDocumentor.phar --config=phpdoc.private.xml && php phpDocumentor.phar --config=phpdoc.public.xml`

You need to push the /docs/public folder to the notifications repository on the main branch.

To view the internal documentation page, simply open the index.html file in /docs/public in your browser.

## More Documentation for Development

- WHMCS Addon Modules: https://developers.whmcs.com/addon-modules/
- WHMCS Hook Index: https://developers.whmcs.com/hooks/hook-index/
- Bootstrap 3.4: https://getbootstrap.com/docs/3.4/components/
- Bootstrap 3.4: https://getbootstrap.com/docs/3.4/css/
- Bootstrap 3.4: https://getbootstrap.com/docs/3.4/javascript/
- FontAwesome 5: https://fontawesome.com/v5/search

### Integrations with the Platforms

#### WhatsApp API

- [Graph API WhatsApp](https://developers.facebook.com/docs/graph-api/reference/whats-app-business-account/message_templates)
- [Understanding the MessageTemplateParser class](https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages)
- [Understanding the Request to the WhatsApp API](https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages)
- [Send message template](https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-message-templates#text-based)
- [List message template](https://developers.facebook.com/docs/whatsapp/business-management-api/message-templates/#retrieve-templates)

#### Chatwoot

**How to test the integration**

- Create a free test account here. Look: https://www.chatwoot.com/docs/user-guide/setup-your-account/create-an-account/#i-am-using-the-cloud-version
- https://www.chatwoot.com/docs/product/channels/api/send-messages/
- About the Chatwoot APIs: https://www.chatwoot.com/docs/contributing-guide/chatwoot-apis

The one used by the module to send messages is the Application API.

##### Live Chat

- https://www.chatwoot.com/developers/api/#tag/Custom-Attributes/operation/add-new-custom-attribute-to-account
- https://www.chatwoot.com/hc/user-guide/articles/1677580558-website-live-chat-settings-explained
- https://www.chatwoot.com/hc/user-guide/articles/1677502327-how-to-create-and-use-custom-attributes

### Notification Types

#### Automatic Trigger

These are the notifications that are automatically executed along with a WHMCS hook.

#### Manual Trigger

Manual trigger notifications require an administrator action to be executed.
They typically use a WHMCS hook ending in Output to display a button that, when clicked, triggers the notification.

In this case, the module simply provides a way to integrate notifications that use the same output hook.
Currently, there are only two notifications of this type: the InvoiceReminder notifications, which use the AdminInvoicesControlsOutput hook.

#### Custom Notifications

They are placed here: `/modules/addons/lknhooknotification/src/Notifications/Custom`

#### Notificações que rodam nos hooks de cron

They inherit the class. `AbstractCronNotification`.

## Testing the Integrations

### Evolution API

[Evolution API Docs](https://doc.evolution-api.com/v2/api-reference/get-information)

To test, just set up locally or use ngrok:

```bash
docker run --net=host -it -e NGROK_AUTHTOKEN={NGROK_AUTHTOKEN} ngrok/ngrok:latest http 8080
```

Copy the given URL and place it in the Evolution API configuration in the module.

### Baileys API

- https://github.com/WhiskeySockets/Baileys
- https://baileys.wiki/docs/intro/

To test, just set up locally or use ngrok:

```bash
docker run --net=host -it -e NGROK_AUTHTOKEN={NGROK_AUTHTOKEN} ngrok/ngrok:latest http 8080
```

Copy the given URL and place it in the Baileys API in the module.

[Using ngrok with Docker](https://ngrok.com/docs/using-ngrok-with/docker/)
ping

```php
[
    {
        "messagename": "Password Reset Validation",
        "relid": 1,
        "mergefields": {
            "user_first_name": "Jorge",
            "user_last_name": "Mendes",
            "user_email": "xxxxx@linknacional.com",
            "reset_password_url": "https://www.WHMCSDOMAIN.com/index.php?rp=/password/reset/redeem/c96b9b7e2248870f4e8933009399232ce5d06a5e649cba2c6e6fd9c56f402c7f",
            "company_name": "Link Nacional",
            "companyname": "Link Nacional",
            "company_domain": "https://www.WHMCSDOMAIN.com",
            "company_logo_url": "",
            "company_tax_code": null,
            "whmcs_url": "https://www.WHMCSDOMAIN.com/",
            "whmcs_link": "<a href=\"https://www.WHMCSDOMAIN.com/\">https://www.WHMCSDOMAIN.com/</a>",
            "signature": "---<br />\r\nLink Nacional<br />\r\nhttp://www.WHMCSDOMAIN.com",
            "date": "Tuesday, 20th May 2025",
            "time": "10:22am",
            "charset": "utf-8"
        }
    }
]
```
