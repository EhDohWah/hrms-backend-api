<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\WorkLocation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkLocation>
 */
class WorkLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => $this->faker->city,
            'type' => $this->faker->randomElement(['Office', 'Remote', 'Hybrid']),        
        ];
    }
}
