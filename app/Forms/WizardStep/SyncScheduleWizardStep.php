<?php

namespace App\Forms\WizardStep;

use Filament\Forms;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;

class SyncScheduleWizardStep
{
    protected $integration;
    protected $schedule;
    public function __construct(Model $integration)
    {
        $this->integration = $integration;
        $this->schedule = json_decode($integration->schedule, true);
    }
    public static function make(Model $integration): self
    {
        return new self($integration);
    }

    public function schedule(): Component
    {
        return Forms\Components\Wizard\Step::make('schedule')
            ->label('Schedule')
            ->schema([
                Forms\Components\Toggle::make('schedule_enabled')
                    ->label('Enable Data Syncing Schedule')
                    ->default($this->schedule && $this->schedule['schedule_enabled'] ?? false)
                    ->live(),
                Forms\Components\Section::make('Schedule')
                    ->description('You have the flexibility to customize the data synchronization schedule to your specific requirements.')
                    ->schema([
                        Forms\Components\Radio::make('is_fixed_time')
                            ->label('Do you want to use a fixed time?')
                            ->helperText("If you select No, then it will sync data every 6 hours by default.")
                            ->validationAttribute('fixed time')
                            ->default($this->schedule && $this->schedule['is_fixed_time'] ?? null)
                            ->boolean()
                            ->required()
                            ->inline(),
                        Forms\Components\Select::make('is_fixed_time_value')
                            ->label('Select a Fixed Time')
                            ->default($this->schedule && ($this->schedule['is_fixed_time_value'] || $this->schedule['is_fixed_time_value'] > 0) ? $this->schedule['is_fixed_time_value'] : null)
                            ->options([
                                1 => 'Every 1 Hour',
                                2 => 'Every 2 Hours',
                                3 => 'Every 3 Hours',
                                4 => 'Every 4 Hours',
                                5 => 'Every 5 Hours'
                            ])
                            ->required(fn (Get $get) => $get('is_fixed_time') == true ? true : false)
                            ->validationAttribute('fixed time value')
                            ->hidden(fn (Get $get) => $get('is_fixed_time') == false ? true : false),
                        Forms\Components\CheckboxList::make('day_value')
                            ->label('Select a Day/s')
                            ->helperText('Select the days you want to sync data.')
                            ->default($this->schedule && !empty($this->schedule['day_value']) ? $this->schedule['day_value'] : null)
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
                            ->validationAttribute('everyday value')
                    ])
                    ->hidden(fn (Get $get) => $get('schedule_enabled') == false ? true : false),
            ]);
    }
}
