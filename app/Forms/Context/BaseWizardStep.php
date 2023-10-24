<?php

namespace App\Forms\Context;

use App\Forms\Contracts\HasFieldMappingWizardStep;
use App\Forms\Contracts\HasWizardStep;
use App\Models\App;
use App\Models\Integration;
use Illuminate\Database\Eloquent\Model;

class BaseWizardStep
{
    public function wizardStep(HasWizardStep $hasWizardStep, App $app, Integration $integration, string $type)
    {
        return $hasWizardStep->wizardStep($app, $integration, $type);
    }

    public function fieldMappingWizardStep(HasFieldMappingWizardStep $hasFieldMappingWizardStep, Model $integration, string $mappedItems)
    {
        return $hasFieldMappingWizardStep->fieldMappingWizardStep($integration, $mappedItems);
    }
}
