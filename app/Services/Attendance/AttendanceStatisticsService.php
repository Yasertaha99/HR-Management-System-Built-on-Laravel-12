<?php

namespace App\Services\Attendance;

use App\DTOs\Attendance\AttendanceStatisticsData;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceStatisticsService
{
    public function __construct(
        private readonly AttendanceQueryService $queryService
    ) {}

    /**
     * Compute statistics for a user for a specific month.
     */
    public function calculateMonthlyStatistics(int $userId, int $year, int $month): AttendanceStatisticsData
    {
        $calendarDays = $this->queryService->buildMonthlyCalendar($userId, $year, $month);

        $presentCount = 0;
        $absentCount = 0;
        $workingCount = 0;
        $completedCount = 0;
        $totalActualMinutes = 0;
        $totalRoundedHours = 0;
        $totalLateMinutes = 0;
        $totalOvertimeMinutes = 0;

        foreach ($calendarDays as $day) {
            $status = $day['status'];
            /** @var Attendance|null $attendance */
            $attendance = $day['attendance'];

            if ($status === AttendanceStatus::ABSENT) {
                $absentCount++;
            }

            if ($attendance) {
                if ($attendance->isWorking()) {
                    $workingCount++;
                    $presentCount++;
                }

                if ($attendance->isCompleted()) {
                    $completedCount++;
                    $presentCount++;
                    $totalActualMinutes += ($attendance->total_minutes ?? 0);
                    $totalRoundedHours += ($attendance->rounded_hours ?? 0);
                    $totalLateMinutes += ($attendance->late_minutes ?? 0);
                    $totalOvertimeMinutes += ($attendance->overtime_minutes ?? 0);
                }
            }
        }

        return new AttendanceStatisticsData(
            totalDays: count($calendarDays),
            presentCount: $presentCount,
            absentCount: $absentCount,
            workingCount: $workingCount,
            completedCount: $completedCount,
            totalActualMinutes: $totalActualMinutes,
            totalRoundedHours: $totalRoundedHours,
            totalLateMinutes: $totalLateMinutes,
            totalOvertimeMinutes: $totalOvertimeMinutes
        );
    }
}
