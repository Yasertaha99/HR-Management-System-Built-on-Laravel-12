<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Workforce Attendance Management</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('attendance.dashboard') }}">My Attendance</a></li>
                    <li class="breadcrumb-item active">Workforce Management</li>
                </ul>
            </div>
            <div class="col-auto float-right ml-auto">
                <a href="{{ route('attendance.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fa fa-arrow-left mr-1"></i> Back to My Attendance
                </a>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <!-- Summary Widgets -->
    <div class="row">
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-2">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body p-3 text-center">
                    <span class="avatar bg-primary text-white mb-2"><i class="fa fa-users"></i></span>
                    <h3 class="mb-0 font-weight-bold">{{ $totalEmployees }}</h3>
                    <small class="text-muted text-uppercase font-weight-bold">Total Staff</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-2">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body p-3 text-center">
                    <span class="avatar bg-success text-white mb-2"><i class="fa fa-check-circle"></i></span>
                    <h3 class="mb-0 font-weight-bold">{{ $presentToday }}</h3>
                    <small class="text-muted text-uppercase font-weight-bold">Present Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-2">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body p-3 text-center">
                    <span class="avatar bg-warning text-dark mb-2"><i class="fa fa-clock-o"></i></span>
                    <h3 class="mb-0 font-weight-bold">{{ $workingNow }}</h3>
                    <small class="text-muted text-uppercase font-weight-bold">Working Now</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-2">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body p-3 text-center">
                    <span class="avatar bg-info text-white mb-2"><i class="fa fa-exclamation-triangle"></i></span>
                    <h3 class="mb-0 font-weight-bold">{{ $lateToday }}</h3>
                    <small class="text-muted text-uppercase font-weight-bold">Late Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-2">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body p-3 text-center">
                    <span class="avatar bg-danger text-white mb-2"><i class="fa fa-user-times"></i></span>
                    <h3 class="mb-0 font-weight-bold">{{ $absentToday }}</h3>
                    <small class="text-muted text-uppercase font-weight-bold">Absent Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-2">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body p-3 text-center">
                    <span class="avatar bg-secondary text-white mb-2"><i class="fa fa-check-double"></i></span>
                    <h3 class="mb-0 font-weight-bold">{{ $completedToday }}</h3>
                    <small class="text-muted text-uppercase font-weight-bold">Completed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card border-0 shadow-sm rounded-lg mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="text-muted small font-weight-bold mb-1">Date</label>
                    <input type="date" wire:model.live="selectedDate" class="form-control rounded-pill">
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="text-muted small font-weight-bold mb-1">Filter Status</label>
                    <select wire:model.live="statusFilter" class="form-control rounded-pill">
                        <option value="all">All Statuses</option>
                        <option value="working">Working Now</option>
                        <option value="completed">Completed</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="text-muted small font-weight-bold mb-1">Search Employee</label>
                    <div class="input-group">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control rounded-pill px-3" placeholder="Search by name, ID or email...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Attendance Table -->
    <div class="card border-0 shadow-sm rounded-lg">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold text-dark mb-0">
                <i class="fa fa-list text-primary mr-2"></i> Attendance Log for {{ $formattedDate }}
            </h5>
            <span class="badge badge-light border px-3 py-2 text-dark">
                Total Records: {{ $attendances->total() }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover custom-table mb-0 align-middle">
                    <thead class="thead-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Employee ID</th>
                            <th>Role / Position</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Actual Duration</th>
                            <th>Rounded Hours</th>
                            <th>Late</th>
                            <th>Overtime</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $record)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs mr-2">
                                            <span class="avatar-title rounded-circle bg-primary text-white font-weight-bold">
                                                {{ strtoupper(substr($record->user->name ?? 'E', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block">{{ $record->user->name ?? 'Unknown User' }}</strong>
                                            <small class="text-muted">{{ $record->user->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-light border">{{ $record->user->user_id ?? 'N/A' }}</span></td>
                                <td><small class="text-muted font-weight-bold">{{ $record->user->role_name ?? 'Employee' }}</small></td>
                                <td>
                                    <span class="badge badge-soft-success text-success px-2 py-1 font-weight-bold">
                                        <i class="fa fa-sign-in mr-1"></i> {{ $record->check_in ? $record->check_in->format('h:i A') : '—' }}
                                    </span>
                                </td>
                                <td>
                                    @if($record->check_out)
                                        <span class="badge badge-soft-danger text-danger px-2 py-1 font-weight-bold">
                                            <i class="fa fa-sign-out mr-1"></i> {{ $record->check_out->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="font-weight-bold text-primary">{{ $record->formatted_duration }}</td>
                                <td class="font-weight-bold text-dark">{{ $record->rounded_hours !== null ? $record->rounded_hours . 'h' : '—' }}</td>
                                <td>
                                    @if($record->late_minutes > 0)
                                        <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">{{ $record->late_minutes }}m</span>
                                    @else
                                        <span class="text-muted">0m</span>
                                    @endif
                                </td>
                                <td>
                                    @if($record->overtime_minutes > 0)
                                        <span class="badge badge-success px-2 py-1 font-weight-bold">{{ $record->overtime_minutes }}m</span>
                                    @else
                                        <span class="text-muted">0m</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $record->status->badgeClass() }} px-3 py-1 font-weight-bold">
                                        {{ $record->status->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fa fa-info-circle mb-2" style="font-size: 36px; d-block;"></i>
                                    <p class="mb-0">No attendance records found matching the criteria for {{ $formattedDate }}.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendances->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
