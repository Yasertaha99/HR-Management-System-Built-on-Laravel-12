<?php

namespace App\DTOs\Notifications;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;

final class NotificationData
{
    public function __construct(
        public readonly int $recipientId,
        public readonly NotificationType $type,
        public readonly string $title,
        public readonly string $body,
        public readonly NotificationPriority $priority = NotificationPriority::NORMAL,
        public readonly string $locale = 'en',
        public readonly ?string $actionUrl = null,
        public readonly array $metadata = [],
        public readonly array $channels = ['database', 'telegram']
    ) {}
}
