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
        private readonly AttendanceQueryService $queryService,
        private readonly AttendanceCalculationEngine $engine
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
                $calc = $this->engine->calculateForModel($attendance);

                if ($calc->status === AttendanceStatus::WORKING) {
                    $workingCount++;
                    $presentCount++;
                }

                if ($calc->status === AttendanceStatus::COMPLETED) {
                    $completedCount++;
                    $presentCount++;
                    $totalActualMinutes += $calc->totalMinutes;
                    $totalRoundedHours += $calc->roundedHours;
                    $totalLateMinutes += $calc->lateMinutes;
                    $totalOvertimeMinutes += $calc->overtimeMinutes;
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
