<?php

namespace App\Enums;

enum RoundingMethod: string
{
    case HALF_HOUR = 'half_hour';
    case QUARTER_HOUR = 'quarter_hour';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::HALF_HOUR => '30-Minute Rounding (Default)',
            self::QUARTER_HOUR => '15-Minute Rounding',
            self::NONE => 'No Rounding (Exact Floor)',
        };
    }
}
