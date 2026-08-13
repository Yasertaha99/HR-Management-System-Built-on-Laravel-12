<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_checkout_without_checkin(): void
    {
        $user = User::factory()->create(['role_name' => 'Employee']);
        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Attendance\Dashboard::class)
            ->call('finishWorkday')
            ->assertSet('errorMessage', 'You have not checked in for today yet.');
    }

    public function test_employee_can_checkout_and_calculates_actual_duration_and_rounded_hours(): void
    {
        Carbon::setTestNow('2026-08-12 16:31:00');

        $user = User::factory()->create(['role_name' => 'Employee']);

        $checkInTime = Carbon::parse('2026-08-12 08:00:00');

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => '2026-08-12',
            'check_in' => $checkInTime,
            'status' => AttendanceStatus::WORKING,
        ]);

        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Attendance\Dashboard::class)
            ->call('finishWorkday')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_date' => '2026-08-12',
            'status' => AttendanceStatus::COMPLETED->value,
            'total_minutes' => 511, // 8h 31m = 511 minutes
            'rounded_hours' => 9,   // 31m >= 30m -> 9h
        ]);
    }

    public function test_employee_cannot_checkout_twice(): void
    {
        Carbon::setTestNow('2026-08-12 17:00:00');

        $user = User::factory()->create(['role_name' => 'Employee']);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => '2026-08-12',
            'check_in' => Carbon::parse('2026-08-12 08:00:00'),
            'check_out' => Carbon::parse('2026-08-12 16:30:00'),
            'status' => AttendanceStatus::COMPLETED,
            'total_minutes' => 510,
            'rounded_hours' => 9,
        ]);

        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Attendance\Dashboard::class)
            ->call('finishWorkday')
            ->assertSet('errorMessage', 'You have already finished your workday for today.');
    }
}
