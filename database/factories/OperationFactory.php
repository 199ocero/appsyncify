<?php

namespace Database\Factories;

use App\Enums\Actor;
use App\Enums\Operation;
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
        $uuid = Str::uuid();
        $uuidParts = explode('-', $uuid);
        $firstName = $uuidParts[0];

        return [
            'uuid' => $uuid,
            'integration_id' => 1,
            'actor_id' => 1,
            'actor_type' => Actor::USER,
            'name' => Str::upper($firstName),
            'status' => Operation::PENDING,
            'started_at' => now(),
            'ended_at' => now()->addMinutes(5),
        ];
    }
}
