<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = ['employee_id', 'clock_in', 'clock_out'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function attendanceHistory()
    {
        return $this->hasOne(AttendanceHistory::class, 'attendance_id', 'id');
    }
}
