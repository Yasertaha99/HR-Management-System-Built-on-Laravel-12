<?php

namespace Tests\Feature\Notifications;

use App\DTOs\Notifications\NotificationData;
use App\Enums\DeliveryStatus;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\NotificationRecord;
use App\Models\User;
use App\Services\Notifications\Channels\DatabaseNotificationChannel;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_notification_channel_persists_notification_and_delivery(): void
    {
        $user = User::factory()->create();

        $dispatcher = new NotificationDispatcher();
        $dispatcher->registerChannel(new DatabaseNotificationChannel());

        $data = new NotificationData(
            recipientId: $user->id,
            type: NotificationType::ATTENDANCE_STARTED,
            title: 'Shift Started',
            body: 'You successfully checked in at 08:00 AM',
            priority: NotificationPriority::HIGH,
            channels: ['database']
        );

        $results = $dispatcher->dispatch($data);

        $this->assertEquals(DeliveryStatus::SENT, $results['database']);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => $user->id,
            'type' => NotificationType::ATTENDANCE_STARTED->value,
            'title' => 'Shift Started',
        ]);

        /** @var NotificationRecord $record */
        $record = NotificationRecord::where('recipient_id', $user->id)->first();
        $this->assertNotNull($record);

        $this->assertDatabaseHas('notification_deliveries', [
            'notification_id' => $record->id,
            'channel' => 'database',
            'status' => DeliveryStatus::SENT->value,
        ]);
    }
}
