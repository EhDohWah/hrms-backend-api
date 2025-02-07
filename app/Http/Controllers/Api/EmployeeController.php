<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\WorkLocation;

/**
 * @OA\Tag(
 *     name="Employees",
 *     description="API Endpoints for Employee management"
 * )
 */
class EmployeeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/employees",
     *     summary="Get list of all employees",
     *     tags={"Employees"},
     *     @OA\Response(
     *         response=200,
     *         description="List of employees",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="staff_id", type="string", example="EMP001"),
     *                 @OA\Property(property="subsidiary", type="string", example="SMRU"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="middle_name", type="string", example="William"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="mobile_phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/employees/{id}",
     *     summary="Get employee details",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Employee ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee details",
     *         @OA\JsonContent(ref="#/components/schemas/Employee")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/employees/search",
     *     summary="Search employees by name or ID",
     *     tags={"Employees"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=true,
     *         description="Search term for name or staff ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Employee")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/employees",
     *     summary="Create a new employee",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"staff_id","first_name","last_name","gender","date_of_birth"},
     *             @OA\Property(property="staff_id", type="string", example="EMP001"),
     *             @OA\Property(property="subsidiary", type="string", example="SMRU"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="middle_name", type="string", example="William"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="gender", type="string", example="male"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="status", type="string", example="active"),
     *             @OA\Property(property="mobile_phone", type="string", example="+1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Employee created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/employees/{id}",
     *     summary="Update employee details",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Employee ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Employee")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/employees/{id}",
     *     summary="Delete an employee",
     *     tags={"Employees"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Employee ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully']);
    }


    // Get Site records
    public function getSiteRecords()
    {
        $sites = WorkLocation::all();
        return response()->json($sites);
    }
}
