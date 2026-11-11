<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure;

use Lkn\HookNotification\Core\Shared\Infrastructure\Repository\BaseRepository;

final class TelegramLinkTokenRepository extends BaseRepository
{
    private const TABLE = 'mod_lkn_hook_notification_telegram_link_tokens';

    public function create(int $clientId, string $tokenHash, string $expiresAt): void
    {
        $this->query->table(self::TABLE)->insert([
            'client_id' => $clientId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function findValidByHash(string $tokenHash): ?object
    {
        return $this->query->table(self::TABLE)
            ->where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();
    }

    public function markUsed(int $id): void
    {
        $this->query->table(self::TABLE)
            ->where('id', $id)
            ->update(['used_at' => date('Y-m-d H:i:s')]);
    }

    public function deleteUnusedForClient(int $clientId): void
    {
        $this->query->table(self::TABLE)
            ->where('client_id', $clientId)
            ->whereNull('used_at')
            ->delete();
    }
}
