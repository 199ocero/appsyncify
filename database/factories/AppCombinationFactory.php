<?php

namespace Database\Factories;

use App\Enums\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppCombination>
 */
class AppCombinationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $features = [
            [
                'feature' => $this->faker->sentence(),
            ],
            [
                'feature' => $this->faker->sentence(),
            ],
            [
                'feature' => $this->faker->sentence(),
            ],
            [
                'feature' => $this->faker->sentence(),
            ]
        ];

        return [
            'first_app_id' => 1,
            'second_app_id' => 2,
            'is_active' => Constant::ACTIVE,
            'features' => $features
        ];
    }
}
