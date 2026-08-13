<?php

namespace App\Livewire\Attendance;

use App\DTOs\Attendance\CheckInData;
use App\DTOs\Attendance\CheckOutData;
use App\Exceptions\Attendance\AttendanceAlreadyCompletedException;
use App\Exceptions\Attendance\AttendanceAlreadyStartedException;
use App\Exceptions\Attendance\AttendanceNotStartedException;
use App\Exceptions\Attendance\InvalidAttendanceActionException;
use App\Services\Attendance\AttendanceQueryService;
use App\Services\Attendance\AttendanceStatisticsService;
use App\Services\Attendance\CheckInService;
use App\Services\Attendance\CheckOutService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public int $selectedYear;
    public int $selectedMonth;
    public ?string $notes = null;
    public bool $showCheckoutConfirmModal = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    protected $listeners = [
        'refreshAttendance' => '$refresh',
    ];

    public function mount(?int $year = null, ?int $month = null): void
    {
        $this->selectedYear = $year ?? (int) now()->format('Y');
        $this->selectedMonth = $month ?? (int) now()->format('m');
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedYear = (int) $date->format('Y');
        $this->selectedMonth = (int) $date->format('m');
        $this->clearMessages();
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedYear = (int) $date->format('Y');
        $this->selectedMonth = (int) $date->format('m');
        $this->clearMessages();
    }

    public function currentMonth(): void
    {
        $this->selectedYear = (int) now()->format('Y');
        $this->selectedMonth = (int) now()->format('m');
        $this->clearMessages();
    }

    public function startAttendance(CheckInService $service): void
    {
        $this->clearMessages();

        try {
            $data = new CheckInData(
                userId: Auth::id(),
                timestamp: now(),
                notes: $this->notes,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent()
            );

            $service->checkIn($data);
            $this->notes = null;
            $this->successMessage = "Workday started successfully at " . now()->format('h:i A') . ".";
            $this->dispatch('refreshAttendance');
        } catch (AttendanceAlreadyStartedException | AttendanceAlreadyCompletedException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            \Log::error('Check-in failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->errorMessage = "Unable to start attendance. Please try again.";
        }
    }

    public function confirmCheckout(): void
    {
        $this->clearMessages();
        $this->showCheckoutConfirmModal = true;
    }

    public function cancelCheckout(): void
    {
        $this->showCheckoutConfirmModal = false;
    }

    public function finishWorkday(CheckOutService $service): void
    {
        $this->clearMessages();
        $this->showCheckoutConfirmModal = false;

        try {
            $data = new CheckOutData(
                userId: Auth::id(),
                timestamp: now(),
                notes: $this->notes,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent()
            );

            $attendance = $service->checkOut($data);
            $this->notes = null;
            $this->successMessage = sprintf(
                "Workday finished! Actual duration: %s. Calculated rounded hours: %dh.",
                $attendance->formatted_duration,
                $attendance->rounded_hours
            );
            $this->dispatch('refreshAttendance');
        } catch (AttendanceNotStartedException | AttendanceAlreadyCompletedException | InvalidAttendanceActionException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Throwable $e) {
            \Log::error('Checkout failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->errorMessage = "Unable to finish workday. Please try again.";
        }
    }

    private function clearMessages(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function render(
        AttendanceQueryService $queryService,
        AttendanceStatisticsService $statsService
    ) {
        $user = Auth::user();
        $todayAttendance = $queryService->getTodayAttendance($user->id);
        $calendarDays = $queryService->buildMonthlyCalendar($user->id, $this->selectedYear, $this->selectedMonth);
        $statistics = $statsService->calculateMonthlyStatistics($user->id, $this->selectedYear, $this->selectedMonth);
        $monthlyAttendances = $queryService->getMonthlyAttendances($user->id, $this->selectedYear, $this->selectedMonth);
        $monthName = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->format('F Y');

        return view('livewire.attendance.dashboard', [
            'todayAttendance' => $todayAttendance,
            'calendarDays' => $calendarDays,
            'statistics' => $statistics,
            'monthlyAttendances' => $monthlyAttendances,
            'monthName' => $monthName,
        ])->layout('layouts.master');
    }
}
