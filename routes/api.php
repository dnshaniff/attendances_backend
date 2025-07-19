<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::resource('departments', DepartmentController::class)->except('create', 'edit');
Route::resource('employees', EmployeeController::class)->except('create', 'edit');
Route::resource('attendances', AttendanceController::class)->except('create', 'edit', 'update');
Route::put('attendances/{employee}', [AttendanceController::class, 'update'])->name('attendances.update');
