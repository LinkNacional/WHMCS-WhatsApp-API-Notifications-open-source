# 🚀 WHMCS WhatsApp Notifications
## Enterprise Open Source Edition (DCTLab)

> A modern, enterprise-grade WhatsApp notification platform for WHMCS with support for multiple providers, delivery tracking, reporting, webhooks, queue processing, analytics, and a developer-friendly architecture.

![Version](https://img.shields.io/badge/version-v5.0.0-blue)
![WHMCS](https://img.shields.io/badge/WHMCS-8.6%2B-green)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple)
![License](https://img.shields.io/badge/license-MIT-success)
![Open Source](https://img.shields.io/badge/Open%20Source-Yes-brightgreen)

---

# ✨ Features

- Meta Cloud API
- Evolution API
- Baileys
- Chatwoot
- Multi-provider architecture
- Queue engine
- Automatic retries
- Scheduled notifications
- Delivery & read tracking
- Webhook processing
- Notification Reports
- Analytics Dashboard
- REST API
- Media Messages
- Manual & Automatic Notifications
- Custom Notifications
- Broadcast Messages
- Provider Failover
- Audit Logs

---

# Requirements

- PHP 8.1+
- WHMCS 8.6+
- MySQL / MariaDB
- Composer
- cURL
- OpenSSL
- JSON Extension

Required SQL privileges:

- CREATE
- ALTER
- INSERT
- UPDATE
- DELETE
- SELECT
- INDEX
- DROP

---

# Supported Providers

| Provider | Status |
|----------|--------|
| Meta Cloud API | ✅ |
| Evolution API | ✅ |
| Baileys | ✅ |
| Chatwoot | ✅ |
| Twilio WhatsApp | Planned |
| UltraMsg | Planned |
| Green API | Planned |
| 360Dialog | Planned |
| WPPConnect | Planned |

---

# Installation

1. Download the latest release.
2. Upload the archive to your WHMCS root.
3. Extract the package.
4. Verify the folder exists:

```
modules/addons/lknhooknotification
```

5. Login to WHMCS.

```
System Settings
→ Addon Modules
→ Activate
```

6. Configure your preferred WhatsApp provider.
7. Configure templates.
8. Enable notification hooks.

---

# Upgrade

1. Backup WHMCS.
2. Backup the database.
3. Disable the addon.
4. Replace module files.
5. Run database migrations.
6. Reactivate the addon.

---

# Architecture

```
Core
Providers
Services
Notifications
Queue
Reports
API
Repositories
Models
Hooks
Helpers
Templates
Assets
Tests
```

---

# Notification Types

## Automatic

Triggered directly from WHMCS hooks.

Examples

- Invoice Created
- Invoice Paid
- Invoice Overdue
- Ticket Opened
- Ticket Reply
- Service Suspension
- Domain Expiry
- Password Reset

## Manual

Administrator initiated.

Examples

- Invoice Reminder
- Payment Reminder
- Custom Message

## Scheduled

Processed by Cron.

## Custom

Create unlimited notifications inside:

```
src/Notifications/Custom/
```

---

# Queue System

Features

- Background processing
- Priority queue
- Automatic retry
- Rate limiting
- Scheduled sending
- Dead queue

---

# Notification Reports

- Every message sent
- Delivery status
- Read receipts
- Failed messages
- Retry history
- API request/response
- Search by Client
- Search by Invoice
- Search by Ticket
- Search by Domain
- Export CSV

---

# Analytics

Dashboard includes

- Messages Sent
- Delivered
- Read
- Failed
- Queue Size
- Provider Performance
- Daily Statistics
- Monthly Statistics

---

# REST API

```
POST /api/messages
GET  /api/messages
GET  /api/reports
POST /api/resend
GET  /api/templates
```

---

# Webhooks

Supported

- Meta Cloud
- Evolution
- Baileys

Webhook events

- Sent
- Delivered
- Read
- Failed

---

# Folder Structure

```
modules/
└── addons/
    └── lknhooknotification/
        ├── src/
        │   ├── Core/
        │   ├── Providers/
        │   ├── Services/
        │   ├── Notifications/
        │   ├── Queue/
        │   ├── Reports/
        │   ├── API/
        │   ├── Models/
        │   ├── Repositories/
        │   └── Tests/
        ├── assets/
        ├── templates/
        └── database/
```

---

# Development

Tools

- PHPUnit
- PHPStan
- PHP-CS-Fixer
- GitHub Actions

---

# Roadmap

## Version 5.0

- Modern architecture
- Queue engine
- Reports
- REST API

## Version 5.1

- Twilio
- UltraMsg
- Broadcast messaging

## Version 5.2

- Plugin Marketplace
- AI Template Assistant
- Flow Builder

---

# Contributing

1. Fork the repository.
2. Create a feature branch.
3. Commit your changes.
4. Submit a Pull Request.

---

# License

MIT License.

---

© DCTLab • Enterprise Open Source Edition
