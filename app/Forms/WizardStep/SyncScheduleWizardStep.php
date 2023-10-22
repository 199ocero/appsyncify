<?php

namespace App\Forms\WizardStep;

use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Get;

class SyncScheduleWizardStep
{
    public static function make(): self
    {
        return new self();
    }

    public function schedule(): Component
    {
        return Forms\Components\Wizard\Step::make('schedule')
            ->label('Schedule')
            ->schema([
                Forms\Components\Toggle::make('schedule_enabled')
                    ->label('Enable Data Syncing Schedule')
                    ->live(),
                Forms\Components\Section::make('Schedule')
                    ->description('You have the flexibility to customize the data synchronization schedule to your specific requirements.')
                    ->schema([
                        Forms\Components\Radio::make('is_fixed_time')
                            ->label('Do you want to use a fixed time?')
                            ->helperText("If you select No, then it will sync data every 6 hours by default.")
                            ->boolean()
                            ->required()
                            ->inline(),
                        Forms\Components\Select::make('is_fixed_time_value')
                            ->label('Select a Fixed Time Schedule')
                            ->options([
                                1 => 'Every 1 Hour',
                                2 => 'Every 2 Hours',
                                3 => 'Every 3 Hours',
                                4 => 'Every 4 Hours',
                                5 => 'Every 5 Hours',
                                6 => 'Every 6 Hours'
                            ])
                            ->required(fn (Get $get) => $get('is_fixed_time') == true ? true : false)
                            ->hidden(fn (Get $get) => $get('is_fixed_time') == false ? true : false),
                        Forms\Components\CheckboxList::make('is_everyday_value')
                            ->label('Select a Specific Day')
                            ->helperText('Select the days you want to sync data.')
                            ->options([
                                'monday' => 'Monday',
                                'tuesday' => 'Tuesday',
                                'wednesday' => 'Wednesday',
                                'thursday' => 'Thursday',
                                'friday' => 'Friday',
                                'saturday' => 'Saturday',
                                'sunday' => 'Sunday'
                            ])
                            ->required()
                    ])
                    ->hidden(fn (Get $get) => $get('schedule_enabled') == false ? true : false),
            ]);
    }
}
