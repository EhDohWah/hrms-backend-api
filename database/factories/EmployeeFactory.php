<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     
    public function definition(): array
    {
        return [
            'staff_id' => $this->faker->unique()->numerify('EMP####'),
            'user_id' => User::factory(), // Create a new user and associate it with the employee
            'first_name' => $this->faker->firstName,
            'middle_name' => $this->faker->optional()->firstName,
            'last_name' => $this->faker->lastName,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'date_of_birth' => $this->faker->date(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'religion' => $this->faker->optional()->word,
            'birth_place' => $this->faker->city,
            'identification_number' => $this->faker->optional()->numerify('ID#######'),
            'passport_number' => $this->faker->optional()->numerify('PASSPORT#####'),
            'bank_name' => $this->faker->optional()->company,
            'bank_branch' => $this->faker->optional()->word,
            'bank_account_name' => $this->faker->optional()->name,
            'bank_account_number' => $this->faker->optional()->numerify('####-####-####'),
            'office_phone' => $this->faker->optional()->phoneNumber,
            'mobile_phone' => $this->faker->optional()->phoneNumber,
            'height' => $this->faker->optional()->randomFloat(2, 1, 2.5),  // height in meters
            'weight' => $this->faker->optional()->randomFloat(2, 30, 100),  // weight in kg
            'permanent_address' => $this->faker->optional()->address,
            'current_address' => $this->faker->optional()->address,
            'stay_with' => $this->faker->optional()->name,
            'military_status' => $this->faker->boolean,
            'marital_status' => $this->faker->optional()->randomElement(['single', 'married', 'divorced']),
            'spouse_name' => $this->faker->optional()->name,
            'spouse_occupation' => $this->faker->optional()->word,
            'father_name' => $this->faker->optional()->name,
            'father_occupation' => $this->faker->optional()->word,
            'mother_name' => $this->faker->optional()->name,
            'mother_occupation' => $this->faker->optional()->word,
            'driver_license_number' => $this->faker->optional()->bothify('DL#####'),
            'created_by' => $this->faker->optional()->name,
            'updated_by' => $this->faker->optional()->name,
        ];
    }
}
