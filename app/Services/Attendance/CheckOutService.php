<?php

namespace App\Services\Attendance;

use App\DTOs\Attendance\CheckOutData;
use App\Enums\AttendanceStatus;
use App\Exceptions\Attendance\AttendanceAlreadyCompletedException;
use App\Exceptions\Attendance\AttendanceNotStartedException;
use App\Exceptions\Attendance\InvalidAttendanceActionException;
use App\Models\Attendance;
use App\Models\AttendanceAuditLog;
use App\Models\WorkScheduleDay;
use Illuminate\Support\Facades\DB;

class CheckOutService
{
    public function __construct(
        private readonly AttendanceCalculationEngine $engine
    ) {}

    /**
     * Perform authoritative server-side checkout for an employee.
     * Guaranteed safe against double clicks and concurrent updates via DB transaction + lockForUpdate.
     *
     * @throws AttendanceNotStartedException
     * @throws AttendanceAlreadyCompletedException
     * @throws InvalidAttendanceActionException
     */
    public function checkOut(CheckOutData $data): Attendance
    {
        return DB::transaction(function () use ($data) {
            $today = $data->timestamp->toDateString();

            /** @var Attendance|null $attendance */
            $attendance = Attendance::where('user_id', $data->userId)
                ->where('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if (!$attendance || !$attendance->hasCheckedIn()) {
                throw new AttendanceNotStartedException("You have not checked in for today yet.");
            }

            if ($attendance->isCompleted() || $attendance->hasCheckedOut()) {
                throw new AttendanceAlreadyCompletedException("You have already finished your workday for today.");
            }

            if ($data->timestamp->lessThan($attendance->check_in)) {
                throw new InvalidAttendanceActionException("Checkout timestamp cannot be earlier than check-in timestamp.");
            }

            $user = $attendance->user;

            // Authoritative attendance calculation via AttendanceCalculationEngine
            $calcResult = $this->engine->calculate(
                userId: $data->userId,
                attendanceDate: $today,
                checkIn: $attendance->check_in,
                checkOut: $data->timestamp,
                status: AttendanceStatus::COMPLETED,
                workScheduleId: $user?->work_schedule_id
            );

            $oldValues = $attendance->toArray();

            // Update attendance record
            $attendance->update([
                'check_out' => $data->timestamp,
                'status' => AttendanceStatus::COMPLETED,
                'total_minutes' => $calcResult->totalMinutes,
                'rounded_hours' => $calcResult->roundedHours,
                'late_minutes' => $calcResult->lateMinutes,
                'early_leave_minutes' => $calcResult->earlyLeaveMinutes,
                'overtime_minutes' => $calcResult->overtimeMinutes,
                'notes' => $data->notes ?? $attendance->notes,
            ]);

            // Create audit log
            AttendanceAuditLog::create([
                'actor_id' => $data->userId,
                'action' => 'check_out',
                'entity_type' => Attendance::class,
                'entity_id' => $attendance->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'check_out' => $data->timestamp->toIso8601String(),
                    'status' => AttendanceStatus::COMPLETED->value,
                    'total_minutes' => $calcResult->totalMinutes,
                    'rounded_hours' => $calcResult->roundedHours,
                    'late_minutes' => $calcResult->lateMinutes,
                    'early_leave_minutes' => $calcResult->earlyLeaveMinutes,
                    'overtime_minutes' => $calcResult->overtimeMinutes,
                ],
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]);

            return $attendance->fresh();
        });
    }
}
