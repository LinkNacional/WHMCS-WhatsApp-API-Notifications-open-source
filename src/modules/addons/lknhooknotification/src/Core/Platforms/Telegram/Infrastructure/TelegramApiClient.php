<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure;

use Lkn\HookNotification\Core\Shared\Infrastructure\ApiResponse;
use Lkn\HookNotification\Core\Shared\Infrastructure\BaseApiClient;

final class TelegramApiClient extends BaseApiClient
{
    private string $baseUrl;

    /** @var array<int, mixed> */
    private array $proxyOptions = [];

    public function __construct(private readonly string $botToken, array $proxy = [])
    {
        $this->baseUrl = 'https://api.telegram.org/bot' . $botToken;

        $type = strtolower(trim((string) ($proxy['type'] ?? '')));
        $host = trim((string) ($proxy['host'] ?? ''));
        $port = (int) ($proxy['port'] ?? 0);

        if (in_array($type, ['http', 'https', 'socks5', 'socks5h'], true) && $host !== '' && $port > 0) {
            $this->proxyOptions[CURLOPT_PROXY] = $type . '://' . $host . ':' . $port;

            $username = trim((string) ($proxy['username'] ?? ''));
            $password = (string) ($proxy['password'] ?? '');

            if ($username !== '' && $password !== '') {
                $this->proxyOptions[CURLOPT_PROXYUSERPWD] = $username . ':' . $password;
            }
        }
    }

    public function sendMessage(string $chatId, string $text, ?string $parseMode = null): ApiResponse
    {
        $body = [
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ];

        if ($parseMode !== null && $parseMode !== '') {
            $body['parse_mode'] = $parseMode;
        }

        return $this->httpRequest(
            'POST',
            $this->baseUrl,
            'sendMessage',
            ['Content-Type: application/json'],
            $body,
            [],
            $this->proxyOptions
        );
    }

    public function getMe(): ApiResponse
    {
        return $this->httpRequest('GET', $this->baseUrl, 'getMe', [], [], [], $this->proxyOptions);
    }

    public function setWebhook(string $url, string $secret): ApiResponse
    {
        return $this->httpRequest(
            'POST',
            $this->baseUrl,
            'setWebhook',
            ['Content-Type: application/json'],
            [
                'url' => $url,
                'secret_token' => $secret,
                'allowed_updates' => ['message'],
            ],
            [],
            $this->proxyOptions
        );
    }

    public function deleteWebhook(): ApiResponse
    {
        return $this->httpRequest(
            'POST',
            $this->baseUrl,
            'deleteWebhook',
            ['Content-Type: application/json'],
            ['drop_pending_updates' => false],
            [],
            $this->proxyOptions
        );
    }

    public function getWebhookInfo(): ApiResponse
    {
        return $this->httpRequest('GET', $this->baseUrl, 'getWebhookInfo', [], [], [], $this->proxyOptions);
    }
}
