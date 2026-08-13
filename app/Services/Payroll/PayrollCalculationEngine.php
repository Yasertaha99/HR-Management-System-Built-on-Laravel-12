<?php

namespace App\Services\Payroll;

use App\DTOs\Payroll\PayrollCalculationResult;
use App\Models\EmployeeCompensation;
use App\Models\PayrollPeriod;
use App\Services\Attendance\AttendanceStatisticsService;
use App\Services\Payroll\DeductionPolicies\AbsenceDeductionPolicy;
use App\Services\Payroll\DeductionPolicies\LateDeductionPolicy;

class PayrollCalculationEngine
{
    public function __construct(
        private readonly AttendanceStatisticsService $statsService,
        private readonly CompensationResolver $compensationResolver,
        private readonly LateDeductionPolicy $lateDeductionPolicy,
        private readonly AbsenceDeductionPolicy $absenceDeductionPolicy
    ) {}

    /**
     * Calculate complete minor-unit payroll for a user in a given payroll period.
     * Consumes authoritative Attendance Engine results as single source of truth.
     */
    public function calculateUserPayroll(int $userId, PayrollPeriod $period): PayrollCalculationResult
    {
        $year = (int) $period->period_start->format('Y');
        $month = (int) $period->period_start->format('m');

        // SINGLE SOURCE OF TRUTH: Consume authoritative attendance engine metrics
        $attendanceStats = $this->statsService->calculateMonthlyStatistics($userId, $year, $month);

        // Resolve effective compensation for employee
        $compensation = $this->compensationResolver->resolveEffectiveCompensation($userId, $period->period_start);

        $baseSalaryMinor = $compensation?->base_salary_minor ?? 0;
        $overtimeMultiplier = $compensation?->overtime_multiplier ?? 1.50;
        $currency = $compensation?->currency ?? 'EGP';

        // Hourly rate minor calculation (standard 160 working hours / month)
        $hourlyRateMinor = $compensation && $compensation->hourly_rate_minor > 0
            ? $compensation->hourly_rate_minor
            : (int) round($baseSalaryMinor / 160);

        // 1. Overtime Compensation calculation
        $overtimeHours = $attendanceStats->totalOvertimeMinutes / 60;
        $overtimeAmountMinor = (int) round($overtimeHours * $hourlyRateMinor * $overtimeMultiplier);

        // 2. Deductions calculation via strategy policies
        $lateDeductionMinor = $this->lateDeductionPolicy->calculateDeduction($baseSalaryMinor, $attendanceStats->totalLateMinutes);
        $earlyLeaveDeductionMinor = 0; // Configurable extension point
        $absenceDeductionMinor = $this->absenceDeductionPolicy->calculateDeduction($baseSalaryMinor, $attendanceStats->absentCount);
        $unpaidLeaveDeductionMinor = 0;
        $allowancesMinor = 0;
        $bonusesMinor = 0;
        $otherDeductionsMinor = 0;

        // 3. Gross & Net Pay calculation
        $grossPayMinor = $baseSalaryMinor + $overtimeAmountMinor + $allowancesMinor + $bonusesMinor;
        $totalDeductionsMinor = $lateDeductionMinor + $earlyLeaveDeductionMinor + $absenceDeductionMinor + $unpaidLeaveDeductionMinor + $otherDeductionsMinor;
        $netPayMinor = max(0, $grossPayMinor - $totalDeductionsMinor);

        return new PayrollCalculationResult(
            baseSalaryMinor: $baseSalaryMinor,
            regularMinutes: $attendanceStats->totalActualMinutes,
            regularHours: $attendanceStats->totalRoundedHours,
            overtimeMinutes: $attendanceStats->totalOvertimeMinutes,
            overtimeAmountMinor: $overtimeAmountMinor,
            lateDeductionMinor: $lateDeductionMinor,
            earlyLeaveDeductionMinor: $earlyLeaveDeductionMinor,
            absenceDeductionMinor: $absenceDeductionMinor,
            unpaidLeaveDeductionMinor: $unpaidLeaveDeductionMinor,
            allowancesMinor: $allowancesMinor,
            bonusesMinor: $bonusesMinor,
            otherDeductionsMinor: $otherDeductionsMinor,
            grossPayMinor: $grossPayMinor,
            totalDeductionsMinor: $totalDeductionsMinor,
            netPayMinor: $netPayMinor,
            currency: $currency
        );
    }
}
