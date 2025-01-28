<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class EmployeeController extends Controller
{
    // Get all employees
    // public function index()
    // {
    //     $employees = Employee::with([
    //         'user:id,email',
    //         'employments' => function($query) {
    //             $query->select('id', 'employee_id', 'employment_type_id', 'position_id', 'department_id', 'work_location_id', 'supervisor_id')
    //                 ->with([
    //                     'employmentType:id,name',
    //                     'position:id,title',
    //                     'department:id,name',
    //                     'workLocation:id,name,type',
    //                     'supervisor:id,first_name,last_name'
    //                 ]);
    //         }
    //     ])
    //     ->whereHas('employments')
    //     ->get(['id', 'user_id', 'staff_id', 'first_name', 'middle_name', 'last_name', 'mobile_phone', 'status']); // Select only necessary columns from the Employee table

    //     return response()->json($employees);
    // }

    public function index(Request $request)
    {
        $employees = Employee::with([
                'user:id,email',
                'employments' => function($query) {
                    $query->select('id', 'employee_id', 'employment_type_id', 'position_id', 'department_id', 'work_location_id', 'supervisor_id', 'start_date')
                        ->with([
                            'employmentType:id,name',
                            'position:id,title',
                            'department:id,name',
                            'workLocation:id,name,type',
                            'supervisor:id,first_name,last_name'
                        ]);
                }
            ])
            ->whereHas('employments', function($query) use ($request) {
                // Add department filter
                if ($request->has('department_id')) {
                    $query->where('department_id', $request->department_id);
                }
                
                // Add site (work location) filter
                if ($request->has('site_id')) {
                    $query->where('work_location_id', $request->site_id);
                }
            })
            ->get(['id', 'user_id', 'staff_id', 'first_name', 'middle_name', 'last_name', 'mobile_phone', 'status',]); // Select only necessary columns from the Employee table

        return response()->json($employees);
    }

    // Get a single employee by ID
    public function show($id)
    {
        $employee = Employee::with([
            'user:id,email',
            'employments' => function($query) {
                $query->select('id', 'employee_id', 'employment_type_id', 'position_id', 'department_id', 'work_location_id', 'supervisor_id')
                    ->with([
                        'employmentType:id,name',
                        'position:id,title',
                        'department:id,name',
                        'workLocation:id,name,type',
                        'supervisor:id,first_name,last_name'
                    ]);
            }
        ])
        ->whereHas('employments')
        ->find($id, ['id', 'user_id', 'staff_id']); // Find the employee by ID and select specific columns

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404); // Return a 404 response if the employee is not found
        }

        return response()->json($employee);
    }

    // Get all employees related to a department
    // public function employeesByDepartment($departmentId)
    // {
    //     $employees = Employee::with([
    //         'user:id,email',
    //         'employments' => function($query) {
    //             $query->with([
    //                 'employmentType:id,name',
    //                 'position:id,title',
    //                 'department:id,name',
    //                 'workLocation:id,name,type',
    //                 'supervisor:id,first_name,last_name'
    //             ]);
    //         }
    //     ])
    //     ->whereHas('employments', function($query) use ($departmentId) {
    //         $query->where('department_id', $departmentId);
    //     })
    //     ->get();
    //     return response()->json($employees);
    // }

    // Get all employees related to work location
    // public function employeesByWorkLocation($workLocationId)
    // {
    //     $employees = Employee::with([
    //         'user:id,email',
    //         'employments' => function($query) {
    //             $query->with([
    //                 'employmentType:id,name',
    //                 'position:id,title',
    //                 'department:id,name',
    //                 'workLocation:id,name,type',
    //                 'supervisor:id,first_name,last_name'
    //             ]);
    //         }
    //     ])
    //     ->whereHas('employments', function($query) use ($workLocationId) {
    //         $query->where('work_location_id', $workLocationId);
    //     })
    //     ->get();
    //     return response()->json($employees);
    // }

    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string|max:255',
        ], [
            'search.required' => 'The search field is required.',
            'search.string'   => 'The search field must be a string.',
            'search.max'      => 'The search field must not exceed 255 characters.',
        ]);

        $search = $request->input('search');

        $employees = Employee::select([
                'id', 'user_id', 'staff_id', 
                'first_name', 'middle_name', 
                'last_name', 'mobile_phone', 'status'
            ])
            ->with([
                'user:id,email',
                'employments' => function($query) {
                    $query->select(
                        'id', 'employee_id', 'employment_type_id', 
                        'position_id', 'department_id', 
                        'work_location_id', 'supervisor_id'
                    )
                    ->with([
                        'employmentType:id,name',
                        'position:id,title',
                        'department:id,name',
                        'workLocation:id,name,type',
                        'supervisor:id,first_name,last_name'
                    ]);
                }
            ])
            ->whereHas('employments') // Include only employees with employments
            ->where(function($query) use ($search) {
                $query->where('staff_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            })
            ->get(); // Retrieve all results

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

        return response()->json([
            'message' => 'Employee created successfully',
            'employee' => $employee
        ], 201);
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
