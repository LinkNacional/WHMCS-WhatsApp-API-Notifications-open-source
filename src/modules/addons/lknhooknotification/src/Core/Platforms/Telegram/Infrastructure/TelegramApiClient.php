<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Infrastructure;

use Lkn\HookNotification\Core\Shared\Infrastructure\ApiResponse;
use Lkn\HookNotification\Core\Shared\Infrastructure\BaseApiClient;

final class TelegramApiClient extends BaseApiClient
{
    private string $baseUrl;

    public function __construct(private readonly string $botToken)
    {
        $this->baseUrl = 'https://api.telegram.org/bot' . $botToken;
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
            $body
        );
    }

    public function getMe(): ApiResponse
    {
        return $this->httpRequest('GET', $this->baseUrl, 'getMe');
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
            ]
        );
    }

    public function deleteWebhook(): ApiResponse
    {
        return $this->httpRequest(
            'POST',
            $this->baseUrl,
            'deleteWebhook',
            ['Content-Type: application/json'],
            ['drop_pending_updates' => false]
        );
    }

    public function getWebhookInfo(): ApiResponse
    {
        return $this->httpRequest('GET', $this->baseUrl, 'getWebhookInfo');
    }
}
