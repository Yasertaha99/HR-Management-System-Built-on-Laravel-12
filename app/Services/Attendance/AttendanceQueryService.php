<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceQueryService
{
    /**
     * Get today's attendance for a given user.
     */
    public function getTodayAttendance(int $userId): ?Attendance
    {
        $today = now()->toDateString();

        return Attendance::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();
    }

    /**
     * Fetch all attendance records for a specific user and month in a single query.
     */
    public function getMonthlyAttendances(int $userId, int $year, int $month): Collection
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        return Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'asc')
            ->get();
    }

    /**
     * Build full calendar days data array for a month without N+1 queries.
     */
    public function buildMonthlyCalendar(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $daysInMonth = $startDate->daysInMonth;
        $today = now()->toDateString();

        // 1 single query for attendance records
        $attendances = $this->getMonthlyAttendances($userId, $year, $month)
            ->keyBy(fn ($item) => $item->attendance_date->toDateString());

        // Fetch holidays for the month if holidays table exists
        $holidays = Collection::make();
        try {
            $holidays = Holiday::whereYear('holiday_date', $year)
                ->whereMonth('holiday_date', $month)
                ->get()
                ->keyBy(fn ($h) => Carbon::parse($h->holiday_date)->toDateString());
        } catch (\Throwable $e) {
            // If holidays table format differs, fail gracefully
        }

        $calendarDays = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $dateString = $date->toDateString();
            $isFuture = $date->greaterThan(now()->endOfDay());
            $isToday = ($dateString === $today);
            $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 6=Saturday

            /** @var Attendance|null $attendance */
            $attendance = $attendances->get($dateString);
            $holiday = $holidays->get($dateString);

            $computedStatus = null;

            if ($attendance) {
                $computedStatus = $attendance->status;
            } elseif ($holiday) {
                $computedStatus = AttendanceStatus::HOLIDAY;
            } elseif ($dayOfWeek === 5 || $dayOfWeek === 6) { // Friday & Saturday default weekend
                $computedStatus = AttendanceStatus::DAY_OFF;
            } elseif (!$isFuture && !$isToday) {
                $computedStatus = AttendanceStatus::ABSENT;
            } else {
                $computedStatus = null; // Future or today not started
            }

            $calendarDays[] = [
                'day_number' => $day,
                'date' => $date,
                'date_string' => $dateString,
                'day_name' => $date->format('D'),
                'is_today' => $isToday,
                'is_future' => $isFuture,
                'is_weekend' => ($dayOfWeek === 5 || $dayOfWeek === 6),
                'attendance' => $attendance,
                'holiday' => $holiday,
                'status' => $computedStatus,
            ];
        }

        return $calendarDays;
    }
}
