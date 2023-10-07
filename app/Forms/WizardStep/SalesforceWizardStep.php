<?php

namespace App\Forms\WizardStep;

use Filament\Forms;
use App\Models\Token;
use App\Enums\Constant;
use App\Models\Integration;
use Illuminate\Support\HtmlString;
use App\Settings\SalesforceSettings;
use App\Forms\Contracts\HasWizardStep;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class SalesforceWizardStep implements HasWizardStep
{
    public function wizardStep(Model $app, int | null $token_id, int $integration_id, array | null $settings, int $step, string $type): Component
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
            ->afterValidation(function ($state) use ($type, $step, $integration_id): void {
                if ($type == Constant::FIRST_APP && $step == 1 || $type == Constant::SECOND_APP && $step == 2) {
                    Integration::query()->find($integration_id)->update([
                        'step' => (int)$step + 1
                    ]);
                }
                $updateDataKey = $type == Constant::FIRST_APP ? 'first_app' : 'second_app';

                Integration::query()->find(session('integration_id'))->update([
                    "{$updateDataKey}_settings" => SalesforceSettings::make()
                        ->domain($state['domain'])
                        ->syncDataType($state['sync_data_type'])
                        ->getSettings(),
                ]);
            })
            ->schema([
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make($app->app_code)
                        ->label(fn () => $token_id ? 'Connected to ' . $app->name : 'Connect to ' . $app->name)
                        ->url(function () use ($app, $integration_id, $type) {
                            session([
                                'salesforce_app_id' => $app->id,
                                'integration_id' => $integration_id,
                                'type' => $type
                            ]);
                            return route('auth.' . $app->app_code);
                        })
                        ->icon(fn () => $token_id ? 'heroicon-o-check-badge' : 'heroicon-o-bolt')
                        ->color(fn () => $token_id ? 'gray' : 'primary')
                        ->disabled(fn () => $token_id ? true : false),
                    Forms\Components\Actions\Action::make('disconnect' . $app->app_code)
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
                Forms\Components\TextInput::make('domain')
                    ->label('Salesforce URL Resource')
                    ->prefixIcon('heroicon-o-globe-asia-australia')
                    ->disabled($settings && isset($settings['domain']) ? true : false)
                    ->hidden($settings && isset($settings['domain']) ? false : true)
                    ->default($settings && isset($settings['domain']) ? $settings['domain'] : null)
                    ->helperText(new HtmlString('This will be use to get different resources from your ' . $app->name . ' organization. See more information <a href="https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/intro_rest_resources.htm" target="_blank"><span class="font-bold hover:underline" style="color: #FB7185;">here</span></a>.')),
                Forms\Components\Section::make('Sync Data')
                    ->description('Choose the type of data you want to sync.')
                    ->schema([
                        Forms\Components\Radio::make('sync_data_type')
                            ->label('')
                            ->required()
                            ->validationAttribute('sync data type')
                            ->options([
                                'contact' => 'Contact',
                                'lead' => 'Lead',
                                'account' => 'Account'
                            ])
                            ->descriptions([
                                'contact' => 'Sync contacts from ' . $app->name . '.',
                                'lead' => 'Sync leads from ' . $app->name . '.',
                                'account' => 'Sync accounts from ' . $app->name . '.'
                            ])
                            ->disableOptionWhen(fn (string $value): bool => $value === 'lead' || $value === 'account')
                            ->default($settings && isset($settings['sync_data_type']) ? $settings['sync_data_type'] : null)
                            ->inline()
                            ->columns('full')
                    ])
                    ->hidden($token_id ? false : true),

            ]);
    }
}
