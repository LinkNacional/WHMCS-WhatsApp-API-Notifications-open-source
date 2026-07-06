<?php

/**
 * Implemented by any custom notification that supports being resent from the
 * Notification Reports admin page.
 *
 * This is an INTERFACE, not a notification class, so the module's
 * Custom/*.php auto-discovery (which instantiates every newly-declared
 * CLASS in this folder) safely skips it.
 */

namespace Lkn\HookNotification\Notifications\Custom;

interface ResendableNotificationInterface
{
    /**
     * Rebuilds the $whmcsHookParams array for a single target, fresh from
     * the database, so NotificationSender::send() can resend the message
     * without needing the original payload to have been stored anywhere.
     *
     * @param  int      $categoryId The report's category_id (e.g. invoice ID, domain ID).
     * @param  int|null $clientId   The report's client_id, in case it's needed to disambiguate.
     *
     * @return array<string, mixed>|null Null if the record no longer exists / can't be resent.
     */
    public function buildResendPayload(int $categoryId, ?int $clientId): ?array;
}
