<?php

namespace App\Services\Telegram;

use App\Models\User;
use App\Models\UserTelegramAccount;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TelegramAccountLinkService
{
    /**
     * Generate an expiring, single-use linking token for an employee.
     */
    public function generateLinkToken(User $user): string
    {
        $token = 'TG-' . Str::upper(Str::random(12));

        UserTelegramAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'linking_token' => $token,
                'token_expires_at' => now()->addMinutes(15),
            ]
        );

        return $token;
    }

    /**
     * Validate linking token and bind Telegram chat ID securely.
     */
    public function validateAndLinkToken(string $token, string $telegramChatId, ?string $telegramUserId = null, ?string $username = null): UserTelegramAccount
    {
        $account = UserTelegramAccount::where('linking_token', $token)->first();

        if (!$account) {
            throw new InvalidArgumentException("Invalid Telegram linking token.");
        }

        if ($account->token_expires_at && $account->token_expires_at->isPast()) {
            throw new InvalidArgumentException("Telegram linking token has expired.");
        }

        // Ensure Telegram account isn't already bound to another active user
        $existing = UserTelegramAccount::where('telegram_chat_id', $telegramChatId)
            ->where('user_id', '!=', $account->user_id)
            ->where('is_active', true)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException("This Telegram account is already linked to another employee.");
        }

        $account->update([
            'telegram_chat_id' => $telegramChatId,
            'telegram_user_id' => $telegramUserId,
            'username' => $username,
            'verified_at' => now(),
            'linked_at' => now(),
            'unlinked_at' => null,
            'is_active' => true,
            'linking_token' => null, // Invalidate token after single use
            'token_expires_at' => null,
        ]);

        return $account->fresh();
    }

    /**
     * Unlink Telegram account for an employee.
     */
    public function unlinkAccount(User $user): void
    {
        $account = UserTelegramAccount::where('user_id', $user->id)->first();
        if ($account) {
            $account->update([
                'is_active' => false,
                'unlinked_at' => now(),
            ]);
        }
    }
}
