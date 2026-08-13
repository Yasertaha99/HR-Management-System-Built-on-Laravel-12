<?php

namespace App\Livewire\Settings;

use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\UserTelegramAccount;
use App\Services\Telegram\TelegramAccountLinkService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationPreferencesPage extends Component
{
    public array $preferences = [];
    public ?string $telegramLinkToken = null;
    public ?string $successMessage = null;

    public function mount(): void
    {
        $userId = Auth::id();

        foreach (NotificationType::cases() as $type) {
            $this->preferences[$type->value] = [
                'database' => $this->isChannelEnabled($userId, $type, 'database'),
                'telegram' => $this->isChannelEnabled($userId, $type, 'telegram'),
            ];
        }
    }

    public function togglePreference(string $typeValue, string $channel): void
    {
        $userId = Auth::id();
        $type = NotificationType::from($typeValue);
        $current = $this->preferences[$typeValue][$channel];
        $newStatus = !$current;

        $this->preferences[$typeValue][$channel] = $newStatus;

        NotificationPreference::updateOrCreate(
            [
                'user_id' => $userId,
                'notification_type' => $type,
                'channel' => $channel,
            ],
            [
                'enabled' => $newStatus,
            ]
        );

        $this->successMessage = "Notification preference updated.";
    }

    public function generateTelegramToken(TelegramAccountLinkService $linkService): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->telegramLinkToken = $linkService->generateLinkToken($user);
        $this->successMessage = "Single-use Telegram link token generated. Send '/start {$this->telegramLinkToken}' to the bot within 15 minutes.";
    }

    public function unlinkTelegram(TelegramAccountLinkService $linkService): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $linkService->unlinkAccount($user);
        $this->telegramLinkToken = null;
        $this->successMessage = "Telegram account unlinked successfully.";
    }

    private function isChannelEnabled(int $userId, NotificationType $type, string $channel): bool
    {
        $pref = NotificationPreference::where('user_id', $userId)
            ->where('notification_type', $type)
            ->where('channel', $channel)
            ->first();

        return $pref ? $pref->enabled : true; // Default enabled
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $telegramAccount = UserTelegramAccount::where('user_id', $user->id)->first();

        return view('livewire.settings.notification-preferences-page', [
            'notificationTypes' => NotificationType::cases(),
            'telegramAccount' => $telegramAccount,
        ])->layout('layouts.master');
    }
}
