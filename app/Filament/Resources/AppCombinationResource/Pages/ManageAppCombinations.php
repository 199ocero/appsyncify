<?php

namespace App\Filament\Resources\AppCombinationResource\Pages;

use App\Filament\Resources\AppCombinationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAppCombinations extends ManageRecords
{
    protected static string $resource = AppCombinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New App Combination')
                ->icon('heroicon-o-plus')
                ->modalHeading('Create New App Combination')
                ->modalDescription('Select the apps you want to combine.')
                ->modalIcon('heroicon-o-queue-list'),
        ];
    }
}
