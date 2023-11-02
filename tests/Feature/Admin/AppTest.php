<?php

use App\Filament\Resources\AppResource;
use App\Models\AdminUser;
use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    actingAs(AdminUser::factory()->create());
});

it('can render the admin app list page', function () {
    $response = get(AppResource::getUrl(name: 'index', panel: 'admin'));
    $response = $this->followRedirects($response);
    $response->assertOk()->assertSuccessful();
});
