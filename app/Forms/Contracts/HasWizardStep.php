<?php

namespace App\Forms\Contracts;

use Filament\Forms\Components\Component;
use Illuminate\Database\Eloquent\Model;

interface HasWizardStep
{
    public function wizardStep(
        Model $app,
        int | null $tokenId,
        int $integrationId,
        array | null $settings,
        int $step,
        string $type
    ): Component;
}
