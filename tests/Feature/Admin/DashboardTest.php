<?php

use App\Models\AdminUser;
use Filament\Pages\Dashboard;

use function Pest\Laravel\{actingAs, get};

beforeEach(function () {
    actingAs(AdminUser::factory()->create());
});

it('can render the admin dashboard', function () {
    $response = get(Dashboard::getUrl(panel: 'admin'));
    $response = $this->followRedirects($response);
    $response->assertOk()->assertSuccessful();
});
