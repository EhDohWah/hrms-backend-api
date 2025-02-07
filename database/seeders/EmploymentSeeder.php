<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentType;
use App\Models\Position;
use App\Models\WorkLocation;

class EmploymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $employees = Employee::pluck('id')->toArray();
        $employmentTypes = EmploymentType::pluck('id')->toArray();
        $positions = Position::pluck('id')->toArray();
        $departments = Department::pluck('id')->toArray();
        $workLocations = WorkLocation::pluck('id')->toArray();

        Employment::insert([
            [
                'employee_id' => $employees[0] ?? null,
                'employment_type_id' => $employmentTypes[0] ?? null,
                'start_date' => '2023-01-01',
                'probation_end_date' => '2023-07-01',
                'end_date' => null,
                'position_id' => $positions[0] ?? null,
                'department_id' => $departments[0] ?? null,
                'work_location_id' => $workLocations[0] ?? null,
                'position_salary' => 50000,
                'probation_salary' => 45000,
                'supervisor_id' => $employees[1] ?? null,
                'employee_tax' => 5,
                'social_security_id' => 'SS123456',
                'employee_social_security' => 5,
                'employer_social_security' => 10,
                'employee_saving_fund' => 8,
                'employer_saving_fund' => 12,
                'employee_health_insurance' => 3,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'employee_id' => $employees[1] ?? null,
                'employment_type_id' => $employmentTypes[1] ?? null,
                'start_date' => '2022-06-15',
                'probation_end_date' => '2022-12-15',
                'end_date' => null,
                'position_id' => $positions[1] ?? null,
                'department_id' => $departments[1] ?? null,
                'work_location_id' => $workLocations[1] ?? null,
                'position_salary' => 60000,
                'probation_salary' => 55000,
                'supervisor_id' => $employees[2] ?? null,
                'employee_tax' => 6,
                'social_security_id' => 'SS654321',
                'employee_social_security' => 6,
                'employer_social_security' => 12,
                'employee_saving_fund' => 10,
                'employer_saving_fund' => 14,
                'employee_health_insurance' => 4,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ]);
    }
}
