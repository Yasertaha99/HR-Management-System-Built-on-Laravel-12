<div class="content container-fluid" x-data="workingTimer()">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Employee Attendance Dashboard</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Attendance</li>
                </ul>
            </div>
            <div class="col-auto float-right ml-auto">
                @if(Auth::user()->isManager() || Auth::user()->isHr() || Auth::user()->isAdmin())
                    <a href="{{ route('attendance.manage') }}" class="btn btn-outline-primary rounded-pill">
                        <i class="fa fa-users mr-1"></i> Workforce Attendance Manager
                    </a>
                @endif
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <!-- Notifications / Alerts -->
    @if($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-lg mb-4" role="alert">
            <i class="fa fa-exclamation-circle mr-2"></i> {{ $errorMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($successMessage)
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-lg mb-4" role="alert">
            <i class="fa fa-check-circle mr-2"></i> {{ $successMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Today's Attendance Action Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title text-white mb-0">
                        <i class="fa fa-clock-o text-warning mr-2"></i> Today's Workday — {{ now()->format('l, F j, Y') }}
                    </h5>
                    <span class="badge badge-light px-3 py-2 text-dark font-weight-bold" style="font-size: 13px;">
                        Server Time: {{ now()->format('h:i A') }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <!-- Status Badge & Details -->
                        <div class="col-lg-7 col-md-12 border-right-md mb-3 mb-lg-0">
                            <div class="d-flex align-items-center mb-3">
                                <span class="mr-3 font-weight-bold text-muted text-uppercase" style="letter-spacing: 1px; font-size: 12px;">Status:</span>
                                @if(!$todayAttendance)
                                    <span class="badge badge-secondary px-3 py-2" style="font-size: 14px;">
                                        <i class="fa fa-circle text-muted mr-1"></i> Not Started
                                    </span>
                                @elseif($todayAttendance->isWorking())
                                    <span class="badge badge-warning text-dark px-3 py-2 font-weight-bold" style="font-size: 14px;">
                                        <i class="fa fa-spinner fa-spin text-dark mr-1"></i> WORKING
                                    </span>
                                @elseif($todayAttendance->isCompleted())
                                    <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 14px;">
                                        <i class="fa fa-check-circle text-white mr-1"></i> COMPLETED
                                    </span>
                                @endif
                            </div>

                            <div class="row text-center text-sm-left mt-3">
                                <div class="col-4">
                                    <small class="text-muted d-block text-uppercase font-weight-bold">Check In</small>
                                    <span class="h6 font-weight-bold text-dark mb-0">
                                        {{ $todayAttendance && $todayAttendance->check_in ? $todayAttendance->check_in->format('h:i A') : '—' }}
                                    </span>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block text-uppercase font-weight-bold">Check Out</small>
                                    <span class="h6 font-weight-bold text-dark mb-0">
                                        {{ $todayAttendance && $todayAttendance->check_out ? $todayAttendance->check_out->format('h:i A') : '—' }}
                                    </span>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block text-uppercase font-weight-bold">Duration</small>
                                    <span class="h6 font-weight-bold text-primary mb-0">
                                        @if($todayAttendance && $todayAttendance->isWorking())
                                            <span x-text="timerText">{{ $todayAttendance->formatted_duration }}</span>
                                        @elseif($todayAttendance && $todayAttendance->isCompleted())
                                            {{ $todayAttendance->formatted_duration }} ({{ $todayAttendance->rounded_hours }}h rounded)
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button & Notes -->
                        <div class="col-lg-5 col-md-12 text-center text-lg-right">
                            <div class="form-group mb-3">
                                <input type="text" wire:model="notes" class="form-control form-control-sm rounded-pill px-3" placeholder="Optional notes for today's workday..." {{ $todayAttendance && $todayAttendance->isCompleted() ? 'disabled' : '' }}>
                            </div>

                            @if(!$todayAttendance)
                                <button type="button" 
                                        wire:click="startAttendance" 
                                        wire:loading.attr="disabled"
                                        class="btn btn-success btn-lg btn-block rounded-pill shadow-sm font-weight-bold py-3" 
                                        style="letter-spacing: 0.5px;">
                                    <span wire:loading.remove wire:target="startAttendance">
                                        <i class="fa fa-play-circle mr-2"></i> START ATTENDANCE
                                    </span>
                                    <span wire:loading wire:target="startAttendance">
                                        <i class="fa fa-spinner fa-spin mr-2"></i> Starting...
                                    </span>
                                </button>
                            @elseif($todayAttendance->isWorking())
                                <button type="button" 
                                        wire:click="confirmCheckout" 
                                        wire:loading.attr="disabled"
                                        class="btn btn-danger btn-lg btn-block rounded-pill shadow-sm font-weight-bold py-3"
                                        style="letter-spacing: 0.5px;">
                                    <span wire:loading.remove wire:target="finishWorkday">
                                        <i class="fa fa-stop-circle mr-2"></i> FINISH WORKDAY
                                    </span>
                                    <span wire:loading wire:target="finishWorkday">
                                        <i class="fa fa-spinner fa-spin mr-2"></i> Processing...
                                    </span>
                                </button>
                            @elseif($todayAttendance->isCompleted())
                                <button type="button" class="btn btn-secondary btn-lg btn-block rounded-pill font-weight-bold py-3" disabled>
                                    <i class="fa fa-check-double mr-2"></i> WORKDAY COMPLETED
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal for Checkout -->
    @if($showCheckoutConfirmModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 rounded-lg shadow-lg">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white font-weight-bold">
                            <i class="fa fa-exclamation-triangle mr-2"></i> Confirm Finish Workday
                        </h5>
                        <button type="button" wire:click="cancelCheckout" class="close text-white" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <i class="fa fa-clock-o text-danger mb-3" style="font-size: 48px;"></i>
                        <h5 class="font-weight-bold text-dark">Are you sure you want to finish your workday?</h5>
                        <p class="text-muted">
                            Once finished, your attendance for today will be finalized and sent for duration & rounded-hour calculation.
                        </p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" wire:click="cancelCheckout" class="btn btn-secondary rounded-pill px-4">Cancel</button>
                        <button type="button" wire:click="finishWorkday" wire:loading.attr="disabled" class="btn btn-danger rounded-pill px-4 font-weight-bold">
                            <span wire:loading.remove wire:target="finishWorkday">Yes, Finish Workday</span>
                            <span wire:loading wire:target="finishWorkday"><i class="fa fa-spinner fa-spin"></i> Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Month Navigation Bar -->
    <div class="card border-0 shadow-sm rounded-lg mb-4">
        <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center">
            <div class="btn-group mb-2 mb-sm-0" role="group">
                <button type="button" wire:click="previousMonth" class="btn btn-outline-secondary rounded-left">
                    <i class="fa fa-chevron-left mr-1"></i> Previous Month
                </button>
                <button type="button" wire:click="currentMonth" class="btn btn-outline-secondary">
                    Current Month
                </button>
                <button type="button" wire:click="nextMonth" class="btn btn-outline-secondary rounded-right">
                    Next Month <i class="fa fa-chevron-right ml-1"></i>
                </button>
            </div>
            <h4 class="font-weight-bold text-dark mb-0">{{ $monthName }}</h4>
        </div>
    </div>

    <!-- Monthly Statistics Cards -->
    <div class="row">
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <span class="dash-widget-icon bg-success text-white"><i class="fa fa-check"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $statistics->presentCount }}</h3>
                        <span class="text-muted">Present Days</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <span class="dash-widget-icon bg-danger text-white"><i class="fa fa-times"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $statistics->absentCount }}</h3>
                        <span class="text-muted">Absent Days</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <span class="dash-widget-icon bg-primary text-white"><i class="fa fa-hourglass-half"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $statistics->getFormattedActualTime() }}</h3>
                        <span class="text-muted">Actual Work Duration</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <span class="dash-widget-icon bg-info text-white"><i class="fa fa-calculator"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $statistics->totalRoundedHours }}h</h3>
                        <span class="text-muted">Calculated Rounded Hours</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Interactive Calendar -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fa fa-calendar text-primary mr-2"></i> Attendance Calendar — {{ $monthName }}
                    </h5>
                </div>
                <div class="card-body p-3">
                    <!-- Calendar Legend -->
                    <div class="d-flex flex-wrap align-items-center mb-3 text-muted" style="font-size: 13px;">
                        <span class="mr-3 mb-1"><span class="badge badge-success px-2 py-1 mr-1">🟢 Completed</span></span>
                        <span class="mr-3 mb-1"><span class="badge badge-warning text-dark px-2 py-1 mr-1">🟡 Working</span></span>
                        <span class="mr-3 mb-1"><span class="badge badge-danger px-2 py-1 mr-1">🔴 Absent</span></span>
                        <span class="mr-3 mb-1"><span class="badge badge-info px-2 py-1 mr-1">🔵 Leave</span></span>
                        <span class="mr-3 mb-1"><span class="badge badge-purple text-white px-2 py-1 mr-1" style="background-color: #6f42c1;">🟣 Holiday</span></span>
                        <span class="mr-3 mb-1"><span class="badge badge-light text-muted border px-2 py-1 mr-1">⚪ Weekend / Future</span></span>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="table-responsive">
                        <div class="d-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;">
                            @php
                                $firstDayOfWeek = $calendarDays[0]['date']->dayOfWeek;
                            @endphp
                            
                            @for($i = 0; $i < $firstDayOfWeek; $i++)
                                <div class="p-2 border rounded-lg bg-light opacity-50" style="min-height: 80px;"></div>
                            @endfor

                            @foreach($calendarDays as $day)
                                <div class="p-2 border rounded-lg position-relative {{ $day['is_today'] ? 'border-primary shadow-sm bg-white' : 'bg-light' }}" style="min-height: 85px;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold {{ $day['is_today'] ? 'text-primary' : 'text-dark' }}" style="font-size: 14px;">
                                            {{ $day['day_number'] }}
                                        </span>
                                        <small class="text-muted" style="font-size: 11px;">{{ $day['day_name'] }}</small>
                                    </div>

                                    <div class="mt-1">
                                        @if($day['status'] === \App\Enums\AttendanceStatus::COMPLETED)
                                            <span class="badge badge-success d-block text-truncate p-1 font-weight-normal">
                                                <i class="fa fa-check-circle"></i> Completed
                                                @if($day['attendance'] && $day['attendance']->rounded_hours)
                                                    ({{ $day['attendance']->rounded_hours }}h)
                                                @endif
                                            </span>
                                        @elseif($day['status'] === \App\Enums\AttendanceStatus::WORKING)
                                            <span class="badge badge-warning text-dark d-block text-truncate p-1 font-weight-bold">
                                                <i class="fa fa-spinner fa-spin"></i> Working
                                            </span>
                                        @elseif($day['status'] === \App\Enums\AttendanceStatus::ABSENT)
                                            <span class="badge badge-danger d-block text-truncate p-1 font-weight-normal">
                                                <i class="fa fa-times-circle"></i> Absent
                                            </span>
                                        @elseif($day['status'] === \App\Enums\AttendanceStatus::HOLIDAY)
                                            <span class="badge badge-purple text-white d-block text-truncate p-1 font-weight-normal" style="background-color: #6f42c1;">
                                                <i class="fa fa-star"></i> Holiday
                                            </span>
                                        @elseif($day['status'] === \App\Enums\AttendanceStatus::DAY_OFF)
                                            <span class="badge badge-light text-muted border d-block text-truncate p-1 font-weight-normal">
                                                Day Off
                                            </span>
                                        @else
                                            <span class="text-muted d-block text-center mt-2" style="font-size: 11px;">—</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fa fa-history text-secondary mr-2"></i> Detailed Attendance History
                    </h5>
                    <span class="text-muted font-weight-normal" style="font-size: 13px;">
                        Showing {{ $monthlyAttendances->count() }} attendance records
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover custom-table mb-0 align-middle">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Actual Duration</th>
                                    <th>Rounded Hours</th>
                                    <th>Late</th>
                                    <th>Early Leave</th>
                                    <th>Overtime</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyAttendances as $record)
                                    <tr>
                                        <td class="font-weight-bold text-dark">
                                            {{ $record->attendance_date->format('M d, Y') }}
                                        </td>
                                        <td class="text-muted">{{ $record->attendance_date->format('l') }}</td>
                                        <td>
                                            <span class="badge badge-soft-success text-success font-weight-bold px-2 py-1" style="font-size: 13px;">
                                                <i class="fa fa-sign-in mr-1"></i> {{ $record->check_in ? $record->check_in->format('h:i A') : '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($record->check_out)
                                                <span class="badge badge-soft-danger text-danger font-weight-bold px-2 py-1" style="font-size: 13px;">
                                                    <i class="fa fa-sign-out mr-1"></i> {{ $record->check_out->format('h:i A') }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold text-primary">
                                            {{ $record->formatted_duration }}
                                        </td>
                                        <td class="font-weight-bold text-dark">
                                            {{ $record->rounded_hours !== null ? $record->rounded_hours . 'h' : '—' }}
                                        </td>
                                        <td>
                                            @if($record->late_minutes > 0)
                                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                                    {{ $record->late_minutes }}m
                                                </span>
                                            @else
                                                <span class="text-muted">0m</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->early_leave_minutes > 0)
                                                <span class="badge badge-info px-2 py-1">
                                                    {{ $record->early_leave_minutes }}m
                                                </span>
                                            @else
                                                <span class="text-muted">0m</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($record->overtime_minutes > 0)
                                                <span class="badge badge-success px-2 py-1">
                                                    {{ $record->overtime_minutes }}m
                                                </span>
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
                                            <i class="fa fa-folder-open-o text-muted mb-2" style="font-size: 36px; d-block;"></i>
                                            <p class="mb-0">No attendance records found for {{ $monthName }}.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function workingTimer() {
    return {
        checkInTime: @json($todayAttendance && $todayAttendance->isWorking() ? $todayAttendance->check_in->toIso8601String() : null),
        timerText: '00h 00m 00s',
        intervalId: null,

        init() {
            if (this.checkInTime) {
                this.updateTimer();
                this.intervalId = setInterval(() => this.updateTimer(), 1000);
            }
        },

        updateTimer() {
            if (!this.checkInTime) return;
            const start = new Date(this.checkInTime).getTime();
            const now = new Date().getTime();
            const diffMs = Math.max(0, now - start);

            const seconds = Math.floor((diffMs / 1000) % 60);
            const minutes = Math.floor((diffMs / (1000 * 60)) % 60);
            const hours = Math.floor(diffMs / (1000 * 60 * 60));

            const pad = (n) => n.toString().padStart(2, '0');
            this.timerText = `${pad(hours)}h ${pad(minutes)}m ${pad(seconds)}s`;
        }
    };
}
</script>
