<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure;

use Lkn\HookNotification\Core\Shared\Infrastructure\Repository\BaseRepository;

final class TelegramLinkRepository extends BaseRepository
{
    private const TABLE = 'mod_lkn_hook_notification_telegram_links';

    public function findByClientId(int $clientId): ?object
    {
        return $this->query->table(self::TABLE)
            ->where('client_id', $clientId)
            ->first();
    }

    public function findByChatId(string $chatId): ?object
    {
        return $this->query->table(self::TABLE)
            ->where('chat_id', $chatId)
            ->first();
    }

    public function upsert(int $clientId, string $chatId, ?string $username): void
    {
        $now = date('Y-m-d H:i:s');

        $this->query->table(self::TABLE)->updateOrInsert(
            ['client_id' => $clientId],
            [
                'chat_id' => $chatId,
                'username' => $username,
                'linked_at' => $now,
                'consent_at' => $now,
                'last_error' => null,
            ]
        );
    }

    public function deleteByClientId(int $clientId): int
    {
        return $this->query->table(self::TABLE)
            ->where('client_id', $clientId)
            ->delete();
    }

    public function deleteByChatId(string $chatId): int
    {
        return $this->query->table(self::TABLE)
            ->where('chat_id', $chatId)
            ->delete();
    }

    public function setLastError(int $clientId, ?string $error): void
    {
        $this->query->table(self::TABLE)
            ->where('client_id', $clientId)
            ->update(['last_error' => $error]);
    }

    public function all(): array
    {
        return $this->query->table(self::TABLE)
            ->orderBy('linked_at', 'desc')
            ->get()
            ->toArray();
    }
}
