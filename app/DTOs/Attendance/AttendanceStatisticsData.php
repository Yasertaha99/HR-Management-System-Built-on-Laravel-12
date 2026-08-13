<?php

namespace App\DTOs\Attendance;

final class AttendanceStatisticsData
{
    public function __construct(
        public readonly int $totalDays,
        public readonly int $presentCount,
        public readonly int $absentCount,
        public readonly int $workingCount,
        public readonly int $completedCount,
        public readonly int $totalActualMinutes,
        public readonly int $totalRoundedHours,
        public readonly int $totalLateMinutes,
        public readonly int $totalOvertimeMinutes
    ) {}

    public function getFormattedActualTime(): string
    {
        $hours = (int) floor($this->totalActualMinutes / 60);
        $mins = $this->totalActualMinutes % 60;

        return sprintf('%dh %02dm', $hours, $mins);
    }
}
