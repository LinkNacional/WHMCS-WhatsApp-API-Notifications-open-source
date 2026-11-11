<?php

namespace Lkn\HookNotification\Core\Platforms\Telegram\Application;

use Lkn\HookNotification\Core\Shared\Infrastructure\Config\Settings;

final class TelegramHandoffService
{
    private const DEFAULT_BUTTON_TEXT = 'Falar com atendimento';
    private const DEFAULT_AUTO_REPLY_TEXT = 'Este canal e apenas para avisos. Para falar com o atendimento, use o botao abaixo.';

    public function isEnabled(): bool
    {
        return (bool) lkn_hn_config(Settings::TELEGRAM_HANDOFF_ENABLE);
    }

    public function isAutoReplyEnabled(): bool
    {
        return (bool) lkn_hn_config(Settings::TELEGRAM_HANDOFF_AUTO_REPLY_ENABLE);
    }

    public function isAutoReplyButtonEnabled(): bool
    {
        return (bool) lkn_hn_config(Settings::TELEGRAM_HANDOFF_AUTO_REPLY_BUTTON_ENABLE);
    }

    public function getButtonText(): string
    {
        $text = trim((string) lkn_hn_config(Settings::TELEGRAM_HANDOFF_BUTTON_TEXT));

        if ($text === '') {
            $text = self::DEFAULT_BUTTON_TEXT;
        }

        return mb_substr($text, 0, 64);
    }

    public function getAutoReplyText(): string
    {
        $text = trim((string) lkn_hn_config(Settings::TELEGRAM_HANDOFF_AUTO_REPLY_TEXT));

        return $text !== '' ? $text : self::DEFAULT_AUTO_REPLY_TEXT;
    }

    public function buildUrl(): ?string
    {
        $destination = strtolower(trim((string) lkn_hn_config(Settings::TELEGRAM_HANDOFF_DESTINATION)));

        if ($destination === 'whatsapp') {
            $number = preg_replace('/\D+/', '', (string) lkn_hn_config(Settings::TELEGRAM_HANDOFF_WHATSAPP_NUMBER));

            if (!preg_match('/^\d{10,15}$/', (string) $number)) {
                return null;
            }

            return 'https://wa.me/' . $number;
        }

        if ($destination === 'telegram_bot') {
            $username = ltrim(trim((string) lkn_hn_config(Settings::TELEGRAM_HANDOFF_TELEGRAM_BOT)), '@');

            if (!preg_match('/^[A-Za-z0-9_]{5,32}$/', $username)) {
                return null;
            }

            return 'https://t.me/' . $username;
        }

        return null;
    }

    /**
     * @return array{inline_keyboard: array<int, array<int, array{text: string, url: string}>>}|null
     */
    public function buildInlineKeyboard(bool $forAutoReply = false): ?array
    {
        if ($forAutoReply) {
            if (!$this->isAutoReplyButtonEnabled()) {
                return null;
            }
        } elseif (!$this->isEnabled()) {
            return null;
        }

        $url = $this->buildUrl();

        if ($url === null) {
            return null;
        }

        return [
            'inline_keyboard' => [
                [
                    [
                        'text' => $this->getButtonText(),
                        'url' => $url,
                    ],
                ],
            ],
        ];
    }
}
