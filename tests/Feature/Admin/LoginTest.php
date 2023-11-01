<?php

use App\Models\AdminUser;
use function Pest\Livewire\livewire;
use function Pest\Laravel\{actingAs, get, post};

beforeEach(function () {
    $this->adminUser = AdminUser::factory()->create();
});

it('can render the admin login page', function () {
    get(route('filament.admin.auth.login'))->assertOk()->assertSuccessful();
});

it('can login as an admin', function () {
    livewire(\Filament\Pages\Auth\Login::class)
        ->fillForm([
            'email' => $this->adminUser->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();
});

it('can validate the admin login', function () {
    livewire(\Filament\Pages\Auth\Login::class)
        ->fillForm([
            'email' => null,
            'password' => null,
        ])
        ->call('authenticate')
        ->assertHasFormErrors([
            'email' => 'required',
            'password' => 'required',
        ]);
});
