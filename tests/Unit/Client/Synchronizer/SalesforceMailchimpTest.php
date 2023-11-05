<?php

use App\Models\App;
use App\Models\User;
use App\Enums\Status;
use App\Models\Integration;
use Illuminate\Support\Str;
use App\Enums\App as EnumApp;
use function Pest\Faker\fake;
use App\Models\AppCombination;
use App\Services\Context\BaseSynchronizer;
use App\Forms\FieldMapping\DefaultMappedItems;
use App\Services\Combinations\SalesforceMailchimp;

it('can sync salesforce and mailchimp data', function () {

    $user = User::factory()->create();

    $salesforce = App::factory()->create([
        'name' => EnumApp::SALESFORCE->label(),
        'description' => fake()->sentence(),
        'app_code' => getEnumValue(EnumApp::SALESFORCE),
        'is_active' => getEnumValue(Status::ACTIVE)
    ]);

    $mailchimp = App::factory()->create([
        'name' => EnumApp::MAILCHIMP->label(),
        'description' => fake()->sentence(),
        'app_code' => getEnumValue(EnumApp::MAILCHIMP),
        'is_active' => getEnumValue(Status::ACTIVE)
    ]);

    $appCombination = AppCombination::factory()->create([
        'first_app_id' => $salesforce->id,
        'second_app_id' => $mailchimp->id
    ]);

    $integration = Integration::factory()->create([
        'name' => 'My Integration',
        'user_id' => $user->id,
        'app_combination_id' => $appCombination->id,
        'custom_field_mapping' => [
            'key_1' => [
                "direction" =>  "right",
                "first_app_fields" => "Birthdate",
                "second_app_fields" => "BIRTHDAY"
            ],
            'key_2' => [
                "direction" =>  "right",
                "first_app_fields" => "Age",
                "second_app_fields" => "AGE"
            ]
        ]
    ]);

    $defaultMappedItems = $salesforce->app_code . '_' . $mailchimp->app_code;

    $synchronizer = BaseSynchronizer::make()
        ->usingSynchronizer(app(SalesforceMailchimp::class))
        ->withIntegration($integration)
        ->withDefaultFields(DefaultMappedItems::make($defaultMappedItems));

    dd($synchronizer->syncData());
});
