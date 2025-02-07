<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GrantController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

// Authentication Routes | add prefix auth
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected Authentication Routes
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Protected Routes
Route::middleware(['auth:api'])->group(function () {
    // Role Management Routes
    Route::middleware(['permission:roles.read'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::get('/roles/{id}/permissions', [RoleController::class, 'getPermissions']);
    });

    Route::middleware(['permission:roles.write'])->group(function () {
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{id}', [RoleController::class, 'update']);
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
        Route::put('/roles/{id}/permissions', [RoleController::class, 'updatePermissions']);
    });

    // Permission Management Routes
    Route::middleware(['permission:permissions.read'])->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/{id}', [PermissionController::class, 'show']);
        Route::get('/permissions/modules', [PermissionController::class, 'getModules']);
    });

    Route::middleware(['permission:permissions.write'])->group(function () {
        Route::post('/permissions', [PermissionController::class, 'store']);
        Route::put('/permissions/{id}', [PermissionController::class, 'update']);
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
    });

    // User-Role Management Routes
    Route::middleware(['permission:users.read'])->group(function () {
        Route::get('/users/{id}/roles', [UserController::class, 'getRoles']);
    });

    Route::middleware(['permission:users.write'])->group(function () {
        Route::put('/users/{id}/roles', [UserController::class, 'updateRoles']);
    });

    // Employee Routes
    Route::middleware(['permission:employees.create'])->post('/employees', [EmployeeController::class, 'store']);
    Route::middleware(['permission:employees.read'])->get('/employees/{id}', [EmployeeController::class, 'show']);
    Route::middleware(['permission:employees.write'])->put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::middleware(['permission:employees.delete'])->delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::middleware(['permission:employees.read'])->get('/employees', [EmployeeController::class, 'index']);
    Route::middleware(['permission:employees.read'])->get('/employees/sites', [EmployeeController::class, 'getSiteRecords']);
    Route::middleware(['permission:employees.read'])->get('/employees/search', [EmployeeController::class, 'search']);

    // Grant Routes
    Route::middleware(['permission:grants.read'])->get('/grants', [GrantController::class, 'index']);
    Route::middleware(['permission:grants.write'])->post('/grants/upload', [GrantController::class, 'upload']);

    // User Routes
    Route::middleware(['permission:users.read'])->get('/users', [UserController::class, 'index']);
    Route::middleware(['permission:users.write'])->post('/users', [UserController::class, 'store']);
});
