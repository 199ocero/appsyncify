<?php

namespace App\Forms\Contracts;

use Illuminate\Database\Eloquent\Model;

interface HasWizardStep
{
    public function wizardStep(Model $model);
}
