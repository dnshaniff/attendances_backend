<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_departments()
    {
        Department::create([
            'department_name' => 'HR',
            'max_clock_in_time' => '08:00',
            'max_clock_out_time' => '17:00',
        ]);

        Department::create([
            'department_name' => 'IT',
            'max_clock_in_time' => '09:00',
            'max_clock_out_time' => '18:00',
        ]);

        $response = $this->getJson('/api/departments');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_can_create_department()
    {
        $payload = [
            'department_name' => 'IT Support',
            'max_clock_in_time' => '08:00',
            'max_clock_out_time' => '17:00',
        ];

        $response = $this->postJson('/api/departments', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Department created successfully',
                'data' => [
                    'department_name' => 'IT Support',
                    'max_clock_in_time' => '08:00',
                    'max_clock_out_time' => '17:00',
                ],
            ]);

        $this->assertDatabaseHas('departments', ['department_name' => 'IT Support']);
    }

    public function test_create_department_fails_if_name_not_unique()
    {
        Department::create([
            'department_name' => 'Finance',
            'max_clock_in_time' => '09:00',
            'max_clock_out_time' => '18:00',
        ]);

        $response = $this->postJson('/api/departments', [
            'department_name' => 'Finance',
            'max_clock_in_time' => '08:00',
            'max_clock_out_time' => '17:00',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('department_name');
    }

    public function test_can_show_department()
    {
        $department = Department::create([
            'department_name' => 'QA',
            'max_clock_in_time' => '07:00',
            'max_clock_out_time' => '16:00',
        ]);

        $response = $this->getJson("/api/departments/{$department->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $department->id,
                    'department_name' => 'QA',
                ],
            ]);
    }

    public function test_can_update_department()
    {
        $department = Department::create([
            'department_name' => 'Support',
            'max_clock_in_time' => '10:00',
            'max_clock_out_time' => '19:00',
        ]);

        $response = $this->putJson("/api/departments/{$department->id}", [
            'department_name' => 'Technical Support',
            'max_clock_in_time' => '08:30',
            'max_clock_out_time' => '17:30',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Department updated successfully',
                'data' => [
                    'department_name' => 'Technical Support',
                    'max_clock_in_time' => '08:30',
                ],
            ]);

        $this->assertDatabaseHas('departments', ['department_name' => 'Technical Support']);
    }

    public function test_can_delete_department()
    {
        $department = Department::create([
            'department_name' => 'Maintenance',
            'max_clock_in_time' => '06:00',
            'max_clock_out_time' => '15:00',
        ]);

        $response = $this->deleteJson("/api/departments/{$department->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Department deleted successfully']);

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
