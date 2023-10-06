<?php

namespace Database\Factories;

use App\Enums\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\App>
 */
class AppFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Constant::SALESFORCE,
            'description' => $this->faker->sentence(),
            'app_code' => Constant::APP_CODE[Constant::SALESFORCE],
            'is_active' => Constant::ACTIVE
        ];
    }
}
