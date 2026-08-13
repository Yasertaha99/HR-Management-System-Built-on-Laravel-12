<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
{
    /**
     * Determine whether the user can view the specific payroll / payslip.
     */
    public function view(User $user, Payroll $payroll): bool
    {
        // Employees can only view their own paid/approved payrolls
        if ($user->id === $payroll->user_id) {
            return true;
        }

        // Admin, HR, Manager can view payrolls
        return $user->isAdmin() || $user->isHr() || $user->isManager();
    }

    /**
     * Determine whether the user can manage payroll periods.
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin() || $user->isHr();
    }
}
