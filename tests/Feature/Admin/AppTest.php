<?php

use App\Models\App;
use App\Enums\Constant;
use App\Models\AdminUser;
use function Pest\Livewire\livewire;

use App\Filament\Resources\AppResource;
use function Pest\Laravel\{actingAs, get};
use App\Filament\Resources\AppResource\Pages\ManageApps;

beforeEach(function () {
    actingAs(AdminUser::factory()->create());
    $this->appSalesforce = App::factory()->create();
});

it('can render the admin app list page', function () {
    $response = get(AppResource::getUrl(name: 'index', panel: 'admin'));
    $response = $this->followRedirects($response);
    $response->assertOk()->assertSuccessful();
});

it('can list apps', function () {
    livewire(ManageApps::class)
        ->assertCanSeeTableRecords(collect([$this->appSalesforce]))
        ->assertCanRenderTableColumn('name', 'app_code', 'description', 'is_active');
});

it('can sort apps', function () {
    livewire(ManageApps::class)
        ->assertCanSeeTableRecords(collect([$this->appSalesforce]))
        ->sortTable('name')
        ->sortTable('app_code');
});

it('can search apps', function () {
    livewire(ManageApps::class)
        ->assertCanSeeTableRecords(collect([$this->appSalesforce]))
        ->searchTableColumns([
            'name' => $this->appSalesforce->name,
            'app_code' => $this->appSalesforce->app_code,
            'description' => $this->appSalesforce->description,
        ]);
});
