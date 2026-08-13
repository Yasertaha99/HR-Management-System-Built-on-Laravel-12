<?php

namespace App\DTOs\Payroll;

final class PayrollCalculationResult
{
    public function __construct(
        public readonly int $baseSalaryMinor,
        public readonly int $regularMinutes,
        public readonly int $regularHours,
        public readonly int $overtimeMinutes,
        public readonly int $overtimeAmountMinor,
        public readonly int $lateDeductionMinor,
        public readonly int $earlyLeaveDeductionMinor,
        public readonly int $absenceDeductionMinor,
        public readonly int $unpaidLeaveDeductionMinor,
        public readonly int $allowancesMinor,
        public readonly int $bonusesMinor,
        public readonly int $otherDeductionsMinor,
        public readonly int $grossPayMinor,
        public readonly int $totalDeductionsMinor,
        public readonly int $netPayMinor,
        public readonly string $currency = 'EGP'
    ) {}

    public function getFormattedGrossPay(): string
    {
        return sprintf('%.2f %s', $this->grossPayMinor / 100, $this->currency);
    }

    public function getFormattedTotalDeductions(): string
    {
        return sprintf('%.2f %s', $this->totalDeductionsMinor / 100, $this->currency);
    }

    public function getFormattedNetPay(): string
    {
        return sprintf('%.2f %s', $this->netPayMinor / 100, $this->currency);
    }
}
