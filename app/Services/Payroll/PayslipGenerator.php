<?php

namespace App\Services\Payroll;

use App\Models\Payroll;

class PayslipGenerator
{
    /**
     * Generate HTML view payload for a payslip using immutable snapshot data.
     */
    public function generatePayslipPayload(Payroll $payroll): array
    {
        $snapshot = $payroll->snapshot?->snapshot_data ?? [];

        return [
            'payroll_id' => $payroll->id,
            'employee_name' => $snapshot['user']['name'] ?? $payroll->user->name,
            'employee_id' => $snapshot['user']['user_id'] ?? $payroll->user->user_id,
            'email' => $snapshot['user']['email'] ?? $payroll->user->email,
            'period_name' => $snapshot['period']['name'] ?? $payroll->period->name,
            'period_start' => $snapshot['period']['start'] ?? $payroll->period->period_start->toDateString(),
            'period_end' => $snapshot['period']['end'] ?? $payroll->period->period_end->toDateString(),
            'status' => $payroll->status->label(),
            'status_badge' => $payroll->status->badgeClass(),
            'gross_pay' => $payroll->formatted_gross_pay,
            'total_deductions' => $payroll->formatted_total_deductions,
            'net_pay' => $payroll->formatted_net_pay,
            'items' => $payroll->items,
            'currency' => $payroll->currency,
            'generated_at' => $snapshot['generated_at'] ?? now()->toIso8601String(),
        ];
    }
}
