<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function createDepartment(): Department
    {
        return Department::create([
            'department_name' => 'IT Support',
            'max_clock_in_time' => '08:00:00',
            'max_clock_out_time' => '17:00:00',
        ]);
    }

    public function test_can_list_employees()
    {
        $department = $this->createDepartment();

        Employee::create([
            'name' => 'John Doe',
            'address' => 'Jakarta',
            'department_id' => $department->id,
        ]);

        Employee::create([
            'name' => 'Jane Smith',
            'address' => 'Bandung',
            'department_id' => $department->id,
        ]);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_can_create_employee()
    {
        $department = $this->createDepartment();

        $payload = [
            'name' => 'Alice',
            'address' => 'Surabaya',
            'department_id' => $department->id,
        ];

        $response = $this->postJson('/api/employees', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Employee created successfully',
                'data' => [
                    'name' => 'Alice',
                    'address' => 'Surabaya',
                    'department_id' => $department->id,
                ],
            ]);

        $this->assertDatabaseHas('employees', ['name' => 'Alice']);
    }

    public function test_create_employee_fails_if_department_invalid()
    {
        $response = $this->postJson('/api/employees', [
            'name' => 'Invalid Dept',
            'address' => 'Somewhere',
            'department_id' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('department_id');
    }

    public function test_can_show_employee()
    {
        $department = $this->createDepartment();

        $employee = Employee::create([
            'name' => 'Rudi Hartono',
            'address' => 'Jogja',
            'department_id' => $department->id,
        ]);

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $employee->id,
                    'name' => 'Rudi Hartono',
                ],
            ]);
    }

    public function test_can_update_employee()
    {
        $department = $this->createDepartment();

        $employee = Employee::create([
            'name' => 'Dina',
            'address' => 'Medan',
            'department_id' => $department->id,
        ]);

        $newDept = Department::create([
            'department_name' => 'HRD',
            'max_clock_in_time' => '09:00:00',
            'max_clock_out_time' => '18:00:00',
        ]);

        $payload = [
            'name' => 'Dina Update',
            'address' => 'Padang',
            'department_id' => $newDept->id,
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Employee updated successfully',
                'data' => [
                    'name' => 'Dina Update',
                    'department_id' => $newDept->id,
                ],
            ]);

        $this->assertDatabaseHas('employees', ['name' => 'Dina Update']);
    }

    public function test_can_delete_employee()
    {
        $department = $this->createDepartment();

        $employee = Employee::create([
            'name' => 'Budi',
            'address' => 'Semarang',
            'department_id' => $department->id,
        ]);

        $response = $this->deleteJson("/api/employees/{$employee->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Employee deleted successfully']);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
