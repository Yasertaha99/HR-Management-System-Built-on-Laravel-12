<?php

namespace App\Contracts\Notifications;

use App\DTOs\Notifications\NotificationData;
use App\Enums\DeliveryStatus;

interface NotificationChannelInterface
{
    public function name(): string;

    public function supports(NotificationData $data): bool;

    public function send(NotificationData $data): DeliveryStatus;
}
