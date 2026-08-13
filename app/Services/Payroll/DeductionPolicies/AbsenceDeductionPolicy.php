<?php

namespace App\Services\Payroll\DeductionPolicies;

use App\Contracts\Payroll\DeductionPolicyInterface;

class AbsenceDeductionPolicy implements DeductionPolicyInterface
{
    /**
     * Calculate absence deduction in minor units based on count of unexcused absent days.
     * Standard rule: daily rate = base salary / 22 working days.
     */
    public function calculateDeduction(int $baseSalaryMinor, int $absentDays): int
    {
        if ($absentDays <= 0 || $baseSalaryMinor <= 0) {
            return 0;
        }

        $dailyRateMinor = $baseSalaryMinor / 22;

        return (int) round($absentDays * $dailyRateMinor);
    }
}
