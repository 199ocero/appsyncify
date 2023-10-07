<?php

namespace App\Forms\WizardStep;

use App\Forms\Contracts\HasWizardStep;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class MailchimpWizardStep implements HasWizardStep
{
    public function wizardStep(Model $app, int | null $token_id, int $integration_id, array | null $settings, string $type): Component
    {
        return Forms\Components\Wizard\Step::make($app->app_code)
            ->label($app->name)
            ->afterValidation(function () use ($app, $token_id): void {
                if (!$token_id) {
                    Notification::make()
                        ->title('Please connect to ' . $app->name)
                        ->danger()
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->send();

                    throw ValidationException::withMessages([
                        'app' => 'Please connect to ' . $app->name,
                    ]);
                }
            })
            ->schema([
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make($app->app_code)
                        ->label('Connect to ' . $app->name)
                        // ->url(route('auth.' . $model->app_code))
                        ->icon('heroicon-o-bolt')
                ])
            ]);
    }
}
