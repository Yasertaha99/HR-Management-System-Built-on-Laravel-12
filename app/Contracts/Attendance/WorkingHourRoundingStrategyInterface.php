<?php

namespace App\Contracts\Attendance;

interface WorkingHourRoundingStrategyInterface
{
    /**
     * Round total working minutes into rounded working hours according to policy.
     *
     * @param int $totalMinutes Total actual working duration in minutes
     * @return int Calculated/Rounded working hours
     */
    public function round(int $totalMinutes): int;
}
