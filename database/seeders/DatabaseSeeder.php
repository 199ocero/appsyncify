<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\App;
use App\Enums\Constant;
use App\Models\AppCombination;
use Illuminate\Database\Seeder;

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
    }
}
