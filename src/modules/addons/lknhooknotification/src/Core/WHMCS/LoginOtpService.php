<?php

namespace Lkn\HookNotification\Core\WHMCS;

/**
 * Login OTP via WhatsApp (passwordless).
 *
 * Fase 1: esqueleto. A implementação real (geração de OTP, rate limit, envio,
 * verificação e criação de sessão via CreateSsoToken) entra na Fase 2.
 */
final class LoginOtpService
{
    /**
     * Envia o OTP para o telefone informado.
     *
     * @return array<mixed>
     */
    public function send(string $phone): array
    {
        return [];
    }

    /**
     * Verifica o OTP informado.
     *
     * @return array<mixed>
     */
    public function verify(string $phone, string $otp): array
    {
        return [];
    }

    /**
     * Verifica um magic link (token de uso único).
     *
     * @return array<mixed>
     */
    public function verifyMagicLink(string $token): array
    {
        return [];
    }
}
