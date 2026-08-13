<?php

namespace App\Livewire\Payroll;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayslipGenerator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmployeePayslips extends Component
{
    public ?int $selectedPayrollId = null;

    public function mount(?int $id = null): void
    {
        $this->selectedPayrollId = $id;
    }

    public function render(PayslipGenerator $generator)
    {
        $userId = Auth::id();

        $payrolls = Payroll::with(['period', 'items', 'snapshot'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        $selectedPayroll = null;
        $payslipPayload = null;

        if ($this->selectedPayrollId) {
            $selectedPayroll = $payrolls->firstWhere('id', $this->selectedPayrollId);
        } else {
            $selectedPayroll = $payrolls->first();
        }

        if ($selectedPayroll) {
            $payslipPayload = $generator->generatePayslipPayload($selectedPayroll);
        }

        return view('livewire.payroll.employee-payslips', [
            'payrolls' => $payrolls,
            'selectedPayroll' => $selectedPayroll,
            'payload' => $payslipPayload,
        ])->layout('layouts.master');
    }
}
