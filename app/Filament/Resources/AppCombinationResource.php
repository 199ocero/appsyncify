<?php

namespace App\Filament\Resources;

use Closure;
use App\Models\App;
use Filament\Forms;
use Filament\Tables;
use App\Enums\Constant;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\AppCombination;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AppCombinationResource\Pages;
use App\Filament\Resources\AppCombinationResource\RelationManagers;

class AppCombinationResource extends Resource
{
    protected static ?string $model = AppCombination::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $activeNavigationIcon = 'heroicon-s-queue-list';

    protected static ?string $navigationLabel = 'Combinations';

    protected static ?string $label = 'Combinations';

    protected static ?string $slug = 'combinations';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('first_app_id')
                    ->label('Select First App')
                    ->required()
                    ->options(App::all()->pluck('name', 'id'))
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {

                            if ((int)$get('second_app_id') === (int)$value) {
                                $fail("The first and second apps cannot be the same.");
                            }
                        },
                        fn (Get $get, string $operation): Closure => function (string $attribute, $value, Closure $fail) use ($get, $operation) {

                            if ($operation === 'edit') {
                                return;
                            }

                            $combination = AppCombination::where(function ($query) use ($value, $get) {
                                $query->where('first_app_id', $value)
                                    ->where('second_app_id', $get('second_app_id'));
                            })->orWhere(function ($query) use ($value, $get) {
                                $query->where('first_app_id', $get('second_app_id'))
                                    ->where('second_app_id', $value);
                            })->first();

                            if ($combination) {
                                $fail("This combination already exists.");
                            }
                        },
                    ]),
                Forms\Components\Select::make('second_app_id')
                    ->label('Select Second App')
                    ->required()
                    ->options(App::all()->pluck('name', 'id')),
                Forms\Components\Toggle::make('is_active')
                    ->label('Make this app combination active?')
                    ->required()
                    ->default(Constant::ACTIVE),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('firstApp.name')
                    ->label('First App')
                    ->placeholder('No app selected.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('secondApp.name')
                    ->label('Second App')
                    ->placeholder('No app selected.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil-square')
                        ->modalHeading('Edit App Combination')
                        ->modalDescription('Select the apps you want to edit.')
                        ->modalIcon('heroicon-o-queue-list'),
                    Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageAppCombinations::route('/'),
        ];
    }
}
