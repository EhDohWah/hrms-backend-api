<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkLocation;

class WorkLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $worklocations = [
            ['name' => 'MKT', 'type' => 'MKT Site Location'],
            ['name' => 'WPA', 'type' => 'WPA Site Location'],
            ['name' => 'MSL', 'type' => 'MSL Site Location'],
            ['name' => 'MRM', 'type' => 'MRM Site Location'],
            ['name' => 'MRMTB', 'type' => 'MRMTB Site Location'],
            ['name' => 'KKTB', 'type' => 'KKTB Site Location'],
        ];

        foreach ($worklocations as $worklocation) {
            WorkLocation::create($worklocation);
        }
    }
}
