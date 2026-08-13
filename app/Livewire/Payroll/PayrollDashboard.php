<?php

namespace App\Livewire\Payroll;

use App\Enums\PayrollStatus;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PayrollDashboard extends Component
{
    use WithPagination;

    public ?int $selectedPeriodId = null;
    public string $search = '';
    public string $statusFilter = 'all';
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    // Period Creation Form
    public string $periodName = '';
    public string $periodStart = '';
    public string $periodEnd = '';
    public bool $showCreatePeriodModal = false;

    public function mount(): void
    {
        $latestPeriod = PayrollPeriod::orderBy('period_start', 'desc')->first();
        $this->selectedPeriodId = $latestPeriod?->id;

        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd = now()->endOfMonth()->toDateString();
        $this->periodName = now()->format('F Y') . ' Payroll';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreatePeriodModal(): void
    {
        $this->showCreatePeriodModal = true;
    }

    public function closeCreatePeriodModal(): void
    {
        $this->showCreatePeriodModal = false;
    }

    public function createPeriod(PayrollWorkflowService $service): void
    {
        $this->validate([
            'periodName' => 'required|string|max:255',
            'periodStart' => 'required|date',
            'periodEnd' => 'required|date|after_or_equal:periodStart',
        ]);

        try {
            $period = $service->createPeriod(
                $this->periodName,
                Carbon::parse($this->periodStart),
                Carbon::parse($this->periodEnd)
            );

            $this->selectedPeriodId = $period->id;
            $this->showCreatePeriodModal = false;
            $this->successMessage = "Payroll Period '{$period->name}' created successfully.";
        } catch (\Throwable $e) {
            $this->errorMessage = "Failed to create payroll period: " . $e->getMessage();
        }
    }

    public function calculatePayroll(PayrollWorkflowService $service): void
    {
        if (!$this->selectedPeriodId) return;

        $period = PayrollPeriod::findOrFail($this->selectedPeriodId);

        try {
            $service->calculatePeriodPayrolls($period);
            $this->successMessage = "Payroll calculated successfully for '{$period->name}'.";
        } catch (\Throwable $e) {
            $this->errorMessage = "Calculation failed: " . $e->getMessage();
        }
    }

    public function approvePayroll(PayrollWorkflowService $service): void
    {
        if (!$this->selectedPeriodId) return;
        $period = PayrollPeriod::findOrFail($this->selectedPeriodId);

        try {
            $service->approvePeriod($period);
            $this->successMessage = "Payroll period '{$period->name}' approved successfully.";
        } catch (\Throwable $e) {
            $this->errorMessage = "Approval failed: " . $e->getMessage();
        }
    }

    public function lockPayroll(PayrollWorkflowService $service): void
    {
        if (!$this->selectedPeriodId) return;
        $period = PayrollPeriod::findOrFail($this->selectedPeriodId);

        try {
            $service->lockPeriod($period, Auth::id());
            $this->successMessage = "Payroll period '{$period->name}' locked permanently.";
        } catch (\Throwable $e) {
            $this->errorMessage = "Lock failed: " . $e->getMessage();
        }
    }

    public function markPaid(PayrollWorkflowService $service): void
    {
        if (!$this->selectedPeriodId) return;
        $period = PayrollPeriod::findOrFail($this->selectedPeriodId);

        try {
            $service->markPaid($period);
            $this->successMessage = "Payroll period '{$period->name}' marked as Paid.";
        } catch (\Throwable $e) {
            $this->errorMessage = "Operation failed: " . $e->getMessage();
        }
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isHr() && !$user->isManager()) {
            abort(403, 'Unauthorized action.');
        }

        $periods = PayrollPeriod::orderBy('period_start', 'desc')->get();
        $activePeriod = $this->selectedPeriodId ? PayrollPeriod::find($this->selectedPeriodId) : null;

        $payrollsQuery = Payroll::with(['user', 'items', 'period'])
            ->when($this->selectedPeriodId, fn ($q) => $q->where('payroll_period_id', $this->selectedPeriodId));

        if (!empty($this->search)) {
            $payrollsQuery->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('user_id', 'like', '%' . $this->search . '%');
            });
        }

        $payrolls = $payrollsQuery->paginate(15);

        // Summary Cards metrics
        $allPayrolls = $this->selectedPeriodId
            ? Payroll::where('payroll_period_id', $this->selectedPeriodId)->get()
            : Payroll::all();

        $grossPayroll = $allPayrolls->sum('gross_pay_minor');
        $totalDeductions = $allPayrolls->sum('total_deductions_minor');
        $netPayroll = $allPayrolls->sum('net_pay_minor');
        $totalCount = $allPayrolls->count();

        return view('livewire.payroll.payroll-dashboard', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
            'payrolls' => $payrolls,
            'grossPayroll' => sprintf('%.2f EGP', $grossPayroll / 100),
            'totalDeductions' => sprintf('%.2f EGP', $totalDeductions / 100),
            'netPayroll' => sprintf('%.2f EGP', $netPayroll / 100),
            'totalCount' => $totalCount,
        ])->layout('layouts.master');
    }
}
