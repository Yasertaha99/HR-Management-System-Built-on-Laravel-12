<?php

namespace App\Contracts\Telegram;

interface TelegramClientInterface
{
    public function sendMessage(string|int $chatId, string $text, ?string $parseMode = 'HTML', array $extra = []): bool;
}
