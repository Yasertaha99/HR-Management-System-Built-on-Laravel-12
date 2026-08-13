<?php

namespace Tests\Feature\Payroll;

use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_employee_cannot_access_payroll_dashboard(): void
    {
        $employee = User::factory()->create(['role_name' => 'Employee']);
        $this->actingAs($employee);

        $response = $this->get(route('payroll.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_and_hr_can_access_payroll_dashboard(): void
    {
        $admin = User::factory()->create(['role_name' => 'Admin']);
        $this->actingAs($admin);
        $this->get(route('payroll.dashboard'))->assertStatus(200);

        $hr = User::factory()->create(['role_name' => 'HR']);
        $this->actingAs($hr);
        $this->get(route('payroll.dashboard'))->assertStatus(200);
    }

    public function test_employee_can_access_my_payslips(): void
    {
        $employee = User::factory()->create(['role_name' => 'Employee']);
        $this->actingAs($employee);

        $response = $this->get(route('payroll.payslips'));
        $response->assertStatus(200);
    }
}
