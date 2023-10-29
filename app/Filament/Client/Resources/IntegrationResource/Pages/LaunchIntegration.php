<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use App\Enums\Constant;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Client\Resources\IntegrationResource;
use App\Models\Operation;
use App\Models\SyncLog;
use Filament\Support\Colors\Color;

class LaunchIntegration extends Page implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    protected static string $resource = IntegrationResource::class;

    protected static string $view = 'filament.client.resources.integration-resource.pages.launch-integration';

    public function table(Table $table): Table
    {
        $operation = Operation::query()->where('actor_id', auth()->user()->id)->where('status', Constant::STATUS_PENDING)->first();

        return $table
            ->query(SyncLog::query()->where('operation_id', $operation->id))
            ->columns([
                Tables\Columns\TextColumn::make('operation_id')
                    ->label('Operation ID')
                    ->placeholder('No Operation ID')
                    ->wrap(),
                Tables\Columns\TextColumn::make('log_type')
                    ->label('Type')
                    ->placeholder('No Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Constant::INFO => 'info',
                        Constant::WARNING => 'warning',
                        Constant::ERROR => 'danger',
                        Constant::DEBUG => 'gray',
                        Constant::AUDIT => 'gray',
                        Constant::REQUEST => 'gray',
                        Constant::RESPONSE => 'gray',
                        Constant::SECURITY => 'gray',
                        Constant::PERFORMANCE => 'gray',
                        Constant::CUSTOM => 'gray',
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->placeholder('No Message')
                    ->wrap(),
                Tables\Columns\TextColumn::make('api_endpoint')
                    ->label('Endpoint')
                    ->placeholder('No Endpoint')
                    ->wrap(),
                Tables\Columns\TextColumn::make('request_data')
                    ->label('Request')
                    ->placeholder('No Request')
                    ->wrap(),
                Tables\Columns\TextColumn::make('response_data')
                    ->label('Response')
                    ->placeholder('No Response')
                    ->wrap(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
