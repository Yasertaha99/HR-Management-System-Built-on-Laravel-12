<?php

namespace App\DTOs\Attendance;

use App\Enums\AttendanceStatus;
use App\ValueObjects\WorkingDuration;
use Carbon\CarbonInterface;

final class AttendanceCalculationResult
{
    public function __construct(
        public readonly int $userId,
        public readonly string $attendanceDate,
        public readonly CarbonInterface $checkIn,
        public readonly ?CarbonInterface $checkOut,
        public readonly AttendanceStatus $status,
        public readonly int $totalMinutes,
        public readonly int $roundedHours,
        public readonly int $scheduledMinutes,
        public readonly int $lateMinutes,
        public readonly int $earlyLeaveMinutes,
        public readonly int $overtimeMinutes
    ) {}

    public function getFormattedDuration(): string
    {
        if ($this->totalMinutes <= 0) {
            if ($this->status === AttendanceStatus::WORKING) {
                $elapsedMinutes = (int) max(0, $this->checkIn->diffInMinutes(now()));
                return WorkingDuration::fromMinutes($elapsedMinutes)->toShortString();
            }
            return '—';
        }

        return WorkingDuration::fromMinutes($this->totalMinutes)->toShortString();
    }
}
