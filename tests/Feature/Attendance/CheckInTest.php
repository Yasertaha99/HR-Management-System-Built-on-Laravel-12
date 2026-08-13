<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_attendance_dashboard(): void
    {
        $response = $this->get(route('attendance.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_employee_can_check_in(): void
    {
        $user = User::factory()->create([
            'role_name' => 'Employee',
        ]);

        $this->actingAs($user);

        $response = \Livewire\Livewire::test(\App\Livewire\Attendance\Dashboard::class)
            ->call('startAttendance');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_date' => now()->toDateString(),
            'status' => AttendanceStatus::WORKING->value,
        ]);
    }

    public function test_employee_cannot_check_in_twice(): void
    {
        $user = User::factory()->create([
            'role_name' => 'Employee',
        ]);

        $checkInTime = now();

        Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => $checkInTime->toDateString(),
            'check_in' => $checkInTime,
            'status' => AttendanceStatus::WORKING,
        ]);

        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\Attendance\Dashboard::class)
            ->call('startAttendance')
            ->assertSet('errorMessage', 'You have already checked in for today at ' . $checkInTime->format('h:i A') . '.');
    }
}
