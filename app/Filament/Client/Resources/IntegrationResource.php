<?php

namespace App\Filament\Client\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\Constant;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Integration;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Client\Resources\IntegrationResource\Pages;
use App\Filament\Client\Resources\IntegrationResource\RelationManagers;

class IntegrationResource extends Resource
{
    protected static ?string $model = Integration::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $activeNavigationIcon = 'heroicon-s-arrow-path-rounded-square';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->placeholder('e.g My Integration')
                    ->required()
                    ->string(),
                Forms\Components\Select::make('app_combination_id')
                    ->label('Select Integration')
                    ->required()
                    ->relationship(
                        name: 'appCombination',
                        modifyQueryUsing: fn (Builder $query) => $query->with('firstApp', 'secondApp'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->firstApp->name} - {$record->secondApp->name}"),
                Forms\Components\Textarea::make('description')
                    ->placeholder('e.g This is the description of integration')
                    ->required()
                    ->string(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Make this integration active?')
                    ->required()
                    ->default(Constant::ACTIVE),
            ])
            ->columns('full');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->wrap(),
                Tables\Columns\TextColumn::make('appCombination.firstApp.name')
                    ->label('First App')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appCombination.secondApp.name')
                    ->label('Second App')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Edit Integration')
                        ->modalDescription('Please edit the form below.')
                        ->modalIcon('heroicon-o-arrow-path-rounded-square'),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading(fn (Model $record): string => __('filament-actions::delete.single.modal.heading', ['label' => $record->appCombination->firstApp->name . ' - ' .  $record->appCombination->secondApp->name . ' Integration'])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageIntegrations::route('/'),
        ];
    }
}
