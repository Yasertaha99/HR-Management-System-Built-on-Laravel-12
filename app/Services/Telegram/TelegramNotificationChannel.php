<?php

namespace App\Services\Telegram;

use App\Contracts\Notifications\NotificationChannelInterface;
use App\Contracts\Telegram\TelegramClientInterface;
use App\DTOs\Notifications\NotificationData;
use App\Enums\DeliveryStatus;
use App\Models\NotificationPreference;
use App\Models\UserTelegramAccount;

class TelegramNotificationChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly TelegramClientInterface $telegramClient
    ) {}

    public function name(): string
    {
        return 'telegram';
    }

    public function supports(NotificationData $data): bool
    {
        // Check if recipient has a verified active Telegram account
        $telegramAccount = UserTelegramAccount::where('user_id', $data->recipientId)
            ->where('is_active', true)
            ->whereNotNull('verified_at')
            ->first();

        if (!$telegramAccount || !$telegramAccount->telegram_chat_id) {
            return false;
        }

        // Check notification preferences
        $pref = NotificationPreference::where('user_id', $data->recipientId)
            ->where('notification_type', $data->type)
            ->where('channel', 'telegram')
            ->first();

        return $pref ? $pref->enabled : true;
    }

    public function send(NotificationData $data): DeliveryStatus
    {
        $account = UserTelegramAccount::where('user_id', $data->recipientId)->first();
        if (!$account || !$account->telegram_chat_id) {
            return DeliveryStatus::SKIPPED;
        }

        $message = "<b>{$data->title}</b>\n\n{$data->body}";
        if ($data->actionUrl) {
            $message .= "\n\n<a href=\"{$data->actionUrl}\">View Details</a>";
        }

        $success = $this->telegramClient->sendMessage($account->telegram_chat_id, $message);

        return $success ? DeliveryStatus::SENT : DeliveryStatus::FAILED;
    }
}
