<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use App\Models\EmploymentType;
use App\Models\Position;
use App\Models\Department;
use App\Models\WorkLocation;
use App\Models\Employment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employment>
 */
class EmploymentFactory extends Factory
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
            'employee_id' => Employee::factory(),
            'employment_type_id' => EmploymentType::factory(),
            'start_date' => $this->faker->date,
            'probation_end_date' => $this->faker->date,
            'end_date' => $this->faker->optional()->date,
            'position_id' => Position::factory(),
            'department_id' => Department::factory(),
            'work_location_id' => WorkLocation::factory(),
            'position_salary' => $this->faker->randomFloat(2, 30000, 100000),
            'probation_salary' => $this->faker->randomFloat(2, 20000, 50000),
            'supervisor_id' => Employee::factory(),
            'employee_tax' => $this->faker->randomFloat(2, 100, 500),
            'social_security_id' => $this->faker->uuid,
            'employee_social_security' => $this->faker->randomFloat(2, 50, 300),
            'employer_social_security' => $this->faker->randomFloat(2, 100, 500),
            'employee_saving_fund' => $this->faker->randomFloat(2, 500, 1000),
            'employer_saving_fund' => $this->faker->randomFloat(2, 500, 1500),
            'employee_health_insurance' => $this->faker->randomFloat(2, 200, 800),
            'created_by' => $this->faker->name,
            'updated_by' => $this->faker->name,
        ];
    }
}
