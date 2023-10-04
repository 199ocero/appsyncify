<?php

namespace App\Forms\WizardStep;

use App\Forms\Contracts\HasWizardStep;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class SalesforceWizardStep implements HasWizardStep
{
    public function wizardStep(Model $model)
    {
        return Forms\Components\Wizard\Step::make($model->app_code)
            ->label($model->name)
            ->afterValidation(function () use ($model): void {
                if (!$model->first_app_token_id) {
                    Notification::make()
                        ->title('Please connect to ' . $model->name)
                        ->danger()
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->send();

                    throw ValidationException::withMessages([
                        'first_app_token_id' => 'Please connect to ' . $model->name,
                    ]);
                }
            })
            ->schema([
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make($model->app_code)
                        ->label('Connect to ' . $model->name)
                        ->url(route('auth.' . $model->app_code))
                        ->icon('heroicon-o-bolt')
                ])
            ]);
    }
}
