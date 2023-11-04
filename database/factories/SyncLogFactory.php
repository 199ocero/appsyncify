<?php

namespace Database\Factories;

use App\Enums\Log;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SyncLog>
 */
class SyncLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'operation_id' => 1,
            'log_type' => Log::INFO,
            'message' => 'Salesforce to Mailchimp Sync',
            'api_endpoint' => 'https://api.salesforce.com',
            'request_data' => json_encode([
                'api_endpoint' => 'https://api.salesforce.com',
                'data' => [
                    'john@example.com',
                    'jenny@example.com'
                ]
            ]),
            'response_data' => null
        ];
    }
}
