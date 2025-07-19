<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_clock_in_on_time()
    {
        $department = Department::create([
            'department_name' => 'IT',
            'max_clock_in_time' => '08:00:00',
            'max_clock_out_time' => '17:00:00',
        ]);

        $employee = Employee::create([
            'department_id' => $department->id,
            'name' => 'John Doe',
            'address' => 'Jakarta',
        ]);

        Carbon::setTestNow(Carbon::parse('07:55:00'));

        $response = $this->postJson('/api/attendances', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Clock-in successful',
                'data' => [
                    'employee_id' => $employee->id,
                ]
            ]);

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
        ]);
    }

    public function test_cannot_clock_in_late_without_description()
    {
        $department = Department::create([
            'department_name' => 'Finance',
            'max_clock_in_time' => '08:00:00',
            'max_clock_out_time' => '17:00:00',
        ]);

        $employee = Employee::create([
            'department_id' => $department->id,
            'name' => 'Jane Doe',
            'address' => 'Bandung',
        ]);

        Carbon::setTestNow(Carbon::parse('08:15:00'));

        $response = $this->postJson('/api/attendances', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Late clock-in requires a description',
            ]);
    }

    public function test_can_clock_in_late_with_description()
    {
        $department = Department::create([
            'department_name' => 'Support',
            'max_clock_in_time' => '08:00:00',
            'max_clock_out_time' => '17:00:00',
        ]);

        $employee = Employee::create([
            'department_id' => $department->id,
            'name' => 'Rina',
            'address' => 'Surabaya',
        ]);

        Carbon::setTestNow(Carbon::parse('08:30:00'));

        $response = $this->postJson('/api/attendances', [
            'employee_id' => $employee->id,
            'description' => 'Stuck in traffic',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Clock-in successful',
                'data' => [
                    'employee_id' => $employee->id,
                ]
            ]);

        $this->assertDatabaseHas('attendance_histories', [
            'employee_id' => $employee->id,
            'attendance_type' => 1,
            'description' => 'Stuck in traffic',
        ]);
    }
}
