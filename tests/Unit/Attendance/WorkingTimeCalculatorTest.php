<?php

namespace Tests\Unit\Attendance;

use App\Models\WorkScheduleDay;
use App\Services\Attendance\WorkingTimeCalculator;
use App\Strategies\Attendance\HalfHourRoundingStrategy;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class WorkingTimeCalculatorTest extends TestCase
{
    private WorkingTimeCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new WorkingTimeCalculator(new HalfHourRoundingStrategy());
    }

    public function test_calculate_total_minutes(): void
    {
        $checkIn = Carbon::parse('2026-08-12 08:00:00');
        $checkOut = Carbon::parse('2026-08-12 16:31:00');

        $totalMinutes = $this->calculator->calculateTotalMinutes($checkIn, $checkOut);
        $this->assertEquals(511, $totalMinutes);
    }

    public function test_calculate_rounded_hours_via_calculator(): void
    {
        $this->assertEquals(8, $this->calculator->calculateRoundedHours(509));
        $this->assertEquals(9, $this->calculator->calculateRoundedHours(511));
    }

    public function test_calculate_late_minutes(): void
    {
        $scheduleDay = new WorkScheduleDay([
            'is_working_day' => true,
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        $onTime = Carbon::parse('2026-08-12 07:55:00');
        $late = Carbon::parse('2026-08-12 08:31:00');

        $this->assertEquals(0, $this->calculator->calculateLateMinutes($onTime, $scheduleDay));
        $this->assertEquals(31, $this->calculator->calculateLateMinutes($late, $scheduleDay));
    }

    public function test_calculate_early_leave_minutes(): void
    {
        $scheduleDay = new WorkScheduleDay([
            'is_working_day' => true,
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        $early = Carbon::parse('2026-08-12 15:30:00');
        $normal = Carbon::parse('2026-08-12 16:00:00');

        $this->assertEquals(30, $this->calculator->calculateEarlyLeaveMinutes($early, $scheduleDay));
        $this->assertEquals(0, $this->calculator->calculateEarlyLeaveMinutes($normal, $scheduleDay));
    }
}
