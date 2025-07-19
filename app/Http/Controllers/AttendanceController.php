<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\AttendanceHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AttendanceResource;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $departmentId = $request->input('department_id');
            $date = $request->input('date');

            $query = Attendance::with(['employee.department', 'attendanceHistory']);

            if ($search) {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhereHas('department', function ($q) use ($search) {
                            $q->where('department_name', 'LIKE', "%{$search}%");
                        });
                });
            }

            if ($departmentId) {
                $query->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

            if ($date) {
                $query->whereDate('clock_in', $date);
            }

            $attendances = $query->latest()->paginate($perPage);

            return AttendanceResource::collection($attendances);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve attendances',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $employee = Employee::with('department')->findOrFail($request->employee_id);

            $existing = Attendance::where('employee_id', $employee->id)
                ->whereDate('clock_in', now()->toDateString())
                ->first();

            if ($existing) {
                DB::rollBack();
                return response()->json([
                    'message' => 'You have already clocked in today',
                ], 400);
            }

            $now = now();
            $clockInLimit = $now->copy()->setTimeFromTimeString($employee->department->max_clock_in_time);

            if ($now->gt($clockInLimit) && !$request->filled('description')) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Late clock-in requires a description',
                ], 422);
            }

            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'clock_in' => $now,
            ]);

            if ($now->gt($clockInLimit)) {
                AttendanceHistory::create([
                    'employee_id' => $employee->id,
                    'attendance_id' => $attendance->id,
                    'date_attendance' => $now,
                    'attendance_type' => 1,
                    'description' => $request->description,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Clock-in successful',
                'data' => new AttendanceResource($attendance->load('employee.department')),
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to clock in',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $attendance = Attendance::with('employee')->findOrFail($id);

            return response()->json($attendance);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Attendance not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, $employeeId)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $attendance = Attendance::with('employee.department')
                ->where('employee_id', $employeeId)
                ->whereDate('clock_in', now()->toDateString())
                ->first();

            if (!$attendance) {
                DB::rollBack();
                return response()->json([
                    'message' => 'You have not clocked in today',
                ], 404);
            }

            if ($attendance->clock_out !== null) {
                DB::rollBack();
                return response()->json([
                    'message' => 'You have already clocked out today',
                ], 400);
            }

            $now = now();
            $clockOutLimit = $now->copy()->setTimeFromTimeString($attendance->employee->department->max_clock_out_time);

            if ($now->lt($clockOutLimit) && !$request->filled('description')) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Early clock-out requires a description',
                ], 422);
            }

            $alreadyLate = $attendance->attendanceHistory()
                ->where('attendance_type', 1)
                ->exists();

            if ($now->lt($clockOutLimit) && $alreadyLate) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Late employees are not allowed early clock-out',
                ], 403);
            }

            $attendance->update([
                'clock_out' => $now,
            ]);

            if ($now->lt($clockOutLimit)) {
                AttendanceHistory::create([
                    'employee_id' => $attendance->employee_id,
                    'attendance_id' => $attendance->id,
                    'date_attendance' => $now,
                    'attendance_type' => 2,
                    'description' => $request->description,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Clock-out successful',
                'data' => new AttendanceResource($attendance->load('employee.department')),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process clock-out',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            DB::commit();

            return response()->json([
                'message' => 'Attendance deleted successfully',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete attendance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
