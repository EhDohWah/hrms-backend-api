<?php

namespace Database\Seeders;


use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmploymentType;
use App\Models\Position;
use App\Models\Department;
use App\Models\WorkLocation;
use App\Models\Employment;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DepartmentSeeder::class,
            WorkLocationSeeder::class,
            EmployeeSeeder::class,
            EmploymentTypeSeeder::class,
            PositionSeeder::class,
            EmploymentSeeder::class,
        ]);


        // User::factory(10)->create();
        // Employee::factory(10)->create();
        // EmploymentType::factory(3)->create();
        // Position::factory(5)->create();
        // Department::factory(4)->create();
        // WorkLocation::factory(2)->create();
        // Employment::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
