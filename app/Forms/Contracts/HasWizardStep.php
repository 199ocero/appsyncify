<?php

namespace App\Forms\Contracts;

use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Model;

interface HasWizardStep
{
    public function wizardStep(Model $model): Component;
}
