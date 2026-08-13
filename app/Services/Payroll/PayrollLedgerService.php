<?php

namespace App\Services\Payroll;

use App\Models\JournalEntry;
use App\Models\PayrollJournal;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PayrollLedgerService
{
    /**
     * Post double-entry financial journal for a calculated/approved payroll period.
     * Enforces Debits === Credits accounting balance invariant.
     */
    public function postPayrollJournal(PayrollPeriod $period): PayrollJournal
    {
        return DB::transaction(function () use ($period) {
            $payrolls = $period->payrolls;

            $totalGrossMinor = $payrolls->sum('gross_pay_minor');
            $totalDeductionsMinor = $payrolls->sum('total_deductions_minor');
            $totalNetMinor = $payrolls->sum('net_pay_minor');
            $currency = $payrolls->first()?->currency ?? 'EGP';

            // Double Entry Invariant Check: Gross (Debit) = Net (Credit) + Deductions (Credit)
            if ($totalGrossMinor !== ($totalNetMinor + $totalDeductionsMinor)) {
                throw new InvalidArgumentException("Unbalanced payroll calculations. Debits must equal Credits.");
            }

            $ref = 'JRN-' . $period->id . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

            /** @var PayrollJournal $journal */
            $journal = PayrollJournal::updateOrCreate(
                ['payroll_period_id' => $period->id],
                [
                    'reference_number' => $ref,
                    'posting_date' => now()->toDateString(),
                    'status' => 'posted',
                    'total_debits_minor' => $totalGrossMinor,
                    'total_credits_minor' => $totalGrossMinor,
                    'currency' => $currency,
                ]
            );

            // Re-create entry lines
            $journal->entries()->delete();

            // 1. DEBIT: Salary Expense (Gross Pay)
            JournalEntry::create([
                'payroll_journal_id' => $journal->id,
                'account_type' => 'EXPENSE',
                'account_name' => 'Gross Salary Expense',
                'entry_type' => 'DEBIT',
                'amount_minor' => $totalGrossMinor,
                'currency' => $currency,
                'description' => "Total gross salary expense for {$period->name}",
            ]);

            // 2. CREDIT: Net Salary Liabilities (Net Payable to Employees)
            JournalEntry::create([
                'payroll_journal_id' => $journal->id,
                'account_type' => 'LIABILITY',
                'account_name' => 'Payroll Payable to Staff',
                'entry_type' => 'CREDIT',
                'amount_minor' => $totalNetMinor,
                'currency' => $currency,
                'description' => "Net payable salaries for {$period->name}",
            ]);

            // 3. CREDIT: Deduction Holding Accounts
            if ($totalDeductionsMinor > 0) {
                JournalEntry::create([
                    'payroll_journal_id' => $journal->id,
                    'account_type' => 'HOLDING',
                    'account_name' => 'Payroll Deductions Holding Account',
                    'entry_type' => 'CREDIT',
                    'amount_minor' => $totalDeductionsMinor,
                    'currency' => $currency,
                    'description' => "Total deductions withheld for {$period->name}",
                ]);
            }

            return $journal->fresh('entries');
        });
    }
}
