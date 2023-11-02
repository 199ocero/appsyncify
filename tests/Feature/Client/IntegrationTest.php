<?php

use App\Filament\Client\Resources\IntegrationResource;
use App\Models\User;

use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('can render the client integration list page', function () {
    get(IntegrationResource::getUrl(name: 'index', panel: 'client'))->assertOk()->assertSuccessful();
});
