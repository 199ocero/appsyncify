<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use App\Filament\Client\Resources\IntegrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageIntegrations extends ManageRecords
{
    protected static string $resource = IntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Integration')
                ->icon('heroicon-o-plus')
                ->modalHeading('Create New Integration')
                ->modalDescription('Please fill out the form below.')
                ->modalIcon('heroicon-o-arrow-path-rounded-square')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['step'] = 1;
                    return $data;
                }),
        ];
    }
}
