<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmploymentType;

class EmploymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $employmentTypes = [
            ['name' => 'Full Time', 'description' => 'Permanent full-time employee', 'created_by' => 1, 'updated_by' => 1],
            ['name' => 'Part Time', 'description' => 'Works less than full-time hours', 'created_by' => 1, 'updated_by' => 1],
            ['name' => 'Contract', 'description' => 'Employed on a contractual basis', 'created_by' => 1, 'updated_by' => 1],
            ['name' => 'Probation', 'description' => 'New employee under probation', 'created_by' => 1, 'updated_by' => 1],
            ['name' => 'Interns', 'description' => 'Internship program for trainees', 'created_by' => 1, 'updated_by' => 1],
        ];

        
        foreach ($employmentTypes as $employmentType) {
            EmploymentType::create($employmentType);
        }
    }
}
