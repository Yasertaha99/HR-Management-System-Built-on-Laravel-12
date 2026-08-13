<?php

namespace App\Services\Telegram;

use App\Contracts\Telegram\TelegramClientInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotClient implements TelegramClientInterface
{
    private string $botToken;
    private string $baseUrl;

    public function __construct(?string $botToken = null, ?string $baseUrl = null)
    {
        $this->botToken = $botToken ?? config('services.telegram.bot_token', 'mock_token');
        $this->baseUrl = $baseUrl ?? config('services.telegram.base_url', 'https://api.telegram.org');
    }

    public function sendMessage(string|int $chatId, string $text, ?string $parseMode = 'HTML', array $extra = []): bool
    {
        if (empty($this->botToken) || $this->botToken === 'mock_token') {
            Log::info("[Mock Telegram Send] Chat: {$chatId} | Message: {$text}");
            return true;
        }

        try {
            $url = "{$this->baseUrl}/bot{$this->botToken}/sendMessage";
            $response = Http::timeout(5)->post($url, array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode,
            ], $extra));

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("[Telegram API Error] " . $e->getMessage());
            return false;
        }
    }
}
