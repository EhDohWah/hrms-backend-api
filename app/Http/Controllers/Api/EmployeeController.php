<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    //
    // Get all employees
    public function index()
    {
        $employees = Employee::with([
            'user:id,email',
            'employments' => function($query) {
                $query->with([
                    'employmentType:id,name',
                    'position:id,title',
                    'department:id,name',
                    'workLocation:id,name,type',
                    'supervisor:id,first_name,last_name'
                ]);
            }
        ])
        ->whereHas('employments')
        ->get();
        return response()->json($employees);
    }

    // Get all employees related to a department
    public function employeesByDepartment($departmentId)
    {
        $employees = Employee::with([
            'user:id,email',
            'employments' => function($query) {
                $query->with([
                    'employmentType:id,name',
                    'position:id,title',
                    'department:id,name',
                    'workLocation:id,name,type',
                    'supervisor:id,first_name,last_name'
                ]);
            }
        ])
        ->whereHas('employments', function($query) use ($departmentId) {
            $query->where('department_id', $departmentId);
        })
        ->get();
        return response()->json($employees);
    }

    // Get all employees related to work location
    public function employeesByWorkLocation($workLocationId)
    {
        $employees = Employee::with([
            'user:id,email',
            'employments' => function($query) {
                $query->with([
                    'employmentType:id,name',
                    'position:id,title',
                    'department:id,name',
                    'workLocation:id,name,type',
                    'supervisor:id,first_name,last_name'
                ]);
            }
        ])
        ->whereHas('employments', function($query) use ($workLocationId) {
            $query->where('work_location_id', $workLocationId);
        })
        ->get();
        return response()->json($employees);
    }

    // Store a new employee
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|string|max:50|unique:employees',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string|max:10',
            'date_of_birth' => 'required|date',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'religion' => 'nullable|string|max:100',
            'birth_place' => 'nullable|string|max:100',
            'identification_number' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'office_phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'height' => 'nullable|numeric|between:0,999.99',
            'weight' => 'nullable|numeric|between:0,999.99',
            'permanent_address' => 'nullable|string',
            'current_address' => 'nullable|string',
            'stay_with' => 'nullable|string|max:100',
            'military_status' => 'boolean',
            'marital_status' => 'nullable|string|max:20',
            'spouse_name' => 'nullable|string|max:100',
            'spouse_occupation' => 'nullable|string|max:100',
            'father_name' => 'nullable|string|max:100',
            'father_occupation' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:100',
            'mother_occupation' => 'nullable|string|max:100',
            'driver_license_number' => 'nullable|string|max:50',
            'created_by' => 'nullable|string|max:255',
            'updated_by' => 'nullable|string|max:255',
        ]);

        $employee = Employee::create($validated);
        return response()->json($employee, 201);
    }

    // Show a single employee
    public function show($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        return response()->json($employee);
    }

    // Update an employee
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $validated = $request->validate([
            'staff_id' => "required|string|max:50|unique:employees,staff_id,{$id}",
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|string|max:10',
            'date_of_birth' => 'required|date',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'religion' => 'nullable|string|max:100',
            'birth_place' => 'nullable|string|max:100',
            'identification_number' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'office_phone' => 'nullable|string|max:20',
            'mobile_phone' => 'nullable|string|max:20',
            'height' => 'nullable|numeric|between:0,999.99',
            'weight' => 'nullable|numeric|between:0,999.99',
            'permanent_address' => 'nullable|string',
            'current_address' => 'nullable|string',
            'stay_with' => 'nullable|string|max:100',
            'military_status' => 'boolean',
            'marital_status' => 'nullable|string|max:20',
            'spouse_name' => 'nullable|string|max:100',
            'spouse_occupation' => 'nullable|string|max:100',
            'father_name' => 'nullable|string|max:100',
            'father_occupation' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:100',
            'mother_occupation' => 'nullable|string|max:100',
            'driver_license_number' => 'nullable|string|max:50',
            'created_by' => 'nullable|string|max:255',
            'updated_by' => 'nullable|string|max:255',
        ]);

        $employee->update($validated);
        return response()->json($employee);
    }

    // Delete an employee
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
