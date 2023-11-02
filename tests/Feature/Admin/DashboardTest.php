<?php

use App\Models\AdminUser;
use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    actingAs(AdminUser::factory()->create());
});

it('can render the admin dashboard', function () {
    $response = get(route('filament.admin.pages.dashboard'));
    $response->assertStatus(302);
    $response = $this->followRedirects($response);
    $response->assertStatus(200);
});
