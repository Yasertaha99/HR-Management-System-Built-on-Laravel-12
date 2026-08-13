<?php

namespace App\Enums;

enum PayrollItemType: string
{
    case BASE_SALARY = 'base_salary';
    case OVERTIME = 'overtime';
    case BONUS = 'bonus';
    case ALLOWANCE = 'allowance';
    case LATE_DEDUCTION = 'late_deduction';
    case EARLY_LEAVE_DEDUCTION = 'early_leave_deduction';
    case ABSENCE_DEDUCTION = 'absence_deduction';
    case UNPAID_LEAVE = 'unpaid_leave';
    case OTHER_DEDUCTION = 'other_deduction';

    public function isEarning(): bool
    {
        return in_array($this, [
            self::BASE_SALARY,
            self::OVERTIME,
            self::BONUS,
            self::ALLOWANCE,
        ]);
    }

    public function isDeduction(): bool
    {
        return !$this->isEarning();
    }

    public function label(): string
    {
        return match ($this) {
            self::BASE_SALARY => 'Base Salary',
            self::OVERTIME => 'Overtime Pay',
            self::BONUS => 'Performance Bonus',
            self::ALLOWANCE => 'Allowance',
            self::LATE_DEDUCTION => 'Late Arrival Deduction',
            self::EARLY_LEAVE_DEDUCTION => 'Early Leave Deduction',
            self::ABSENCE_DEDUCTION => 'Absence Deduction',
            self::UNPAID_LEAVE => 'Unpaid Leave Deduction',
            self::OTHER_DEDUCTION => 'Other Deduction',
        };
    }
}
