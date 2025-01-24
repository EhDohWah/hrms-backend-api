<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EmploymentType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmploymentType>
 */
class EmploymentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = EmploymentType::class;
    
    public function definition(): array
    {
        return [
            //
            'name' => $this->faker->jobTitle,
            'description' => $this->faker->sentence,
        ];
    }
}
