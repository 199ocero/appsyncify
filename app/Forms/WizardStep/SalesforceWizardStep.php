<?php

namespace App\Forms\WizardStep;

use Filament\Forms;
use Illuminate\Support\HtmlString;
use App\Forms\Contracts\HasWizardStep;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class SalesforceWizardStep implements HasWizardStep
{
    public function wizardStep(Model $app, int | null $token_id, int $integration_id, string $type): Component
    {
        return Forms\Components\Wizard\Step::make($app->app_code)
            ->label($app->name)
            ->beforeValidation(function () use ($app, $token_id): void {
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
                        ->label(function () use ($token_id, $app) {
                            if ($token_id) {
                                return 'Connected to ' . $app->name;
                            }
                            return 'Connect to ' . $app->name;
                        })
                        ->url(function () use ($app, $integration_id, $type) {
                            session([
                                'salesforce_app_id' => $app->id,
                                'integration_id' => $integration_id,
                                'type' => $type
                            ]);
                            return route('auth.' . $app->app_code);
                        })
                        ->icon('heroicon-o-bolt')
                        ->color(function () use ($token_id) {
                            if ($token_id) {
                                return 'primary';
                            }
                            return 'gray';
                        })
                        ->disabled(function () use ($token_id) {
                            if ($token_id) {
                                return true;
                            }
                            return false;
                        })
                ]),
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->placeholder('e.g MyDomainName')
                    ->prefix('https://')
                    ->suffix('my.salesforce.com')
                    ->helperText(new HtmlString('This will be use to connect to your ' . $app->name . ' organization. See more information <a href="https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/intro_rest_resources.htm" target="_blank"><span class="font-bold hover:underline" style="color: #FB7185;">here</span></a>.'))
            ]);
    }
}
