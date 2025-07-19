<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DepartmentResource;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);

            $query = Department::query();

            if ($search) {
                $query->where('department_name', 'LIKE', "%{$search}%");
            }

            $departments = $query->latest()->paginate($perPage);

            return DepartmentResource::collection($departments);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|unique:departments,department_name',
            'max_clock_in_time' => 'required|date_format:H:i',
            'max_clock_out_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $department = Department::create($validator->validated());

            DB::commit();

            return response()->json([
                'message' => 'Department created successfully',
                'data' => new DepartmentResource($department)
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $department = Department::findOrFail($id);
            return new DepartmentResource($department);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Department not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $department = Department::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'department_name' => 'required|unique:departments,department_name,' . $department->id,
                'max_clock_in_time' => 'required|date_format:H:i',
                'max_clock_out_time' => 'required|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $department->update($validator->validated());
            DB::commit();

            return response()->json([
                'message' => 'Department updated successfully',
                'data' => new DepartmentResource($department)
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $department = Department::findOrFail($id);
            $department->delete();

            DB::commit();

            return response()->json([
                'message' => 'Department deleted successfully'
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete department',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
