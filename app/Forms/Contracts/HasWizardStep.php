<?php

namespace App\Forms\Contracts;

use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Model;

interface HasWizardStep
{
    public function wizardStep(
        Model $app,
        int | null $token_id,
        int $integration_id,
        array | null $settings,
        int $step,
        string $type
    ): Component;
}
