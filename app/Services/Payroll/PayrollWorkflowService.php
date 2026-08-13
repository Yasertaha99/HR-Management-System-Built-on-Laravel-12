<?php

namespace App\Services\Payroll;

use App\Enums\PayrollItemType;
use App\Enums\PayrollStatus;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollSnapshot;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class PayrollWorkflowService
{
    public function __construct(
        private readonly PayrollCalculationEngine $calculationEngine
    ) {}

    /**
     * Create a new payroll period in DRAFT status.
     */
    public function createPeriod(string $name, CarbonInterface $start, CarbonInterface $end): PayrollPeriod
    {
        return PayrollPeriod::create([
            'name' => $name,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'status' => PayrollStatus::DRAFT,
        ]);
    }

    /**
     * Run full calculation for all active employees in a payroll period.
     */
    public function calculatePeriodPayrolls(PayrollPeriod $period): void
    {
        if ($period->isLocked()) {
            throw new \DomainException("Cannot calculate payroll for a locked or paid period.");
        }

        DB::transaction(function () use ($period) {
            $period->update(['status' => PayrollStatus::CALCULATING]);

            $users = User::where('status', 'Active')->get();

            foreach ($users as $user) {
                $calc = $this->calculationEngine->calculateUserPayroll($user->id, $period);

                /** @var Payroll $payroll */
                $payroll = Payroll::updateOrCreate(
                    [
                        'payroll_period_id' => $period->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'status' => PayrollStatus::CALCULATED,
                        'gross_pay_minor' => $calc->grossPayMinor,
                        'total_deductions_minor' => $calc->totalDeductionsMinor,
                        'net_pay_minor' => $calc->netPayMinor,
                        'currency' => $calc->currency,
                    ]
                );

                // Clear previous items for clean recalculation
                $payroll->items()->delete();

                // Create normalized payroll line items
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'type' => PayrollItemType::BASE_SALARY,
                    'code' => 'BASE',
                    'description' => 'Base Monthly Salary',
                    'quantity' => 1.0,
                    'unit_amount_minor' => $calc->baseSalaryMinor,
                    'amount_minor' => $calc->baseSalaryMinor,
                ]);

                if ($calc->overtimeAmountMinor > 0) {
                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'type' => PayrollItemType::OVERTIME,
                        'code' => 'OT',
                        'description' => sprintf('Overtime Pay (%d mins)', $calc->overtimeMinutes),
                        'quantity' => round($calc->overtimeMinutes / 60, 2),
                        'unit_amount_minor' => 0,
                        'amount_minor' => $calc->overtimeAmountMinor,
                    ]);
                }

                if ($calc->lateDeductionMinor > 0) {
                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'type' => PayrollItemType::LATE_DEDUCTION,
                        'code' => 'LATE',
                        'description' => 'Late Arrival Deduction',
                        'quantity' => 1.0,
                        'unit_amount_minor' => $calc->lateDeductionMinor,
                        'amount_minor' => $calc->lateDeductionMinor,
                    ]);
                }

                if ($calc->absenceDeductionMinor > 0) {
                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'type' => PayrollItemType::ABSENCE_DEDUCTION,
                        'code' => 'ABSENT',
                        'description' => 'Absence Deduction',
                        'quantity' => 1.0,
                        'unit_amount_minor' => $calc->absenceDeductionMinor,
                        'amount_minor' => $calc->absenceDeductionMinor,
                    ]);
                }

                // Create/Update Immutable Historical Snapshot
                PayrollSnapshot::updateOrCreate(
                    ['payroll_id' => $payroll->id],
                    [
                        'snapshot_data' => [
                            'user' => [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'user_id' => $user->user_id,
                                'role_name' => $user->role_name,
                            ],
                            'calculation' => [
                                'base_salary_minor' => $calc->baseSalaryMinor,
                                'regular_hours' => $calc->regularHours,
                                'overtime_minutes' => $calc->overtimeMinutes,
                                'overtime_amount_minor' => $calc->overtimeAmountMinor,
                                'late_deduction_minor' => $calc->lateDeductionMinor,
                                'absence_deduction_minor' => $calc->absenceDeductionMinor,
                                'gross_pay_minor' => $calc->grossPayMinor,
                                'total_deductions_minor' => $calc->totalDeductionsMinor,
                                'net_pay_minor' => $calc->netPayMinor,
                                'currency' => $calc->currency,
                            ],
                            'period' => [
                                'id' => $period->id,
                                'name' => $period->name,
                                'start' => $period->period_start->toDateString(),
                                'end' => $period->period_end->toDateString(),
                            ],
                            'generated_at' => now()->toIso8601String(),
                        ]
                    ]
                );
            }

            $period->update(['status' => PayrollStatus::CALCULATED]);
        });
    }

    /**
     * Approve payroll period.
     */
    public function approvePeriod(PayrollPeriod $period): void
    {
        if ($period->isLocked()) {
            throw new \DomainException("Locked payroll periods cannot be modified.");
        }

        $period->update(['status' => PayrollStatus::APPROVED]);
        $period->payrolls()->update(['status' => PayrollStatus::APPROVED]);
    }

    /**
     * Lock payroll period permanently.
     */
    public function lockPeriod(PayrollPeriod $period, int $actorId): void
    {
        $period->update([
            'status' => PayrollStatus::LOCKED,
            'locked_at' => now(),
            'locked_by' => $actorId,
        ]);
        $period->payrolls()->update(['status' => PayrollStatus::LOCKED]);
    }

    /**
     * Mark payroll period as Paid.
     */
    public function markPaid(PayrollPeriod $period): void
    {
        $period->update(['status' => PayrollStatus::PAID]);
        $period->payrolls()->update(['status' => PayrollStatus::PAID]);
    }
}
