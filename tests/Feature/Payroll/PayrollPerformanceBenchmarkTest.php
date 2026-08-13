<?php

namespace Tests\Feature\Payroll;

use App\Models\EmployeeCompensation;
use App\Models\User;
use App\Services\Payroll\PayrollWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollPerformanceBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_benchmark_payroll_calculation_performance_for_scale(): void
    {
        // Generate 100 active synthetic employee records
        $users = User::factory()->count(100)->create(['status' => 'Active']);

        foreach ($users as $user) {
            EmployeeCompensation::create([
                'user_id' => $user->id,
                'effective_from' => '2026-01-01',
                'base_salary_minor' => rand(500000, 2000000),
                'overtime_multiplier' => 1.5,
                'currency' => 'EGP',
                'is_active' => true,
            ]);
        }

        /** @var PayrollWorkflowService $workflowService */
        $workflowService = app(PayrollWorkflowService::class);

        $period = $workflowService->createPeriod(
            'High Scale 100 Staff Benchmark',
            Carbon::parse('2026-08-01'),
            Carbon::parse('2026-08-31')
        );

        $startTime = microtime(true);
        $workflowService->calculatePeriodPayrolls($period);
        $elapsedMs = (microtime(true) - $startTime) * 1000;

        $period->refresh();
        $this->assertEquals(100, $period->payrolls()->count());

        // Performance Assertion: 100 employees calculation should complete within reasonable timeframe
        $this->assertLessThan(25000, $elapsedMs, "100 employees calculation should take under 25,000ms (Actual: {$elapsedMs}ms)");
    }
}
