<?php

namespace App\Forms\WizardStep;

use Filament\Forms;
use App\Enums\Constant;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use App\Forms\FieldMapping\DefaultMappedItems;
use App\Forms\Contracts\HasFieldMappingWizardStep;

class FieldMappingWizardStep implements HasFieldMappingWizardStep
{
    public function fieldMappingWizardStep(
        Model $integration,
        string $mappedItems
    ): Component {
        return Forms\Components\Wizard\Step::make('field_mapping')
            ->label('Field Mapping')
            ->schema([
                Forms\Components\Section::make('Pre-mapped Fields')
                    ->description('These are the pre-mapped fields that make sure data moves smoothly from one app to another, simplifying the transfer.')
                    ->schema([
                        Forms\Components\ViewField::make('default_items')
                            ->label('')
                            ->view('forms.components.field-mapping')
                            ->viewData([
                                'mappedItems' => DefaultMappedItems::$mappedItems[$mappedItems]
                            ])
                    ])
                    ->collapsed()
                    ->collapsible(),
                Forms\Components\Toggle::make('field_mapping_enabled')
                    ->label('Enable Field Mapping')
                    ->live(),
                Forms\Components\Repeater::make('custom_field_mapping')
                    ->schema([
                        Forms\Components\Select::make('first_app_fields')
                            ->label(function () use ($integration, $mappedItems) {
                                return $integration->appCombination->firstApp->name . ' Fields';
                            })
                            ->required()
                            ->options(function () use ($integration, $mappedItems) {
                                if ($integration->firstAppToken) {
                                    $result = $this->getFieldMappingOptions(
                                        $integration->id,
                                        $integration->appCombination->firstApp->name,
                                        $integration->firstAppToken,
                                        json_decode($integration->first_app_settings, true),
                                        $mappedItems
                                    );

                                    return $result;
                                }
                                return [];
                            })
                            ->disableOptionWhen(
                                fn (Get $get, string $value, mixed $state) => collect($get('../../custom_field_mapping'))
                                    ->pluck('first_app_fields')
                                    ->diff([$state])
                                    ->filter()
                                    ->contains($value)
                            )
                            ->searchable()
                            ->live(),
                        Forms\Components\Select::make('direction')
                            ->label('Sync Direction')
                            ->required()
                            ->options([
                                'right' => "<div class='flex items-center justify-between gap-4'>
                                            <span>Right</span>
                                            <span>--></span>
                                        </div>",
                                'left' => "<div class='flex items-center justify-between gap-4'>
                                                <span>Left</span>
                                                <span><--</span>
                                            </div>",
                                'bidirectional' => "<div class='flex items-center justify-between gap-4'>
                                            <span>Bidirectional</span>
                                            <span><--></span>
                                        </div>",
                            ])
                            ->allowHtml()

                            ->validationAttribute('sync direction')
                            ->disableOptionWhen(fn (string $value): bool => $value === 'left' || $value === 'bidirectional')
                            ->live(),
                        Forms\Components\Select::make('second_app_fields')
                            ->label(function () use ($integration) {
                                return $integration->appCombination->secondApp->name . ' Fields';
                            })
                            ->required()
                            ->options(function () use ($integration, $mappedItems) {
                                if ($integration->secondAppToken) {
                                    $result = $this->getFieldMappingOptions(
                                        $integration->id,
                                        $integration->appCombination->secondApp->name,
                                        $integration->secondAppToken,
                                        json_decode($integration->second_app_settings, true),
                                        $mappedItems
                                    );

                                    return $result;
                                }
                                return [];
                            })
                            ->disableOptionWhen(
                                fn (Get $get, string $value, mixed $state) => collect($get('../../custom_field_mapping'))
                                    ->pluck('second_app_fields')
                                    ->diff([$state])
                                    ->filter()
                                    ->contains($value)
                            )
                            ->searchable()
                            ->live(),
                    ])
                    ->required(fn (Get $get) => $get('field_mapping_enabled') == true ? true : false)
                    ->hidden(fn (Get $get) => $get('field_mapping_enabled') == false ? true : false)
                    ->itemLabel(function (array $state): ?string {
                        if (isset($state['first_app_fields']) && isset($state['second_app_fields']) && isset($state['direction'])) {
                            $direction = match ($state['direction']) {
                                'right' => '-->',
                                'left' => '<--',
                                'bidirectional' => '<-->',
                            };
                            return $state['first_app_fields'] . ' ' . $direction . ' ' . $state['second_app_fields'];
                        }
                        return 'Select Field';
                    })
                    ->collapsible()
                    ->columns(3)
                    ->addActionLabel('Add Another Field'),
            ]);
    }

    private function getFieldMappingOptions($integrationId, $appName, $token, $settings, $mappedItems)
    {
        return match ($appName) {
            Constant::SALESFORCE => \App\Services\SalesforceApi::make(domain: $settings['domain'], accessToken: $token->token, refreshToken: $token->refresh_token)
                ->apiVersion($settings['api_version'])
                ->type(ucfirst($settings['sync_data_type']))
                ->getFields($integrationId, $mappedItems),
            Constant::MAILCHIMP => \App\Services\MailchimpApi::make(accessToken: $token->token, region: $settings['region'])
                ->getAudienceFields($settings['audience_id'], $mappedItems),
            default => null,
        };
    }
}
