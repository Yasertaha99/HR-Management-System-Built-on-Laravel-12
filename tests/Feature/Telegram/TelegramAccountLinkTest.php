<?php

namespace Tests\Feature\Telegram;

use App\Models\User;
use App\Models\UserTelegramAccount;
use App\Services\Telegram\TelegramAccountLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TelegramAccountLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_expiring_single_use_linking_token(): void
    {
        $user = User::factory()->create();
        $service = new TelegramAccountLinkService();

        $token = $service->generateLinkToken($user);

        $this->assertNotNull($token);
        $this->assertStringStartsWith('TG-', $token);

        $account = UserTelegramAccount::where('user_id', $user->id)->first();
        $this->assertEquals($token, $account->linking_token);
        $this->assertFalse($account->token_expires_at->isPast());
    }

    public function test_validate_and_link_telegram_chat_id(): void
    {
        $user = User::factory()->create();
        $service = new TelegramAccountLinkService();

        $token = $service->generateLinkToken($user);
        $account = $service->validateAndLinkToken($token, '123456789', '987654', 'john_doe');

        $this->assertTrue($account->isVerified());
        $this->assertEquals('123456789', $account->telegram_chat_id);
        $this->assertEquals('john_doe', $account->username);
        $this->assertNull($account->linking_token); // Token consumed
    }

    public function test_cannot_link_with_invalid_or_expired_token(): void
    {
        $service = new TelegramAccountLinkService();

        $this->expectException(InvalidArgumentException::class);
        $service->validateAndLinkToken('TG-INVALID-TOKEN', '123456789');
    }
}
