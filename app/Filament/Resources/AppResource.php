<?php

namespace App\Filament\Resources;

use App\Models\App;
use Filament\Forms;
use Filament\Tables;
use App\Enums\Status;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AppResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AppResource\RelationManagers;

class AppResource extends Resource
{
    protected static ?string $model = App::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $activeNavigationIcon = 'heroicon-s-bolt';

    protected static ?string $navigationLabel = 'Apps';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->placeholder('e.g Salesforce')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->string(),
                Forms\Components\TextInput::make('app_code')
                    ->label('App Code')
                    ->placeholder('e.g salesforce')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->string(),
                Forms\Components\Textarea::make('description')
                    ->placeholder('e.g This is the description of the app')
                    ->required()
                    ->string(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Make this app active?')
                    ->required()
                    ->default(Status::ACTIVE),
            ])
            ->columns('full');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('app_code')
                    ->label('App Code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil-square')
                        ->modalHeading('Edit App')
                        ->modalDescription('Provide the details of the app.')
                        ->modalIcon('heroicon-o-pencil-square'),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading(fn (Model $record): string => __('filament-actions::delete.single.modal.heading', ['label' => $record->name])),
                ])
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
            'index' => Pages\ManageApps::route('/'),
        ];
    }
}
