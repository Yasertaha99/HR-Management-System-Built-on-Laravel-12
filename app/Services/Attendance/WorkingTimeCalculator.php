<?php

namespace App\Services\Attendance;

use App\Contracts\Attendance\WorkingHourRoundingStrategyInterface;
use App\Models\WorkScheduleDay;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class WorkingTimeCalculator
{
    public function __construct(
        private readonly WorkingHourRoundingStrategyInterface $roundingStrategy
    ) {}

    /**
     * Calculate actual duration between check_in and check_out in minutes.
     */
    public function calculateTotalMinutes(CarbonInterface $checkIn, CarbonInterface $checkOut): int
    {
        if ($checkOut->lessThan($checkIn)) {
            return 0;
        }

        return (int) max(0, $checkIn->diffInMinutes($checkOut));
    }

    /**
     * Calculate rounded hours based on total actual minutes using the injected strategy.
     */
    public function calculateRoundedHours(int $totalMinutes): int
    {
        return $this->roundingStrategy->round($totalMinutes);
    }

    /**
     * Calculate lateness in minutes compared to scheduled start time.
     */
    public function calculateLateMinutes(CarbonInterface $checkIn, ?WorkScheduleDay $scheduleDay): int
    {
        if (!$scheduleDay || !$scheduleDay->is_working_day || !$scheduleDay->start_time) {
            return 0;
        }

        $scheduledStart = Carbon::parse($checkIn->toDateString() . ' ' . $scheduleDay->start_time, $checkIn->getTimezone());

        if ($checkIn->greaterThan($scheduledStart)) {
            return (int) $scheduledStart->diffInMinutes($checkIn);
        }

        return 0;
    }

    /**
     * Calculate early leave in minutes compared to scheduled end time.
     */
    public function calculateEarlyLeaveMinutes(CarbonInterface $checkOut, ?WorkScheduleDay $scheduleDay): int
    {
        if (!$scheduleDay || !$scheduleDay->is_working_day || !$scheduleDay->end_time) {
            return 0;
        }

        $scheduledEnd = Carbon::parse($checkOut->toDateString() . ' ' . $scheduleDay->end_time, $checkOut->getTimezone());

        if ($checkOut->lessThan($scheduledEnd)) {
            return (int) $checkOut->diffInMinutes($scheduledEnd);
        }

        return 0;
    }

    /**
     * Calculate overtime minutes beyond scheduled expected minutes.
     */
    public function calculateOvertimeMinutes(int $actualMinutes, ?WorkScheduleDay $scheduleDay): int
    {
        if (!$scheduleDay || !$scheduleDay->is_working_day) {
            return $actualMinutes; // All work on non-working day is overtime
        }

        $expectedMinutes = $scheduleDay->expected_minutes ?? 480;

        if ($actualMinutes > $expectedMinutes) {
            return $actualMinutes - $expectedMinutes;
        }

        return 0;
    }
}
