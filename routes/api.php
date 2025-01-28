<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\UserController;


// Login route
Route::post('/login', [AuthController::class, 'login']);

// Public routes for test
Route::get('/employees', [EmployeeController::class, 'index']); // Get all employees

//Search employee by name or ID
Route::get('/employees/search', [EmployeeController::class, 'search']);

// Routes protected by Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    // Employee Routes
    //Route::get('/employees', [EmployeeController::class, 'index']); // Get all employees
    Route::post('/employees', [EmployeeController::class, 'store']); // Create a new employee
    Route::get('/employees/{id}', [EmployeeController::class, 'show']); // Get a single employee
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']); // Update employee
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']); // Delete employee

    // Optionally, other authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']); // Get authenticated user details

    // User Routes
    Route::get('/users', [UserController::class, 'index']); // Get all users
    Route::post('/users', [UserController::class, 'store']); // Create a new user
});