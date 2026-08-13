<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">My Payroll & Payslips</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">My Payslips</li>
                </ul>
            </div>
            <div class="col-auto float-right ml-auto">
                @if($selectedPayroll)
                    <button type="button" onclick="window.print()" class="btn btn-outline-secondary rounded-pill">
                        <i class="fa fa-print mr-1"></i> Print / Save PDF
                    </button>
                @endif
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <div class="row">
        <!-- Sidebar Payroll Period List -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fa fa-history text-primary mr-2"></i> Payroll History
                    </h5>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($payrolls as $p)
                        <button type="button" 
                                wire:click="$set('selectedPayrollId', {{ $p->id }})" 
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $selectedPayroll && $selectedPayroll->id === $p->id ? 'active' : '' }}">
                            <div>
                                <strong class="d-block">{{ $p->period->name }}</strong>
                                <small class="{{ $selectedPayroll && $selectedPayroll->id === $p->id ? 'text-white-50' : 'text-muted' }}">
                                    {{ $p->period->period_start->format('M d, Y') }} - {{ $p->period->period_end->format('M d, Y') }}
                                </small>
                            </div>
                            <span class="badge {{ $p->status->badgeClass() }} px-2 py-1">
                                {{ $p->formatted_net_pay }}
                            </span>
                        </button>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fa fa-info-circle mb-2" style="font-size: 32px; display: block;"></i>
                            No payslips available yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Main Payslip Detail View -->
        <div class="col-md-8">
            @if($payload)
                <div class="card border-0 shadow-sm rounded-lg overflow-hidden p-4" id="printable-payslip">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h3 class="font-weight-bold text-primary mb-1">PAYSLIP</h3>
                            <span class="text-muted font-weight-bold">{{ $payload['period_name'] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="badge {{ $payload['status_badge'] }} px-3 py-2 font-weight-bold" style="font-size: 14px;">
                                Status: {{ $payload['status'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Employee & Period Info -->
                    <div class="row mb-4 bg-light p-3 rounded-lg mx-0">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <small class="text-muted text-uppercase font-weight-bold d-block">Employee Details</small>
                            <h5 class="font-weight-bold text-dark mb-0">{{ $payload['employee_name'] }}</h5>
                            <span class="text-muted small">ID: {{ $payload['employee_id'] }} | {{ $payload['email'] }}</span>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <small class="text-muted text-uppercase font-weight-bold d-block">Pay Period</small>
                            <span class="font-weight-bold text-dark d-block">{{ $payload['period_start'] }} to {{ $payload['period_end'] }}</span>
                        </div>
                    </div>

                    <!-- Line Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered custom-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Earnings / Line Item</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payload['items'] as $item)
                                    <tr>
                                        <td>
                                            <strong class="text-dark">{{ $item->description }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge {{ $item->type->isEarning() ? 'badge-soft-success text-success' : 'badge-soft-danger text-danger' }} px-2 py-1">
                                                {{ $item->type->label() }}
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold {{ $item->type->isEarning() ? 'text-dark' : 'text-danger' }}">
                                            {{ $item->type->isEarning() ? '' : '-' }}{{ sprintf('%.2f %s', $item->amount_minor / 100, $payload['currency']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Summary Card -->
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded-lg">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Gross Earnings:</span>
                                    <strong class="text-dark">{{ $payload['gross_pay'] }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Deductions:</span>
                                    <strong class="text-danger">-{{ $payload['total_deductions'] }}</strong>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold text-dark">NET PAYABLE:</span>
                                    <h4 class="font-weight-bold text-success mb-0">{{ $payload['net_pay'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-lg p-5 text-center text-muted">
                    <i class="fa fa-file-text-o mb-3" style="font-size: 48px; display: block;"></i>
                    <h5 class="font-weight-bold">No Payslip Selected</h5>
                    <p class="mb-0">Select a payroll period from the history menu to view details.</p>
                </div>
            @endif
        </div>
    </div>
</div>
