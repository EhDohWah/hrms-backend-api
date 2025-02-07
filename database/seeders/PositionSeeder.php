<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $positions = [
            ['title' => 'Data Entry', 'description' => 'Handles data entry tasks', 'created_by' => 1, 'updated_by' => 1],
            ['title' => 'Medic', 'description' => 'Provides medical support', 'created_by' => 1, 'updated_by' => 1],
            ['title' => 'Midwife', 'description' => 'Assists with childbirth', 'created_by' => 1, 'updated_by' => 1],
            ['title' => 'Health Worker', 'description' => 'Community health worker', 'created_by' => 1, 'updated_by' => 1],
            ['title' => 'HR Assistant', 'description' => 'Supports HR functions', 'created_by' => 1, 'updated_by' => 1],
            ['title' => 'Driver', 'description' => 'Responsible for transportation', 'created_by' => 1, 'updated_by' => 1],
        ];
        
        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
