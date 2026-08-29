<?php

namespace Lkn\HookNotification\Core\WHMCS;

use DateTime;
use Lkn\HookNotification\Core\NotificationReport\Domain\NotificationReportStatus;
use Lkn\HookNotification\Core\Notification\Application\NotificationFactory;
use Lkn\HookNotification\Core\Notification\Application\Services\NotificationSender;
use Lkn\HookNotification\Core\Notification\Application\Services\NotificationService;
use Lkn\HookNotification\Core\Platforms\Common\PlatformNotificationSendResult;
use Lkn\HookNotification\Core\Shared\Infrastructure\Result;
use WHMCS\Database\Capsule;

/**
 * @see https://docs.whmcs.com/8-13/clients/users-and-client-accounts/
 */
final class PasswordResetService
{
    private readonly NotificationFactory $notificationFactory;
    private readonly NotificationSender $notificationSender;
    private readonly NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationFactory = NotificationFactory::getInstance();
        $this->notificationSender  = NotificationSender::getInstance();
        $this->notificationService = new NotificationService();
    }

    /**
     * @param  string $email
     *
     * @return array<mixed>
     */
    public function run(string $email): array
    {
        // Fase 2 (hardening): respect the admin toggle for the SafePasswordReset
        // notification. If disabled, do not send anything (email or WhatsApp).
        if (!$this->notificationService->isNotificationEnabled('SafePasswordReset')) {
            return $this->neutralResponse();
        }

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!$email) {
            return $this->neutralResponse();
        }

        /** @var null|string $clientIp */
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $thirtyMinutesAgo = (new DateTime())->modify('-30 minutes');
        $windowStart      = $thirtyMinutesAgo->format('Y-m-d H:i:s');

        // Fase 1 (hardening): rate limit by requester IP (dedicated table), added
        // on top of the existing per-target (client_id) limit below. Guarded by
        // hasTable so the endpoint keeps working if the upgrade has not run yet.
        if (Capsule::schema()->hasTable('mod_lkn_hook_notification_password_reset_attempts')) {
            $ipAttemptsCount = Capsule::table('mod_lkn_hook_notification_password_reset_attempts')
                ->where('ip', $clientIp)
                ->where('created_at', '>=', $windowStart)
                ->count();

            if ($ipAttemptsCount >= 10) {
                return ['exceeded_try' => true];
            }

            Capsule::table('mod_lkn_hook_notification_password_reset_attempts')->insert([
                'ip' => $clientIp,
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            ]);
        }

        /** @var null|object{id: int, email: string, reset_token: string, reset_token_expiry: string} $user */
        $user = Capsule::table('tblusers')->where('email', $email)->first(['reset_token', 'reset_token_expiry', 'id', 'email']);

        /** @var null|object{id: int, email: string} $client */
        $client = Capsule::table('tblclients')->where('email', $email)->first(['id', 'email']);

        if (!$user && !$client) {
            // Fase 1 (hardening): uniform response, avoid account enumeration.
            return $this->neutralResponse();
        }

        $attemptsCount = Capsule::table('mod_lkn_hook_notification_reports')
            ->where('client_id', $user->id ?? $client->id)
            ->where('notification', 'SafePasswordReset')
            ->where('created_at', '>=', $windowStart)
            ->count();

        if ($attemptsCount > 5) {
            return ['exceeded_try' => true];
        }


        /** @var null|string $systemUrl */
        $systemUrl = Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->value('value');

        if (!$systemUrl) {
            return $this->neutralResponse();
        }

        if ($client) {
            // It's just an Client
            return $this->notifyClient($client);
        } elseif ($user) {
            // It's just an User
            return $this->notifyUser($user);
        }

        return $this->neutralResponse();

        // tem cliente, tem usuario, envia whatsapp
        // tem client nao tem usuario, envia email
        // Pesquisar o email na tabela users, verifica se o usuário esta em mais de um cliente em tblusers_clientes.
        // Se estiver em apenas 1 cliente pesquisar na tabela client pelo email e pega o número e envia.
        // Se estiver em mais de um cliente enviar apenas o email de troca de senha.
    }

    /**
     * Fase 1 (hardening): uniform "no action" response to avoid account enumeration.
     *
     * @return array{sent_to_email: null, sent_to_phone: null}
     */
    private function neutralResponse(): array
    {
        return ['sent_to_email' => null, 'sent_to_phone' => null];
    }

    /**
     * @param  object{id: int, email: string, reset_token: string, reset_token_expiry: string} $user
     *
     * @return array{sent_to_email: string, sent_to_phone?: string}
     */
    private function notifyUser(object $user): array
    {
        $userResetToken       = $user->reset_token;
        $userResetTokenExpiry = $user->reset_token_expiry ? new DateTime($user->reset_token_expiry) : null;

        if (!$userResetTokenExpiry || $userResetTokenExpiry < new DateTime()) {
            /** @var array{email: string} $output */
            $output         = localAPI('ResetPassword', ['email' => $user->email]);
            $userResetToken = Capsule::table('tblusers')->where('id', $user->id)->value('reset_token');
        }

        $resetUrl = get_passsword_reset_url_for_user($user->email);

        $sendEmailResult = $this->sendEmail($user->id, $resetUrl);
        /** @var array{sent_to_email: string, sent_to_phone?: string} $result */
        $result = [];

        if ($sendEmailResult) {
            $result['sent_to_email'] = lkn_hn_mask_value($user->email);
        }

        /** @var null|object{id: int, email: string} $client */
        $client = Capsule::table('tblusers_clients')
            ->leftJoin('tblclients', 'tblusers_clients.client_id', '=', 'tblclients.id')
            ->where('tblusers_clients.auth_user_id', $user->id)
            ->where('tblusers_clients.owner', 1)
            ->select('tblclients.id', 'tblclients.email')
            ->first();

        if (!$client) {
            return $result;
        }

        $sendWhatsAppNotificationResult = $this->sendWhatsAppNotification(
            $client->id,
            $client->email,
            $user->id,
            $user->email,
        );

        return $result;
    }

    /**
     * @param  object{id: int, email: string} $client
     *
     * @return array{sent_to_email: string, sent_to_phone: string}
     */
    private function notifyClient(object $client): array
    {
        // Fase 2 (hardening): do NOT reset the password token unconditionally here.
        // Only regenerate it below if the owner user token is missing/expired
        // (parity with notifyUser), so a still-valid link is not invalidated.
        /** @var null|int $clientUserOwnerId */
        $clientUserOwnerId = Capsule::table('tblusers_clients')
            ->where('client_id', $client->id)
            ->where('owner', 1)
            ->value('auth_user_id');

        if (!$clientUserOwnerId) {
            return [];
        }

        /** @var null|object{id: int, email: string, reset_token: string, reset_token_expiry: string} $user */
        $user = Capsule::table('tblusers')->where('id', $clientUserOwnerId)->first(['reset_token', 'reset_token_expiry', 'id', 'email']);

        if (!$user) {
            return [];
        }

        $thirtyMinutesAgo = (new DateTime())->modify('-30 minutes');

        $userResetToken       = $user->reset_token;
        $userResetTokenExpiry = $user->reset_token_expiry ? new DateTime($user->reset_token_expiry) : null;

        if (!$userResetTokenExpiry || $userResetTokenExpiry < new DateTime()) {
            /** @var array{email: string} $output */
            $output         = localAPI('ResetPassword', ['email' => $user->email]);
            $userResetToken = Capsule::table('tblusers')->where('email', $output['email'])->value('reset_token');
        }

        /** @var array{sent_to_email: string, sent_to_phone: string} $result */
        $result = [];

        $sendEmailResult = $this->sendEmail($client->id, $client->email);

        if ($sendEmailResult) {
            $result['sent_to_email'] = lkn_hn_mask_value($client->email);
        }

        $sendWhatsAppNotificationResult = $this->sendWhatsAppNotification(
            $client->id,
            $client->email,
            $user->id,
            $user->email,
        );

        if (
            $sendWhatsAppNotificationResult instanceof PlatformNotificationSendResult
            && $sendWhatsAppNotificationResult->status === NotificationReportStatus::SENT
        ) {
            $result['sent_to_phone'] = lkn_hn_mask_value($sendWhatsAppNotificationResult->target ?? '');
        }

        return $result;
    }

    private function sendWhatsAppNotification(
        int $clientId,
        string $clientEmail,
        ?int $clientUserOwnerId = null,
        ?string $clientUserOwnerEmail = null,
    ): Result|PlatformNotificationSendResult {
        $safePasswordResetNotification = $this->notificationFactory->makeByCode('SafePasswordReset');

        if (!$safePasswordResetNotification) {
            return lkn_hn_result(code: 'unable-to-mount-notification');
        }

        return $this->notificationSender->send(
            $safePasswordResetNotification,
            [
                'client_id' => $clientId,
                'client_email' => $clientEmail,
                'client_user_owner_id' => $clientUserOwnerId,
                'client_user_owner_email' => $clientUserOwnerEmail,
            ]
        );
    }

    private function sendEmail(int $clientId, string $resetUrl): bool
    {
        return sendMessage(
            'Password Reset Validation',
            $clientId,
            ['reset_password_url' => $resetUrl]
        );
    }
}
