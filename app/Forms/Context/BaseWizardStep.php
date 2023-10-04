<?php

namespace App\Forms\Context;

use App\Forms\Contracts\HasWizardStep;
use Illuminate\Database\Eloquent\Model;

class BaseWizardStep
{
    public function wizardStep(HasWizardStep $hasWizardStep, Model $model)
    {
        return $hasWizardStep->wizardStep($model);
    }
}
