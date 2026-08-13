<?php

namespace Tests\Feature\Payroll;

use App\Models\EmployeeCompensation;
use App\Models\User;
use App\Services\Payroll\PayrollLedgerService;
use App\Services\Payroll\PayrollWorkflowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_payroll_journal_creates_balanced_double_entry_ledger(): void
    {
        $user = User::factory()->create(['role_name' => 'Employee']);

        EmployeeCompensation::create([
            'user_id' => $user->id,
            'effective_from' => '2026-01-01',
            'base_salary_minor' => 1000000, // 10,000 EGP
            'overtime_multiplier' => 1.5,
            'currency' => 'EGP',
            'is_active' => true,
        ]);

        /** @var PayrollWorkflowService $workflowService */
        $workflowService = app(PayrollWorkflowService::class);
        /** @var PayrollLedgerService $ledgerService */
        $ledgerService = app(PayrollLedgerService::class);

        $period = $workflowService->createPeriod(
            'August 2026 Journal Test',
            Carbon::parse('2026-08-01'),
            Carbon::parse('2026-08-31')
        );

        $workflowService->calculatePeriodPayrolls($period);

        $journal = $ledgerService->postPayrollJournal($period);

        $this->assertNotNull($journal);
        $this->assertTrue($journal->isBalanced());
        $this->assertEquals(1000000, $journal->total_debits_minor);
        $this->assertEquals(1000000, $journal->total_credits_minor);

        $this->assertDatabaseHas('journal_entries', [
            'payroll_journal_id' => $journal->id,
            'entry_type' => 'DEBIT',
            'account_name' => 'Gross Salary Expense',
            'amount_minor' => 1000000,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'payroll_journal_id' => $journal->id,
            'entry_type' => 'CREDIT',
            'account_name' => 'Payroll Payable to Staff',
            'amount_minor' => 590909,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'payroll_journal_id' => $journal->id,
            'entry_type' => 'CREDIT',
            'account_name' => 'Payroll Deductions Holding Account',
            'amount_minor' => 409091,
        ]);
    }
}
