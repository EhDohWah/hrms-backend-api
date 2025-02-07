<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $departments = [
            ['name' => 'Admin', 'description' => 'Administrative department'],
            ['name' => 'HR', 'description' => 'Human Resources department'],
            ['name' => 'Data Management', 'description' => 'Data management and analytics'],
            ['name' => 'IT', 'description' => 'Information technology support'],
            ['name' => 'Finance', 'description' => 'Finance and accounting'],
            ['name' => 'Lab', 'description' => 'Laboratory department'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
