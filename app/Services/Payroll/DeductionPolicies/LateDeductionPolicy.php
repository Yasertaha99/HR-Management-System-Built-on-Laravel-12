<?php

namespace App\Services\Payroll\DeductionPolicies;

use App\Contracts\Payroll\DeductionPolicyInterface;

class LateDeductionPolicy implements DeductionPolicyInterface
{
    /**
     * Calculate late arrival deduction in minor units based on total late minutes in month.
     * Standard rule: hourly rate = (base salary / 160 hours), minute rate = hourly rate / 60.
     */
    public function calculateDeduction(int $baseSalaryMinor, int $lateMinutes): int
    {
        if ($lateMinutes <= 0 || $baseSalaryMinor <= 0) {
            return 0;
        }

        // Minute rate in minor units = baseSalaryMinor / (160 * 60)
        $minuteRateMinor = $baseSalaryMinor / (160 * 60);

        return (int) round($lateMinutes * $minuteRateMinor);
    }
}
