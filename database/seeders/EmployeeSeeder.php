<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $employees = [
            [
                'staff_id' => 'EMP001',
                'subsidiary' => 'SMRU',
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Doe',
                'gender' => 'Male',
                'date_of_birth' => '1990-05-15',
                'status' => 'Active',
                'religion' => 'Christianity',
                'birth_place' => 'New York',
                'identification_number' => '123456789',
                'passport_number' => 'A1234567',
                'bank_name' => 'Bank of America',
                'bank_branch' => 'New York Branch',
                'bank_account_name' => 'John M. Doe',
                'bank_account_number' => '1234567890',
                'office_phone' => '123-456-7890',
                'mobile_phone' => '987-654-3210',
                'height' => '175',
                'weight' => '70',
                'permanent_address' => '123 Main Street, New York, NY',
                'current_address' => '456 Elm Street, Los Angeles, CA',
                'stay_with' => 'Alone',
                'military_status' => 0,
                'marital_status' => 1,
                'spouse_name' => 'Jane Doe',
                'spouse_occupation' => 'Teacher',
                'father_name' => 'Robert Doe',
                'father_occupation' => 'Engineer',
                'mother_name' => 'Mary Doe',
                'mother_occupation' => 'Nurse',
                'driver_license_number' => 'D123456789',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'staff_id' => 'EMP002',
                'subsidiary' => 'BHF',
                'first_name' => 'Alice',
                'middle_name' => 'Marie',
                'last_name' => 'Smith',
                'gender' => 'Female',
                'date_of_birth' => '1988-08-20',
                'status' => 'Active',
                'religion' => 'Buddhism',
                'birth_place' => 'Los Angeles',
                'identification_number' => '987654321',
                'passport_number' => 'B7654321',
                'bank_name' => 'Wells Fargo',
                'bank_branch' => 'Los Angeles Branch',
                'bank_account_name' => 'Alice M. Smith',
                'bank_account_number' => '0987654321',
                'office_phone' => '456-789-0123',
                'mobile_phone' => '321-654-0987',
                'height' => '165',
                'weight' => '55',
                'permanent_address' => '789 Pine Street, Chicago, IL',
                'current_address' => '101 Maple Street, San Francisco, CA',
                'stay_with' => 'Family',
                'military_status' => 1,
                'marital_status' => 0,
                'spouse_name' => null,
                'spouse_occupation' => null,
                'father_name' => 'James Smith',
                'father_occupation' => 'Doctor',
                'mother_name' => 'Linda Smith',
                'mother_occupation' => 'Lawyer',
                'driver_license_number' => 'D987654321',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'staff_id' => 'EMP003',
                'subsidiary' => 'SMRU',
                'first_name' => 'Michael',
                'middle_name' => 'James',
                'last_name' => 'Brown',
                'gender' => 'Male',
                'date_of_birth' => '1985-07-10',
                'status' => 'Active',
                'religion' => 'Islam',
                'birth_place' => 'Houston',
                'identification_number' => '112233445',
                'passport_number' => 'C3344556',
                'bank_name' => 'Chase Bank',
                'bank_branch' => 'Houston Branch',
                'bank_account_name' => 'Michael J. Brown',
                'bank_account_number' => '3344556677',
                'office_phone' => '789-012-3456',
                'mobile_phone' => '210-987-6543',
                'height' => '180',
                'weight' => '80',
                'permanent_address' => '222 Oak Street, Houston, TX',
                'current_address' => '333 Cedar Street, Dallas, TX',
                'stay_with' => 'Roommate',
                'military_status' => 1,
                'marital_status' => 1,
                'spouse_name' => 'Sarah Brown',
                'spouse_occupation' => 'Nurse',
                'father_name' => 'George Brown',
                'father_occupation' => 'Architect',
                'mother_name' => 'Martha Brown',
                'mother_occupation' => 'Professor',
                'driver_license_number' => 'D334455667',
                'created_by' => 1,
                'updated_by' => 1,
            ]
        ];

        Employee::insert($employees);
    }
}
