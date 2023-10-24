<?php

namespace App\Forms\WizardStep\Apps;

use App\Models\App;
use Filament\Forms;
use App\Enums\Constant;
use Filament\Forms\Set;
use App\Models\Integration;
use App\Services\SalesforceApi;
use Illuminate\Support\HtmlString;
use App\Settings\SalesforceSettings;
use App\Forms\Contracts\HasWizardStep;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use App\Forms\WizardStep\GeneralWizardStep;
use Illuminate\Validation\ValidationException;

class SalesforceWizardStep implements HasWizardStep
{
    public function wizardStep(App $app, Integration $integration, string $type): Component
    {
        $integrationId = $integration->id;
        $tokenId = getTokenId($type, $integration);
        $settings = getSettings($type, $integration);
        $step = (int)$integration->step;
        $isFinished = $integration->is_finished;

        return Forms\Components\Wizard\Step::make($app->app_code)
            ->label($app->name)
            ->beforeValidation(function () use ($app, $tokenId): void {
                if (!$tokenId) {
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
            ->afterValidation(function ($state) use ($type, $step, $integrationId, $settings): void {
                if ($type == Constant::FIRST_APP && $step == 1 || $type == Constant::SECOND_APP && $step == 2) {
                    Integration::query()->find($integrationId)->update([
                        'step' => (int)$step + 1
                    ]);
                }

                $currentState = [
                    'domain' => $state['domain'],
                    'api_version' => $state['api_version'],
                    'sync_data_type' => $state['sync_data_type']
                ];

                if (count(array_diff_assoc($currentState, $settings)) > 0) {

                    $updateDataKey = $type == Constant::FIRST_APP ? 'first_app' : 'second_app';

                    Integration::query()->find($integrationId)->update([
                        "{$updateDataKey}_settings" => SalesforceSettings::make()
                            ->domain($state['domain'])
                            ->apiVersion($state['api_version'])
                            ->syncDataType($state['sync_data_type'])
                            ->getSettings(),
                    ]);
                }
            })
            ->schema([
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make($app->app_code)
                        ->label(fn () => $tokenId ? 'Connected to ' . $app->name : 'Connect to ' . $app->name)
                        ->url(function () use ($app, $integrationId, $type) {
                            session([
                                'salesforce_app_id' => $app->id,
                                'salesforce_integration_id' => $integrationId,
                                'salesforce_type' => $type
                            ]);
                            return route('auth.' . $app->app_code);
                        })
                        ->badge()
                        ->tooltip('Great! You can connect to ' . $app->name . ' now!')
                        ->icon(fn () => $tokenId ? 'heroicon-o-check-badge' : 'heroicon-o-bolt')
                        ->color(fn () => $tokenId ? 'success' : 'primary')
                        ->disabled(fn () => $tokenId ? true : false),
                    Forms\Components\Actions\Action::make('disconnect' . $app->app_code)
                        ->label('Disconnect')
                        ->icon('heroicon-o-bolt-slash')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Disconnect from ' . $app->name)
                        ->modalDescription('AppSyncify will remove your ' . $app->name . ' access and refresh tokens for this integration, guaranteeing we have no further access to your account.')
                        ->modalSubmitActionLabel('Yes, disconnect')
                        ->modalIcon('heroicon-o-bolt-slash')
                        ->action(function () use ($integrationId, $type, $step, $tokenId) {
                            return GeneralWizardStep::make($integrationId, $type, $step, $tokenId)->disconnectApp();
                        })
                        ->badge()
                        ->tooltip('You can disconnect from ' . $app->name . ' and start over!')
                        ->hidden($tokenId ? false : true)
                ]),
                Forms\Components\Section::make('Salesforce Resource')
                    ->description(new HtmlString('This will be use to get different resources from your ' . $app->name . ' organization. See more information <a href="https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/intro_rest_resources.htm" target="_blank"><span class="font-bold hover:underline" style="color: #FB7185;">here</span></a>.'))
                    ->schema([
                        Forms\Components\TextInput::make('domain')
                            ->label('Salesforce Organization Url')
                            ->prefixIcon('heroicon-o-globe-asia-australia')
                            ->disabled($settings && isset($settings['domain']) ? true : false)
                            ->default($settings && isset($settings['domain']) ? $settings['domain'] : null),
                        Forms\Components\TextInput::make('api_version')
                            ->label('Salesforce Api Version')
                            ->prefixIcon('heroicon-o-code-bracket-square')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('update_api_version')
                                    ->icon('heroicon-o-arrow-path')
                                    ->requiresConfirmation()
                                    ->modalHeading('Update Api Version')
                                    ->modalIcon('heroicon-o-arrow-path')
                                    ->action(function ($state, Set $set) use ($integrationId, $tokenId, $settings, $type) {
                                        if ($tokenId) {
                                            $integration = Integration::query()->with('firstAppToken', 'secondAppToken')->find($integrationId);

                                            $updateDataKey = $type == Constant::FIRST_APP ? 'first_app' : 'second_app';

                                            $apiVersion = SalesforceApi::make(
                                                domain: $settings['domain'],
                                                accessToken: $type == Constant::FIRST_APP ? $integration->firstAppToken->token : $integration->secondAppToken->token,
                                                refreshToken: $type == Constant::FIRST_APP ? $integration->firstAppToken->refresh_token : $integration->secondAppToken->refresh_token
                                            )
                                                ->getApiVersion();

                                            if ($state != $apiVersion) {
                                                $integration->update([
                                                    "{$updateDataKey}_settings" => SalesforceSettings::make()
                                                        ->domain($settings['domain'])
                                                        ->apiVersion($apiVersion)
                                                        ->syncDataType($settings['sync_data_type'])
                                                        ->getSettings(),
                                                ]);

                                                $set('api_version', $apiVersion);

                                                Notification::make()
                                                    ->title('Api Version Updated')
                                                    ->success()
                                                    ->color('success')
                                                    ->send();
                                            } else {

                                                Notification::make()
                                                    ->title('API Version Is Up to Date')
                                                    ->info()
                                                    ->color('info')
                                                    ->send();
                                            }
                                        }
                                    })
                            )
                            ->disabled($settings && isset($settings['api_version']) ? true : false)
                            ->default($settings && isset($settings['api_version']) ? $settings['api_version'] : null)
                    ])
                    ->hidden($tokenId ? false : true)
                    ->columns(2),
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
                    ->hidden($tokenId ? false : true),

            ]);
    }
}
