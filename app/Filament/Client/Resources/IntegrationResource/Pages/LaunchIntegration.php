<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use Filament\Tables;
use App\Enums\Constant;
use App\Models\SyncLog;
use App\Models\Operation;
use Filament\Tables\Table;
use App\Models\Integration;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Client\Resources\IntegrationResource;

class LaunchIntegration extends Page implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    protected static string $resource = IntegrationResource::class;

    protected static string $view = 'filament.client.resources.integration-resource.pages.launch-integration';

    public Integration $integration;

    protected $schedule;

    public function mount($integration)
    {
        if ($integration) {
            if ($integration->is_finished) {
                $integration->load([
                    'appCombination.firstApp',
                    'appCombination.secondApp',
                    'firstAppToken',
                    'secondAppToken'
                ]);
                $this->integration = $integration;
            } else {
                abort(403);
            }
        } else {
            abort(404);
        }
    }

    public function boot()
    {
        $this->heading = $this->integration->name;
        $this->subheading = $this->integration->description;
        $this->schedule = $this->integration->schedule ? json_decode($this->integration->schedule, true) : null;
    }

    public function table(Table $table): Table
    {
        $operation = Operation::query()->where('actor_id', auth()->user()->id)->where('status', Constant::STATUS_RUNNING)->first();

        $actions = [];

        if (!$this->schedule) {
            $actions = [
                Tables\Actions\Action::make('manual_sync')
                    ->label('Sync Now')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Now')
                    ->modalDescription('This will create a new sync operation.')
                    ->modalSubmitActionLabel('Yes, sync now')
                    ->modalIcon('heroicon-o-arrow-path'),
            ];
        }

        return $table
            ->query(SyncLog::query()->where('operation_id', $operation ? $operation->id : null))
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
            ])
            ->emptyStateIcon('heroicon-o-rocket-launch')
            ->emptyStateHeading('No sync running.')
            ->emptyStateDescription(function () {
                if (!$this->schedule) {
                    return "You've chosen manual syncing. You can initiate the sync now.";
                }
                return 'Your scheduled sync has not started yet.';
            })
            ->emptyStateActions($actions);
    }
}
