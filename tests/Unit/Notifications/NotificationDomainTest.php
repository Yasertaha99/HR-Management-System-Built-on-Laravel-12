<?php

namespace Tests\Unit\Notifications;

use App\Contracts\Notifications\NotificationChannelInterface;
use App\DTOs\Notifications\NotificationData;
use App\Enums\DeliveryStatus;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Services\Notifications\NotificationDispatcher;
use PHPUnit\Framework\TestCase;

class NotificationDomainTest extends TestCase
{
    public function test_notification_dispatcher_dispatches_to_registered_channels(): void
    {
        $dispatcher = new NotificationDispatcher();

        $mockChannel = new class implements NotificationChannelInterface {
            public function name(): string { return 'test_channel'; }
            public function supports(NotificationData $data): bool { return true; }
            public function send(NotificationData $data): DeliveryStatus { return DeliveryStatus::SENT; }
        };

        $dispatcher->registerChannel($mockChannel);

        $data = new NotificationData(
            recipientId: 1,
            type: NotificationType::ATTENDANCE_STARTED,
            title: 'Workday Started',
            body: 'You checked in at 08:00 AM',
            channels: ['test_channel']
        );

        $results = $dispatcher->dispatch($data);

        $this->assertArrayHasKey('test_channel', $results);
        $this->assertEquals(DeliveryStatus::SENT, $results['test_channel']);
    }
}
