<?php

namespace App\Forms\Context;

use App\Forms\Contracts\HasWizardStep;
use Illuminate\Database\Eloquent\Model;

class BaseWizardStep
{
    public function wizardStep(HasWizardStep $hasWizardStep, Model $model, int | null $token_id, int $integration_id, string $type)
    {
        return $hasWizardStep->wizardStep($model, $token_id, $integration_id, $type);
    }
}
