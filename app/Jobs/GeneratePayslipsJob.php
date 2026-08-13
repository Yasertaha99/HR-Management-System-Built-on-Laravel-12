<?php

namespace App\Jobs;

use App\Models\PayrollPeriod;
use App\Services\Payroll\PayslipGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePayslipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    public function __construct(
        public readonly int $payrollPeriodId
    ) {}

    public function handle(PayslipGenerator $generator): void
    {
        $period = PayrollPeriod::with('payrolls.snapshot')->findOrFail($this->payrollPeriodId);

        foreach ($period->payrolls as $payroll) {
            $generator->generatePayslipPayload($payroll);
        }
    }
}
