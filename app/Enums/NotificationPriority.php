<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function badgeClass(): string
    {
        return match ($this) {
            self::LOW => 'badge-secondary',
            self::NORMAL => 'badge-info',
            self::HIGH => 'badge-warning',
            self::CRITICAL => 'badge-danger',
        };
    }
}
