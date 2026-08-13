<?php

namespace App\Services\Attendance;

use App\DTOs\Attendance\CheckInData;
use App\Enums\AttendanceStatus;
use App\Exceptions\Attendance\AttendanceAlreadyCompletedException;
use App\Exceptions\Attendance\AttendanceAlreadyStartedException;
use App\Models\Attendance;
use App\Models\AttendanceAuditLog;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    /**
     * Perform authoritative server-side check-in for an employee.
     * Guaranteed safe against concurrent duplicate check-ins via DB transaction + lockForUpdate + unique key constraint.
     *
     * @throws AttendanceAlreadyStartedException
     * @throws AttendanceAlreadyCompletedException
     */
    public function checkIn(CheckInData $data): Attendance
    {
        return DB::transaction(function () use ($data) {
            $today = $data->timestamp->toDateString();

            // Lock existing record if any to prevent race conditions
            $existing = Attendance::where('user_id', $data->userId)
                ->where('attendance_date', $today)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->isWorking()) {
                    throw new AttendanceAlreadyStartedException("You have already checked in for today at " . $existing->check_in->format('h:i A') . ".");
                }

                if ($existing->isCompleted()) {
                    throw new AttendanceAlreadyCompletedException("You have already completed your workday for today.");
                }
            }

            // Create new working attendance record with authoritative server timestamp
            $attendance = Attendance::create([
                'user_id' => $data->userId,
                'attendance_date' => $today,
                'check_in' => $data->timestamp,
                'check_out' => null,
                'status' => AttendanceStatus::WORKING,
                'total_minutes' => null,
                'rounded_hours' => null,
                'notes' => $data->notes,
            ]);

            // Create audit log
            AttendanceAuditLog::create([
                'actor_id' => $data->userId,
                'action' => 'check_in',
                'entity_type' => Attendance::class,
                'entity_id' => $attendance->id,
                'old_values' => null,
                'new_values' => [
                    'check_in' => $data->timestamp->toIso8601String(),
                    'status' => AttendanceStatus::WORKING->value,
                ],
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]);

            return $attendance;
        });
    }
}
