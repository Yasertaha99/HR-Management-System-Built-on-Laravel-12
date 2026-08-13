<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Enterprise Payroll & Compensation</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payroll Management</li>
                </ul>
            </div>
            <div class="col-auto float-right ml-auto">
                <button type="button" wire:click="openCreatePeriodModal" class="btn btn-primary rounded-pill">
                    <i class="fa fa-plus mr-1"></i> New Payroll Period
                </button>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <!-- Alerts -->
    @if($successMessage)
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-lg mb-4" role="alert">
            <i class="fa fa-check-circle mr-2"></i> {{ $successMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-lg mb-4" role="alert">
            <i class="fa fa-exclamation-circle mr-2"></i> {{ $errorMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Period Selector & Action Bar -->
    <div class="card border-0 shadow-sm rounded-lg mb-4">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <label class="font-weight-bold text-dark mb-0 mr-3" style="min-width: 120px;">Payroll Period:</label>
                <select wire:model.live="selectedPeriodId" class="form-control rounded-pill" style="min-width: 250px;">
                    @forelse($periods as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->status->label() }})</option>
                    @empty
                        <option value="">No payroll periods found</option>
                    @endforelse
                </select>
            </div>

            @if($activePeriod)
                <div class="btn-group flex-wrap">
                    <button type="button" wire:click="calculatePayroll" wire:loading.attr="disabled" class="btn btn-outline-primary rounded-pill mr-2 mb-1" {{ $activePeriod->isLocked() ? 'disabled' : '' }}>
                        <i class="fa fa-calculator mr-1"></i> Recalculate Period
                    </button>
                    <button type="button" wire:click="approvePayroll" wire:loading.attr="disabled" class="btn btn-outline-success rounded-pill mr-2 mb-1" {{ $activePeriod->isLocked() ? 'disabled' : '' }}>
                        <i class="fa fa-check-circle mr-1"></i> Approve
                    </button>
                    <button type="button" wire:click="lockPeriod" wire:loading.attr="disabled" class="btn btn-outline-dark rounded-pill mr-2 mb-1" {{ $activePeriod->isLocked() ? 'disabled' : '' }}>
                        <i class="fa fa-lock mr-1"></i> Lock Period
                    </button>
                    <button type="button" wire:click="markPaid" wire:loading.attr="disabled" class="btn btn-success rounded-pill mb-1">
                        <i class="fa fa-money mr-1"></i> Mark as Paid
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row mb-4">
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <span class="dash-widget-icon bg-primary text-white"><i class="fa fa-users"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $totalCount }}</h3>
                        <span class="text-muted">Employees Included</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <span class="dash-widget-icon bg-info text-white"><i class="fa fa-money"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $grossPayroll }}</h3>
                        <span class="text-muted">Total Gross Pay</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <span class="dash-widget-icon bg-danger text-white"><i class="fa fa-minus-circle"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $totalDeductions }}</h3>
                        <span class="text-muted">Total Deductions</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <span class="dash-widget-icon bg-success text-white"><i class="fa fa-bank"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $netPayroll }}</h3>
                        <span class="text-muted">Total Net Payable</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm rounded-lg mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control rounded-pill px-3" placeholder="Search employee name, ID or email...">
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="card border-0 shadow-sm rounded-lg">
        <div class="card-header bg-white py-3">
            <h5 class="card-title font-weight-bold text-dark mb-0">
                <i class="fa fa-file-text-o text-primary mr-2"></i> Period Payslips & Payroll Items
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover custom-table mb-0 align-middle">
                    <thead class="thead-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Employee ID</th>
                            <th>Gross Salary</th>
                            <th>Total Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $p)
                            <tr>
                                <td>
                                    <strong class="text-dark d-block">{{ $p->user->name ?? 'Unknown' }}</strong>
                                    <small class="text-muted">{{ $p->user->email ?? '' }}</small>
                                </td>
                                <td><span class="badge badge-light border">{{ $p->user->user_id ?? 'N/A' }}</span></td>
                                <td class="font-weight-bold text-dark">{{ $p->formatted_gross_pay }}</td>
                                <td class="text-danger font-weight-bold">{{ $p->formatted_total_deductions }}</td>
                                <td class="font-weight-bold text-success" style="font-size: 15px;">{{ $p->formatted_net_pay }}</td>
                                <td>
                                    <span class="badge {{ $p->status->badgeClass() }} px-3 py-1 font-weight-bold">
                                        {{ $p->status->label() }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('payroll.payslips', ['id' => $p->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="fa fa-eye"></i> View Payslip
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa fa-folder-open-o text-muted mb-2" style="font-size: 36px; d-block;"></i>
                                    <p class="mb-0">No payroll records found for this period. Click 'Recalculate Period' to calculate.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payrolls->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Period Modal -->
    @if($showCreatePeriodModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 rounded-lg shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white font-weight-bold">
                            <i class="fa fa-calendar-plus-o mr-2"></i> Create New Payroll Period
                        </h5>
                        <button type="button" wire:click="closeCreatePeriodModal" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark">Period Name</label>
                            <input type="text" wire:model="periodName" class="form-control rounded-pill">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark">Start Date</label>
                            <input type="date" wire:model="periodStart" class="form-control rounded-pill">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark">End Date</label>
                            <input type="date" wire:model="periodEnd" class="form-control rounded-pill">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" wire:click="closeCreatePeriodModal" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                        <button type="button" wire:click="createPeriod" class="btn btn-primary rounded-pill px-4 font-weight-bold">Save Period</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
