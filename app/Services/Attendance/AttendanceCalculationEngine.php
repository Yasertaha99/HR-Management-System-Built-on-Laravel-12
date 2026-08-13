<?php

namespace App\Services\Attendance;

use App\DTOs\Attendance\AttendanceCalculationResult;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\WorkScheduleDay;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AttendanceCalculationEngine
{
    public function __construct(
        private readonly WorkingTimeCalculator $calculator
    ) {}

    /**
     * Calculate authoritative result for an attendance record or check-in/out timestamps.
     */
    public function calculate(
        int $userId,
        string $attendanceDate,
        CarbonInterface $checkIn,
        ?CarbonInterface $checkOut = null,
        ?AttendanceStatus $status = null,
        ?int $workScheduleId = null
    ): AttendanceCalculationResult {
        $checkOut = $checkOut ?? now();
        $status = $status ?? ($checkOut ? AttendanceStatus::COMPLETED : AttendanceStatus::WORKING);

        // 1. Total actual working minutes
        $totalMinutes = $this->calculator->calculateTotalMinutes($checkIn, $checkOut);

        // 2. Rounded working hours via strategy pattern
        $roundedHours = $this->calculator->calculateRoundedHours($totalMinutes);

        // 3. Resolve work schedule day if assigned
        $dayOfWeek = Carbon::parse($attendanceDate)->dayOfWeek;
        $scheduleDay = null;

        if ($workScheduleId) {
            $scheduleDay = WorkScheduleDay::where('work_schedule_id', $workScheduleId)
                ->where('day_of_week', $dayOfWeek)
                ->first();
        }

        $scheduledMinutes = $scheduleDay && $scheduleDay->is_working_day ? ($scheduleDay->expected_minutes ?? 480) : 0;
        $lateMinutes = $this->calculator->calculateLateMinutes($checkIn, $scheduleDay);
        $earlyLeaveMinutes = $this->calculator->calculateEarlyLeaveMinutes($checkOut, $scheduleDay);
        $overtimeMinutes = $this->calculator->calculateOvertimeMinutes($totalMinutes, $scheduleDay);

        return new AttendanceCalculationResult(
            userId: $userId,
            attendanceDate: $attendanceDate,
            checkIn: $checkIn,
            checkOut: $checkOut,
            status: $status,
            totalMinutes: $totalMinutes,
            roundedHours: $roundedHours,
            scheduledMinutes: $scheduledMinutes,
            lateMinutes: $lateMinutes,
            earlyLeaveMinutes: $earlyLeaveMinutes,
            overtimeMinutes: $overtimeMinutes
        );
    }

    /**
     * Calculate authoritative result directly from an existing Attendance eloquent model.
     */
    public function calculateForModel(Attendance $attendance): AttendanceCalculationResult
    {
        $user = $attendance->user;

        return $this->calculate(
            userId: $attendance->user_id,
            attendanceDate: $attendance->attendance_date->toDateString(),
            checkIn: $attendance->check_in,
            checkOut: $attendance->check_out,
            status: $attendance->status,
            workScheduleId: $user?->work_schedule_id
        );
    }
}
