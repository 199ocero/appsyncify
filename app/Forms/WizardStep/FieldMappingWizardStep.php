<?php

namespace App\Forms\WizardStep;

use Filament\Forms;
use App\Enums\Constant;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Integration;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
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
            ->afterValidation(function ($state, Set $set) use ($integration): void {

                if ($integration->step == 3) {
                    Integration::query()->find($integration->id)->update([
                        'step' => (int)$integration->step + 1
                    ]);
                }

                if ($state['field_mapping_enabled'] == true) {
                    Integration::query()->find($integration->id)->update([
                        'custom_field_mapping' => $state['custom_field_mapping']
                    ]);
                } else {
                    if ($integration->custom_field_mapping) {
                        Integration::query()->find($integration->id)->update([
                            'custom_field_mapping' => null
                        ]);

                        $set('custom_field_mapping', []);
                    }
                }
            })
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
                Forms\Components\Grid::make()
                    ->columns([
                        'md' => 2
                    ])
                    ->schema([
                        Forms\Components\Toggle::make('field_mapping_enabled')
                            ->label('Enable Field Mapping')
                            ->default($integration->custom_field_mapping == null ? false : true)
                            ->live(),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('refresh_fields')
                                ->label('Refresh Fields')
                                ->icon('heroicon-o-arrow-path')
                                ->size('sm')
                                ->requiresConfirmation()
                                ->modalHeading('Refresh Fields')
                                ->modalDescription('This will refresh the fields for the selected app.')
                                ->modalSubmitActionLabel('Yes, refresh fields')
                                ->modalIcon('heroicon-o-arrow-path')
                                ->action(function (Set $set) use ($integration, $mappedItems) {

                                    if ($integration->firstAppToken && $integration->secondAppToken) {
                                        $first = $this->getFieldMappingOptions(
                                            $integration->id,
                                            $integration->appCombination->firstApp->name,
                                            $integration->firstAppToken,
                                            json_decode($integration->first_app_settings, true),
                                            $mappedItems,
                                            true
                                        );

                                        $second = $this->getFieldMappingOptions(
                                            $integration->id,
                                            $integration->appCombination->secondApp->name,
                                            $integration->secondAppToken,
                                            json_decode($integration->second_app_settings, true),
                                            $mappedItems,
                                            true
                                        );

                                        $set('first_app_fields', $first);
                                        $set('second_app_fields', $second);

                                        Notification::make()
                                            ->title('Fields Refreshed')
                                            ->success()
                                            ->color('success')
                                            ->send();
                                    }
                                })
                        ])
                            ->hidden(fn (Get $get) => $get('field_mapping_enabled') == true ? false : true)
                            ->alignEnd(),
                    ]),
                Forms\Components\Section::make('Custom Field Mapping')
                    ->description('Customize the field mapping for each app.')
                    ->schema([
                        Forms\Components\Repeater::make('custom_field_mapping')
                            ->label('')
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
                            ->validationAttribute('custom')
                            ->required(fn (Get $get) => $get('field_mapping_enabled') == true ? true : false)
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
                            ->default($integration->custom_field_mapping ?? [])
                            ->collapsible()
                            ->columns(3)
                            ->addActionLabel('Add Field'),
                    ])
                    ->hidden(fn (Get $get) => $get('field_mapping_enabled') == false ? true : false)
            ]);
    }

    private function getFieldMappingOptions($integrationId, $appName, $token, $settings, $mappedItems, $forceRefresh = false)
    {
        return match ($appName) {
            Constant::SALESFORCE => \App\Services\SalesforceApi::make(domain: $settings['domain'], accessToken: $token->token, refreshToken: $token->refresh_token)
                ->apiVersion($settings['api_version'])
                ->type(ucfirst($settings['sync_data_type']))
                ->getFields($integrationId, $mappedItems, $forceRefresh),
            Constant::MAILCHIMP => \App\Services\MailchimpApi::make(accessToken: $token->token, region: $settings['region'])
                ->getAudienceFields($settings['audience_id'], $mappedItems, $forceRefresh),
            default => null,
        };
    }
}
