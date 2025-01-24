<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Public routes for test
Route::get('/employees', [EmployeeController::class, 'index']); // Get all employees
Route::get('/employees/department/{departmentId}', [EmployeeController::class, 'employeesByDepartment']); // Get employees by department
Route::get('/employees/site/{workLocationId}', [EmployeeController::class, 'employeesByWorkLocation']); // Get employees by work location



// Routes protected by Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    // Employee Routes
    //Route::get('/employees', [EmployeeController::class, 'index']); // Get all employees
    Route::post('/employees', [EmployeeController::class, 'store']); // Create a new employee
    Route::get('/employees/{id}', [EmployeeController::class, 'show']); // Get a single employee
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']); // Update employee
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']); // Delete employee

    // Employee Filter Routes
    //Route::get('/employees/department/{departmentId}', [EmployeeController::class, 'employeesByDepartment']); // Get employees by department
    //Route::get('/employees/work-location/{workLocationId}', [EmployeeController::class, 'employeesByWorkLocation']); // Get employees by work location


    // Optionally, other authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});