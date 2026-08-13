<?php

namespace App\Services\Payroll;

use App\Models\EmployeeCompensation;
use Carbon\CarbonInterface;

class CompensationResolver
{
    /**
     * Resolve effective compensation record for an employee on a specific target date.
     */
    public function resolveEffectiveCompensation(int $userId, CarbonInterface $date): ?EmployeeCompensation
    {
        $dateString = $date->toDateString();

        return EmployeeCompensation::where('user_id', $userId)
            ->where('effective_from', '<=', $dateString)
            ->where(function ($query) use ($dateString) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $dateString);
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }
}
