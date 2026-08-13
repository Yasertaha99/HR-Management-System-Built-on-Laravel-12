<?php

namespace App\Strategies\Attendance;

use App\Contracts\Attendance\WorkingHourRoundingStrategyInterface;

/**
 * No rounding strategy: returns the raw integer floor of hours worked.
 */
class NoRoundingStrategy implements WorkingHourRoundingStrategyInterface
{
    public function round(int $totalMinutes): int
    {
        if ($totalMinutes <= 0) {
            return 0;
        }

        return (int) floor($totalMinutes / 60);
    }
}
