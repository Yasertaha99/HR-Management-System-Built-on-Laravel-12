<?php

namespace App\Strategies\Attendance;

use App\Contracts\Attendance\WorkingHourRoundingStrategyInterface;

/**
 * 15-minute rounding strategy:
 * Rounds to nearest quarter hour, converted to integer hours (or integer equivalent).
 */
class QuarterHourRoundingStrategy implements WorkingHourRoundingStrategyInterface
{
    public function round(int $totalMinutes): int
    {
        if ($totalMinutes <= 0) {
            return 0;
        }

        // Round total minutes to nearest 15 minutes, then calculate hours
        $roundedMinutes = (int) (round($totalMinutes / 15) * 15);
        return (int) round($roundedMinutes / 60);
    }
}
