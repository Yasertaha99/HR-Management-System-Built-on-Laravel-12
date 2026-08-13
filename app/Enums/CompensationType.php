<?php

namespace App\Enums;

enum CompensationType: string
{
    case MONTHLY = 'monthly';
    case HOURLY = 'hourly';
    case DAILY = 'daily';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly Fixed Salary',
            self::HOURLY => 'Hourly Wage',
            self::DAILY => 'Daily Wage Rate',
        };
    }
}
