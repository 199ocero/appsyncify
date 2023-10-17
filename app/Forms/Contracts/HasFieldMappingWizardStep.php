<?php

namespace App\Forms\Contracts;

use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Model;

interface HasFieldMappingWizardStep
{
    public function fieldMappingWizardStep(Model $integration, string $mappedItems): Component;
}
