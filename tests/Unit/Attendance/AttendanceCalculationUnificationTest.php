<?php

namespace Tests\Unit\Attendance;

use App\DTOs\Attendance\AttendanceCalculationResult;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AttendanceCalculationEngine;
use App\Services\Attendance\WorkingTimeCalculator;
use App\Strategies\Attendance\HalfHourRoundingStrategy;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AttendanceCalculationUnificationTest extends TestCase
{
    private AttendanceCalculationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new AttendanceCalculationEngine(
            new WorkingTimeCalculator(new HalfHourRoundingStrategy())
        );
    }

    public function test_attendance_calculation_engine_returns_unified_result_dto(): void
    {
        $userId = 1;
        $date = '2026-08-12';
        $checkIn = Carbon::parse('2026-08-12 08:00:00');
        $checkOut = Carbon::parse('2026-08-12 16:31:00');

        $result = $this->engine->calculate(
            userId: $userId,
            attendanceDate: $date,
            checkIn: $checkIn,
            checkOut: $checkOut,
            status: AttendanceStatus::COMPLETED
        );

        $this->assertInstanceOf(AttendanceCalculationResult::class, $result);
        $this->assertEquals(511, $result->totalMinutes); // 8h 31m
        $this->assertEquals(9, $result->roundedHours);    // 31m >= 30m -> 9h
        $this->assertEquals('8h 31m', $result->getFormattedDuration());
    }
}
