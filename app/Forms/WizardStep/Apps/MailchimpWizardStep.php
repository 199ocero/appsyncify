<?php

namespace App\Forms\WizardStep\Apps;

use App\Enums\AppType;
use Filament\Forms;
use App\Models\Integration;
use App\Settings\MailchimpSettings;
use App\Forms\Contracts\HasWizardStep;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use App\Forms\WizardStep\GeneralWizardStep;
use App\Models\App;
use App\Models\Token;
use App\Services\MailchimpApi;
use Illuminate\Validation\ValidationException;

class MailchimpWizardStep implements HasWizardStep
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
            ->disabled($isFinished)
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
                if ($type == getEnumValue(AppType::FIRST_APP) && $step == 1 || $type == getEnumValue(AppType::SECOND_APP) && $step == 2) {
                    Integration::query()->find($integrationId)->update([
                        'step' => (int)$step + 1
                    ]);
                }

                $currentState = [
                    'region' => $state['region'],
                    'audience_id' => $state['audience_id']
                ];

                if (count(array_diff_assoc($currentState, $settings)) > 0) {
                    $updateDataKey = $type == getEnumValue(AppType::FIRST_APP) ? 'first_app' : 'second_app';

                    Integration::query()->find($integrationId)->update([
                        "{$updateDataKey}_settings" => MailchimpSettings::make()
                            ->region($state['region'])
                            ->audienceId($state['audience_id'])
                            ->getSettings()
                    ]);
                }
            })
            ->schema([
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make($app->app_code)
                        ->label(fn () => $tokenId ? 'Connected to ' . $app->name : 'Connect to ' . $app->name)
                        ->url(function () use ($app, $integrationId, $type) {
                            session([
                                'mailchimp_app_id' => $app->id,
                                'mailchimp_integration_id' => $integrationId,
                                'mailchimp_type' => $type
                            ]);
                            return route('auth.' . $app->app_code);
                        })
                        ->badge()
                        ->tooltip('Great! You can connect to ' . $app->name . ' now!')
                        ->icon(fn () => $tokenId ? 'heroicon-o-check-badge' : 'heroicon-o-bolt')
                        ->color(fn () => $tokenId ? 'success' : 'primary')
                        ->disabled(fn () => $tokenId ? true : false),
                    Forms\Components\Actions\Action::make('disconnect_' . $app->app_code)
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
                        ->disabled($isFinished)
                ]),
                Forms\Components\TextInput::make('region')
                    ->label('Region')
                    ->prefixIcon('heroicon-o-globe-asia-australia')
                    ->disabled($settings && isset($settings['region']) ? true : false)
                    ->hidden($settings && isset($settings['region']) ? false : true)
                    ->default($settings && isset($settings['region']) ? $settings['region'] : null),
                Forms\Components\Select::make('audience_id')
                    ->label('Mailchimp Audience')
                    ->required()
                    ->options(function () use ($tokenId, $settings, $integrationId): array {
                        $token = Token::query()->find($tokenId);
                        if ($token) {
                            return MailchimpApi::make(accessToken: $token->token, region: $settings['region'])->getAudience($integrationId);
                        }
                        return [];
                    })
                    ->native($isFinished)
                    ->searchable(!$isFinished)
                    ->default($settings && isset($settings['audience_id']) ? $settings['audience_id'] : null)
                    ->hidden($tokenId ? false : true),
            ]);
    }
}
