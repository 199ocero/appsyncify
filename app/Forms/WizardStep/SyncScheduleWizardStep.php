<?php

namespace App\Forms\WizardStep;

use Filament\Forms;
use Filament\Forms\Components\Component;

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
            ->schema([]);
    }
}
