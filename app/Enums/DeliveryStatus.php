<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';

    public function isFinal(): bool
    {
        return in_array($this, [self::SENT, self::FAILED, self::SKIPPED]);
    }
}
