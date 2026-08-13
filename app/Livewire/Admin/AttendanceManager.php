<?php

namespace App\Livewire\Admin;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceManager extends Component
{
    use WithPagination;

    public string $selectedDate;
    public string $search = '';
    public string $statusFilter = 'all';

    public function mount(?string $date = null): void
    {
        $this->selectedDate = $date ?? now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function setDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->resetPage();
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isHr() && !$user->isManager()) {
            abort(403, 'Unauthorized action.');
        }

        $date = Carbon::parse($this->selectedDate)->toDateString();

        // Total registered active users
        $totalEmployees = User::where('status', 'Active')->count();

        // Today's attendances query
        $attendancesQuery = Attendance::with('user')
            ->where('attendance_date', $date);

        if (!empty($this->search)) {
            $attendancesQuery->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('user_id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $attendancesQuery->where('status', $this->statusFilter);
        }

        $attendances = $attendancesQuery->orderBy('check_in', 'asc')->paginate(15);

        // Daily summary stats
        $allTodayAttendances = Attendance::where('attendance_date', $date)->get();
        $presentToday = $allTodayAttendances->count();
        $workingNow = $allTodayAttendances->where('status', AttendanceStatus::WORKING)->count();
        $completedToday = $allTodayAttendances->where('status', AttendanceStatus::COMPLETED)->count();
        $lateToday = $allTodayAttendances->where('late_minutes', '>', 0)->count();
        $absentToday = max(0, $totalEmployees - $presentToday);

        return view('livewire.admin.attendance-manager', [
            'totalEmployees' => $totalEmployees,
            'presentToday' => $presentToday,
            'workingNow' => $workingNow,
            'completedToday' => $completedToday,
            'lateToday' => $lateToday,
            'absentToday' => $absentToday,
            'attendances' => $attendances,
            'formattedDate' => Carbon::parse($date)->format('l, F j, Y'),
        ])->layout('layouts.master');
    }
}
