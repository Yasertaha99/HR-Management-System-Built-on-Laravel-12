<?php

namespace App\Contracts\Payroll;

interface DeductionPolicyInterface
{
    /**
     * Calculate deduction amount in minor units.
     */
    public function calculateDeduction(int $baseSalaryMinor, int $metricValue): int;
}
