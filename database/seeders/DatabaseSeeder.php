<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\App;
use App\Models\User;
use App\Enums\Constant;
use App\Models\SyncLog;
use App\Models\Operation;
use App\Models\Integration;
use App\Models\AppCombination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $firstApp = App::factory()->create([
            'name' => Constant::SALESFORCE,
            'app_code' => Constant::APP_CODE[Constant::SALESFORCE],
            'is_active' => Constant::ACTIVE
        ]);

        $secondApp = App::factory()->create([
            'name' => Constant::MAILCHIMP,
            'app_code' => Constant::APP_CODE[Constant::MAILCHIMP],
            'is_active' => Constant::ACTIVE
        ]);

        AppCombination::factory()->create([
            'first_app_id' => $firstApp->id,
            'second_app_id' => $secondApp->id,
            'is_active' => Constant::ACTIVE
        ]);

        $user = User::factory()->create([
            'name' => fake()->name(),
            'email' => 'jay@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('shutdown199'),
            'remember_token' => null,
        ]);

        $integration = Integration::factory()->create([
            'user_id' => $user->id,
        ]);

        $operation = Operation::factory()->create([
            'integration_id' => $integration->id,
            'actor_id' => $user->id,
        ]);

        SyncLog::factory()->create([
            'operation_id' => $operation->id
        ]);
    }
}
