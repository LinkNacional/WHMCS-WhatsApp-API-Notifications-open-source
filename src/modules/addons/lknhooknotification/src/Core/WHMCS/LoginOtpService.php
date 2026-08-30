<?php

namespace Lkn\HookNotification\Core\WHMCS;

use DateTime;
use Lkn\HookNotification\Core\Notification\Application\NotificationFactory;
use Lkn\HookNotification\Core\Notification\Application\Services\NotificationSender;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;
use Lkn\HookNotification\Core\Shared\Infrastructure\Repository\ClientRepository;
use WHMCS\Database\Capsule;

/**
 * Login OTP via WhatsApp (passwordless).
 *
 * Fase 2: send/verify + sessão passwordless via CreateSsoToken.
 */
final class LoginOtpService
{
    private readonly NotificationFactory $notificationFactory;
    private readonly NotificationSender $notificationSender;
    private readonly ClientRepository $clientRepository;

    public function __construct()
    {
        $this->notificationFactory = NotificationFactory::getInstance();
        $this->notificationSender  = NotificationSender::getInstance();
        $this->clientRepository    = new ClientRepository();
    }

    /**
     * Envia o OTP para o telefone. Resposta uniforme (anti-enumeração).
     *
     * @return array<mixed>
     */
    public function send(string $phone): array
    {
        $phone = $this->normalizePhone($phone);

        if ($phone === '') {
            return $this->neutralResponse();
        }

        $clients = $this->clientRepository->getClientsByPhone($phone);

        // Anti-enumeração: não revela se o telefone existe ou não.
        if (count($clients) === 0) {
            return $this->neutralResponse();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!$this->canSend($phone, $ip)) {
            return $this->neutralResponse();
        }

        $otp           = (string) random_int(100000, 999999);
        $expiryMinutes = $this->otpExpiryMinutes();
        $otpHash       = password_hash($otp, PASSWORD_BCRYPT);

        Capsule::table('mod_lkn_hook_notification_login_otp')->insert([
            'phone'          => $phone,
            'otp_hash'       => $otpHash,
            'otp_expires_at' => (new DateTime())->modify("+{$expiryMinutes} minutes")->format('Y-m-d H:i:s'),
            'attempts'       => 0,
            'ip'             => $ip,
            'status'         => 'pending',
            'created_at'     => (new DateTime())->format('Y-m-d H:i:s'),
        ]);

        $this->sendOtpNotification((int) $clients[0]->id, $otp, $expiryMinutes);

        return $this->neutralResponse();
    }

    /**
     * Verifica o OTP e cria a sessão (passwordless).
     *
     * @param int|null $client_id  quando informado, conclui o login da conta selecionada
     *                             (fluxo de múltiplas contas no mesmo telefone).
     *
     * @return array<mixed>
     */
    public function verify(string $phone, string $otp, ?int $client_id = null): array
    {
        $phone = $this->normalizePhone($phone);

        if ($phone === '' || $otp === '') {
            return ['error' => 'invalid_input'];
        }

        $record = Capsule::table('mod_lkn_hook_notification_login_otp')
            ->where('phone', $phone)
            ->whereIn('status', ['pending', 'verified'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$record) {
            return ['error' => 'no_pending_otp'];
        }

        if ($record->otp_expires_at && new DateTime($record->otp_expires_at) < new DateTime()) {
            Capsule::table('mod_lkn_hook_notification_login_otp')->where('id', $record->id)->update(['status' => 'expired']);

            return ['error' => 'expired'];
        }

        // Validação do OTP acontece apenas na primeira chamada (sem client_id).
        if ($client_id === null) {
            $maxAttempts = (int) (lkn_hn_config(Settings::LOGIN_OTP_MAX_ATTEMPTS) ?? 5);

            if ((int) $record->attempts >= $maxAttempts) {
                return ['error' => 'too_many_attempts'];
            }

            if (!password_verify($otp, $record->otp_hash)) {
                Capsule::table('mod_lkn_hook_notification_login_otp')->where('id', $record->id)->increment('attempts');

                return ['error' => 'invalid_otp'];
            }

            Capsule::table('mod_lkn_hook_notification_login_otp')->where('id', $record->id)->update(['status' => 'verified']);
        }

        $clients = $this->clientRepository->getClientsByPhone($phone);

        if (count($clients) === 0) {
            return ['error' => 'client_not_found'];
        }

        if ($client_id === null && count($clients) > 1) {
            return [
                'need_account_selection' => true,
                'accounts'               => array_map(
                    fn ($client) => [
                        'id'    => (int) $client->id,
                        'email' => lkn_hn_mask_value((string) ($client->email ?? '')),
                    ],
                    $clients
                ),
            ];
        }

        $finalClientId = $client_id ?? (int) $clients[0]->id;

        $valid = false;
        foreach ($clients as $client) {
            if ((int) $client->id === $finalClientId) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            return ['error' => 'invalid_account'];
        }

        Capsule::table('mod_lkn_hook_notification_login_otp')
            ->where('id', $record->id)
            ->update(['status' => 'used', 'client_id' => $finalClientId]);

        return $this->createLoginSession($finalClientId);
    }

    /**
     * Fase 4: verificação de magic link (token de uso único).
     *
     * @return array<mixed>
     */
    public function verifyMagicLink(string $token): array
    {
        return ['error' => 'not_implemented'];
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone) ?? '';
    }

