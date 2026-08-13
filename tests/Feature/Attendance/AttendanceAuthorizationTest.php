<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_employee_cannot_access_workforce_manager(): void
    {
        $employee = User::factory()->create(['role_name' => 'Employee']);
        $this->actingAs($employee);

        $response = $this->get(route('attendance.manage'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_workforce_manager(): void
    {
        $admin = User::factory()->create(['role_name' => 'Admin']);
        $this->actingAs($admin);

        $response = $this->get(route('attendance.manage'));
        $response->assertStatus(200);
    }

    public function test_manager_can_access_workforce_manager(): void
    {
        $manager = User::factory()->create(['role_name' => 'Manager']);
        $this->actingAs($manager);

        $response = $this->get(route('attendance.manage'));
        $response->assertStatus(200);
    }
}
