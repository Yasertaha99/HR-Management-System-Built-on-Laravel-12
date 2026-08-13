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
        private readonly WorkingTimeCalculator $calculator
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

            // Calculate actual working duration in minutes (never destroyed)
            $totalMinutes = $this->calculator->calculateTotalMinutes($attendance->check_in, $data->timestamp);

            // Calculate rounded working hours using the strategy pattern
            $roundedHours = $this->calculator->calculateRoundedHours($totalMinutes);

            // Find user work schedule day if assigned
            $user = $attendance->user;
            $dayOfWeek = $data->timestamp->dayOfWeek; // 0 = Sunday, 1 = Monday...
            $scheduleDay = null;

            if ($user && $user->work_schedule_id) {
                $scheduleDay = WorkScheduleDay::where('work_schedule_id', $user->work_schedule_id)
                    ->where('day_of_week', $dayOfWeek)
                    ->first();
            }

            $lateMinutes = $this->calculator->calculateLateMinutes($attendance->check_in, $scheduleDay);
            $earlyLeaveMinutes = $this->calculator->calculateEarlyLeaveMinutes($data->timestamp, $scheduleDay);
            $overtimeMinutes = $this->calculator->calculateOvertimeMinutes($totalMinutes, $scheduleDay);

            $oldValues = $attendance->toArray();

            // Update attendance record
            $attendance->update([
                'check_out' => $data->timestamp,
                'status' => AttendanceStatus::COMPLETED,
                'total_minutes' => $totalMinutes,
                'rounded_hours' => $roundedHours,
                'late_minutes' => $lateMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'overtime_minutes' => $overtimeMinutes,
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
                    'total_minutes' => $totalMinutes,
                    'rounded_hours' => $roundedHours,
                    'late_minutes' => $lateMinutes,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                ],
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]);

            return $attendance->fresh();
        });
    }
}