    /**
     * @return array{sent: bool}
     */
    private function neutralResponse(): array
    {
        return ['sent' => true];
    }

    private function otpExpiryMinutes(): int
    {
        $minutes = (int) (lkn_hn_config(Settings::LOGIN_OTP_EXPIRY_MINUTES) ?? 10);

        return max(10, min(20, $minutes));
    }

    private function canSend(string $phone, string $ip): bool
    {
        $now = new DateTime();

        // Cooldown entre reenvios.
        $cooldownMinutes = max(0, (int) (lkn_hn_config(Settings::LOGIN_OTP_COOLDOWN_MINUTES) ?? 1));
        if ($cooldownMinutes > 0) {
            $recent = Capsule::table('mod_lkn_hook_notification_login_otp')
                ->where('phone', $phone)
                ->where('created_at', '>=', $now->modify("-{$cooldownMinutes} minutes")->format('Y-m-d H:i:s'))
                ->exists();

            if ($recent) {
                return false;
            }
        }

        $windowMinutes = max(1, (int) (lkn_hn_config(Settings::LOGIN_OTP_SENDS_WINDOW_MINUTES) ?? 30));
        $maxSends      = max(1, (int) (lkn_hn_config(Settings::LOGIN_OTP_MAX_SENDS_PER_PHONE) ?? 3));
        $windowStart   = (new DateTime())->modify("-{$windowMinutes} minutes")->format('Y-m-d H:i:s');

        $phoneSends = Capsule::table('mod_lkn_hook_notification_login_otp')
            ->where('phone', $phone)
            ->where('created_at', '>=', $windowStart)
            ->count();

        if ($phoneSends >= $maxSends) {
            return false;
        }

        // Rate limit secundário por IP.
        $ipSends = Capsule::table('mod_lkn_hook_notification_login_otp')
            ->where('ip', $ip)
            ->where('created_at', '>=', $windowStart)
            ->count();

        if ($ipSends >= 20) {
            return false;
        }

        return true;
    }

    private function sendOtpNotification(int $clientId, string $otp, int $expiryMinutes): void
    {
        $notification = $this->notificationFactory->makeByCode('LoginOtp');

        if (!$notification) {
            return;
        }

        $this->notificationSender->send(
            $notification,
            [
                'client_id'      => $clientId,
                'otp_code'       => $otp,
                'magic_link_url' => '',
                'expiry_minutes' => $expiryMinutes,
            ]
        );
    }

    /**
     * @return array<mixed>
     */
    private function createLoginSession(int $clientId): array
    {
        $result = localAPI('CreateSsoToken', ['clientid' => $clientId]);

        if (is_array($result) && ($result['result'] ?? '') === 'success' && !empty($result['redirect_url'])) {
            return [
                'logged_in'    => true,
                'redirect_url' => (string) $result['redirect_url'],
            ];
        }

        // Fallback aprovado: sessão via classes oficiais do WHMCS.
        if (class_exists('WHMCS\\Session') && method_exists('WHMCS\\Session', 'createLoginSession')) {
            try {
                \WHMCS\Session::createLoginSession($clientId);

                return [
                    'logged_in'    => true,
                    'redirect_url' => 'clientarea.php',
                ];
            } catch (\Throwable $th) {
                lkn_hn_log('Login OTP: fallback createLoginSession failed', ['clientId' => $clientId], ['exception' => $th->__toString()]);
            }
        }

        lkn_hn_log('Login OTP: CreateSsoToken failed', ['clientId' => $clientId], $result);

        return ['error' => 'login_failed'];
    }
}
