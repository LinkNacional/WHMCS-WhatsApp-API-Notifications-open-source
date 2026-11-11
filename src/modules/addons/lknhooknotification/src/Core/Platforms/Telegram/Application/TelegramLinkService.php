<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Application;

use Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure\TelegramLinkRepository;
use Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure\TelegramLinkTokenRepository;
use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;

final class TelegramLinkService
{
    private TelegramLinkRepository $links;
    private TelegramLinkTokenRepository $tokens;

    public function __construct()
    {
        $this->links  = new TelegramLinkRepository();
        $this->tokens = new TelegramLinkTokenRepository();
    }

    public function generateToken(int $clientId): ?string
    {
        $token     = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $hash      = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 900);

        $this->tokens->deleteUnusedForClient($clientId);
        $this->tokens->create($clientId, $hash, $expiresAt);

        return $token;
    }

    public function buildDeepLink(string $token): ?string
    {
        $username = ltrim((string) lkn_hn_config(Settings::TELEGRAM_BOT_USERNAME), '@');

        if ($username === '') {
            return null;
        }

        return 'https://t.me/' . $username . '?start=' . rawurlencode($token);
    }

    /**
     * @return array{ok: bool, code: string, client_id?: int}
     */
    public function handleStart(string $token, string $chatId, ?string $username): array
    {
        $hash = hash('sha256', $token);
        $row  = $this->tokens->findValidByHash($hash);

        if (!$row) {
            return ['ok' => false, 'code' => 'invalid-token'];
        }

        $existingChat = $this->links->findByChatId($chatId);

        if ($existingChat && (int) $existingChat->client_id !== (int) $row->client_id) {
            return ['ok' => false, 'code' => 'chat-already-linked'];
        }

        $this->links->upsert((int) $row->client_id, $chatId, $username);
        $this->tokens->markUsed((int) $row->id);

        return ['ok' => true, 'code' => 'linked', 'client_id' => (int) $row->client_id];
    }

    public function getLink(int $clientId): ?object
    {
        return $this->links->findByClientId($clientId);
    }

    public function unlinkByClientId(int $clientId): void
    {
        $this->links->deleteByClientId($clientId);
    }

    public function unlinkByChatId(string $chatId): void
    {
        $this->links->deleteByChatId($chatId);
    }
}
