<?php

namespace App\Services\Notifications\Channels;

use App\Contracts\Notifications\NotificationChannelInterface;
use App\DTOs\Notifications\NotificationData;
use App\Enums\DeliveryStatus;
use App\Models\NotificationDelivery;
use App\Models\NotificationRecord;

class DatabaseNotificationChannel implements NotificationChannelInterface
{
    public function name(): string
    {
        return 'database';
    }

    public function supports(NotificationData $data): bool
    {
        return true;
    }

    public function send(NotificationData $data): DeliveryStatus
    {
        $record = NotificationRecord::create([
            'recipient_id' => $data->recipientId,
            'type' => $data->type,
            'priority' => $data->priority,
            'title' => $data->title,
            'body' => $data->body,
            'action_url' => $data->actionUrl,
            'data' => $data->metadata,
        ]);

        NotificationDelivery::create([
            'notification_id' => $record->id,
            'channel' => 'database',
            'status' => DeliveryStatus::SENT,
            'attempts' => 1,
            'sent_at' => now(),
        ]);

        return DeliveryStatus::SENT;
    }
}
