<?php

namespace Database\Factories;

use App\Enums\App;
use App\Enums\Status;
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
            'name' => App::SALESFORCE->label(),
            'description' => $this->faker->sentence(),
            'app_code' => App::SALESFORCE,
            'is_active' => Status::ACTIVE
        ];
    }
}
