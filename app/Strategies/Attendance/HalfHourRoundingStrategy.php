<?php

namespace App\Strategies\Attendance;

use App\Contracts\Attendance\WorkingHourRoundingStrategyInterface;

/**
 * Standard company half-hour rounding strategy:
 * 0–29 additional minutes -> do not add an hour
 * 30–59 additional minutes -> add one hour
 *
 * Algorithm:
 * base_hours = floor(total_minutes / 60)
 * remaining_minutes = total_minutes % 60
 * if remaining_minutes >= 30 -> rounded_hours = base_hours + 1
 * else -> rounded_hours = base_hours
 */
class HalfHourRoundingStrategy implements WorkingHourRoundingStrategyInterface
{
    public function round(int $totalMinutes): int
    {
        if ($totalMinutes <= 0) {
            return 0;
        }

        $baseHours = (int) floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;

        if ($remainingMinutes >= 30) {
            return $baseHours + 1;
        }

        return $baseHours;
    }
}
