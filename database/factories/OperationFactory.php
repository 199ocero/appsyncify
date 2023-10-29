<?php

namespace Database\Factories;

use App\Enums\Constant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operation>
 */
class OperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => 1,
            'actor_type' => Constant::USER,
            'name' => Str::random(10),
            'uuid' => $this->faker->uuid,
            'started_at' => now(),
            'ended_at' => now()->addMinutes(5),
        ];
    }
}
