<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Resources\EmployeeResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);

            $query = Employee::with('department');

            if ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('department_name', 'LIKE', "%{$search}%");
                    });
            }

            $employees = $query->latest()->paginate($perPage);

            return EmployeeResource::collection($employees);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve employees',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $employee = Employee::create($validator->validated());

            DB::commit();

            return response()->json([
                'message' => 'Employee created successfully',
                'data' => new EmployeeResource($employee->load('department')),
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create employee',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $employee = Employee::with('department')->findOrFail($id);

            return new EmployeeResource($employee);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Employee not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'department_id' => 'required|exists:departments,id',
                'name' => 'required|string|max:255',
                'address' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $employee->update($validator->validated());
            DB::commit();

            return response()->json([
                'message' => 'Employee updated successfully',
                'data' => new EmployeeResource($employee->load('department')),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update employee',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();

            DB::commit();

            return response()->json([
                'message' => 'Employee deleted successfully',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete employee',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
