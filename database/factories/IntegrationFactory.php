<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Integration>
 */
class IntegrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(2),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'user_id' => 1,
            'app_combination_id' => 1,
            'first_app_token_id' => null,
            'second_app_token_id' => null,
            'first_app_settings' => null,
            'second_app_settings' => null,
            'custom_field_mapping' => null,
            'schedule' => null,
            'step' => 1,
            'is_finished' => false
        ];
    }
}
