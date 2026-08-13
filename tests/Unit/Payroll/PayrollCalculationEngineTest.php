<?php

namespace Tests\Unit\Payroll;

use App\DTOs\Attendance\AttendanceStatisticsData;
use App\Models\EmployeeCompensation;
use App\Models\PayrollPeriod;
use App\Services\Attendance\AttendanceStatisticsService;
use App\Services\Payroll\CompensationResolver;
use App\Services\Payroll\DeductionPolicies\AbsenceDeductionPolicy;
use App\Services\Payroll\DeductionPolicies\LateDeductionPolicy;
use App\Services\Payroll\PayrollCalculationEngine;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PayrollCalculationEngineTest extends TestCase
{
    public function test_payroll_calculation_with_overtime_and_late_deduction(): void
    {
        // Mock attendance stats: 160h regular, 120m (2h) overtime, 60m late, 0 absent
        $attendanceStats = new AttendanceStatisticsData(
            totalDays: 20,
            presentCount: 20,
            absentCount: 0,
            workingCount: 0,
            completedCount: 20,
            totalActualMinutes: 9720,
            totalRoundedHours: 162,
            totalLateMinutes: 60,
            totalOvertimeMinutes: 120
        );

        $statsService = $this->createMock(AttendanceStatisticsService::class);
        $statsService->method('calculateMonthlyStatistics')->willReturn($attendanceStats);

        $compensation = new EmployeeCompensation([
            'base_salary_minor' => 1000000, // 10,000.00 EGP
            'overtime_multiplier' => 1.50,
            'currency' => 'EGP',
        ]);

        $resolver = $this->createMock(CompensationResolver::class);
        $resolver->method('resolveEffectiveCompensation')->willReturn($compensation);

        $engine = new PayrollCalculationEngine(
            $statsService,
            $resolver,
            new LateDeductionPolicy(),
            new AbsenceDeductionPolicy()
        );

        $period = new PayrollPeriod([
            'name' => 'August 2026',
            'period_start' => Carbon::parse('2026-08-01'),
            'period_end' => Carbon::parse('2026-08-31'),
        ]);

        $result = $engine->calculateUserPayroll(1, $period);

        // Base Salary: 10,000.00 EGP (1,000,000 minor)
        // Hourly rate: 1,000,000 / 160 = 6,250 minor (62.50 EGP/h)
        // Overtime (2h @ 1.5x): 2 * 6,250 * 1.5 = 18,750 minor (187.50 EGP)
        // Late deduction (60 mins = 1 hour): 6,250 minor (62.50 EGP)
        // Net pay: 1,000,000 + 18,750 - 6,250 = 1,012,500 minor (10,125.00 EGP)

        $this->assertEquals(1000000, $result->baseSalaryMinor);
        $this->assertEquals(18750, $result->overtimeAmountMinor);
        $this->assertEquals(6250, $result->lateDeductionMinor);
        $this->assertEquals(1018750, $result->grossPayMinor);
        $this->assertEquals(1012500, $result->netPayMinor);
        $this->assertEquals('10125.00 EGP', $result->getFormattedNetPay());
    }
}
