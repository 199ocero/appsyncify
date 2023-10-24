<?php

namespace App\Forms\Contracts;

use App\Models\App;
use App\Models\Integration;
use Filament\Forms\Components\Component;

interface HasWizardStep
{
    public function wizardStep(App $app, Integration $integration, string $type): Component;
}
