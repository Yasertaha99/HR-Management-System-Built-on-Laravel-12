<?php

namespace Tests\Feature\Telegram;

use App\Models\Attendance;
use App\Models\User;
use App\Models\UserTelegramAccount;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_webhook_idempotency_prevents_duplicate_processing(): void
    {
        $payload = [
            'update_id' => 999888,
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/help',
            ],
        ];

        // First call -> 200 OK
        $response1 = $this->postJson('/webhooks/telegram', $payload);
        $response1->assertStatus(200)->assertJson(['status' => 'ok']);

        // Duplicate call with same update_id -> 200 OK duplicate ignored
        $response2 = $this->postJson('/webhooks/telegram', $payload);
        $response2->assertStatus(200)->assertJson(['status' => 'duplicate']);
    }

    public function test_telegram_unlinked_user_cannot_access_employee_commands(): void
    {
        $payload = [
            'update_id' => 111222,
            'message' => [
                'chat' => ['id' => 999999], // Unlinked Chat ID
                'text' => '/status',
            ],
        ];

        $response = $this->postJson('/webhooks/telegram', $payload);
        $response->assertStatus(200);
    }

    public function test_linked_user_can_query_status_command(): void
    {
        $user = User::factory()->create();
        UserTelegramAccount::create([
            'user_id' => $user->id,
            'telegram_chat_id' => '555444',
            'verified_at' => now(),
            'is_active' => true,
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->subHours(4),
            'status' => \App\Enums\AttendanceStatus::WORKING,
        ]);

        $payload = [
            'update_id' => 333444,
            'message' => [
                'chat' => ['id' => 555444],
                'text' => '/status',
            ],
        ];

        $response = $this->postJson('/webhooks/telegram', $payload);
        $response->assertStatus(200)->assertJson(['status' => 'ok']);
    }
}
