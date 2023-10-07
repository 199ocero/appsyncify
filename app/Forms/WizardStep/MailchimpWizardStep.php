<?php

namespace App\Forms\WizardStep;

use Filament\Forms;
use App\Models\Token;
use App\Enums\Constant;
use App\Models\Integration;
use App\Forms\Contracts\HasWizardStep;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class MailchimpWizardStep implements HasWizardStep
{
    public function wizardStep(Model $app, int | null $token_id, int $integration_id, array | null $settings, int $step, string $type): Component
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
                        ->label(fn () => $token_id ? 'Connected to ' . $app->name : 'Connect to ' . $app->name)
                        ->url(function () use ($app, $integration_id, $type) {
                            session([
                                'mailchimp_app_id' => $app->id,
                                'integration_id' => $integration_id,
                                'type' => $type
                            ]);
                            return route('auth.' . $app->app_code);
                        })
                        ->icon(fn () => $token_id ? 'heroicon-o-check-badge' : 'heroicon-o-bolt')
                        ->color(fn () => $token_id ? 'gray' : 'primary')
                        ->disabled(fn () => $token_id ? true : false),
                    Forms\Components\Actions\Action::make('disconnect_' . $app->app_code)
                        ->label('Disconnect')
                        ->icon('heroicon-o-bolt-slash')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Disconnect from ' . $app->name)
                        ->modalSubmitActionLabel('Yes, disconnect')
                        ->modalIcon('heroicon-o-bolt-slash')
                        ->action(function () use ($integration_id, $token_id, $type) {
                            $updateDataKey = $type == Constant::FIRST_APP ? 'first_app' : 'second_app';
                            Integration::query()->find($integration_id)->update([
                                "{$updateDataKey}_settings" => null,
                            ]);
                            Token::query()->find($token_id)->delete();
                        })
                        ->hidden($token_id ? false : true)
                ]),
            ]);
    }
}
