<?php

namespace Tests\Feature\Payroll;

use App\Enums\PayrollStatus;
use App\Models\EmployeeCompensation;
use App\Models\User;
use App\Services\Payroll\PayrollWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_calculate_and_lock_payroll_period(): void
    {
        $user = User::factory()->create(['role_name' => 'Employee']);

        EmployeeCompensation::create([
            'user_id' => $user->id,
            'effective_from' => '2026-01-01',
            'base_salary_minor' => 800000, // 8,000 EGP
            'overtime_multiplier' => 1.5,
            'currency' => 'EGP',
            'is_active' => true,
        ]);

        /** @var PayrollWorkflowService $workflowService */
        $workflowService = app(PayrollWorkflowService::class);

        $period = $workflowService->createPeriod(
            'August 2026 Payroll',
            Carbon::parse('2026-08-01'),
            Carbon::parse('2026-08-31')
        );

        $this->assertEquals(PayrollStatus::DRAFT, $period->status);

        $workflowService->calculatePeriodPayrolls($period);
        $period->refresh();

        $this->assertEquals(PayrollStatus::CALCULATED, $period->status);
        $this->assertDatabaseHas('payrolls', [
            'payroll_period_id' => $period->id,
            'user_id' => $user->id,
            'status' => PayrollStatus::CALCULATED->value,
            'gross_pay_minor' => 800000,
        ]);

        // Lock period
        $workflowService->lockPeriod($period, $user->id);
        $period->refresh();

        $this->assertEquals(PayrollStatus::LOCKED, $period->status);

        // Attempting to recalculate locked period must throw exception
        $this->expectException(\DomainException::class);
        $workflowService->calculatePeriodPayrolls($period);
    }
}
