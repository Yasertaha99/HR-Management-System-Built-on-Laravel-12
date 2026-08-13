<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramCommandProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TelegramWebhookController
{
    public function __construct(
        private readonly TelegramCommandProcessor $processor
    ) {}

    public function handle(Request $request): JsonResponse
    {
        // 1. Secret Token Security Validation
        $expectedSecret = config('services.telegram.webhook_secret', 'secret_webhook_token');
        $headerSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');

        if (!empty($expectedSecret) && $expectedSecret !== 'secret_webhook_token' && $headerSecret !== $expectedSecret) {
            return response()->json(['error' => 'Unauthorized webhook secret'], 401);
        }

        $update = $request->all();
        $updateId = $update['update_id'] ?? null;

        if (!$updateId) {
            return response()->json(['status' => 'ignored', 'reason' => 'missing_update_id']);
        }

        // 2. Idempotency Check (Prevent duplicate update execution)
        $cacheKey = "telegram_update_{$updateId}";
        if (Cache::has($cacheKey)) {
            return response()->json(['status' => 'duplicate', 'message' => 'Update already processed']);
        }
        Cache::put($cacheKey, true, now()->addHours(24));

        // 3. Extract Message & Delegate to Processor
        $message = $update['message'] ?? null;
        if ($message && isset($message['chat']['id']) && isset($message['text'])) {
            $chatId = (string) $message['chat']['id'];
            $text = (string) $message['text'];
            $username = $message['from']['username'] ?? null;

            $this->processor->process($chatId, $text, $username);
        }

        return response()->json(['status' => 'ok']);
    }
}
