<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use Filament\Forms;
use App\Enums\Constant;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Integration;
use Filament\Resources\Pages\Page;
use App\Forms\Context\BaseWizardStep;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Actions\Action;
use App\Forms\FieldMapping\DefaultMappedItems;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Client\Resources\IntegrationResource;
use Illuminate\Support\Facades\Crypt;

class SetupIntegration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = IntegrationResource::class;

    protected static string $view = 'filament.client.resources.integration-resource.pages.setup-integration';

    public Integration $integration;

    protected $baseWizardStep;

    protected $firstAppWizardStep;

    protected $secondAppWizardStep;

    protected $mappedItems;

    protected $fieldMappingOptions;

    public ?array $data = [];

    public function mount(Integration $integration)
    {
        if ($integration) {
            $this->integration = $integration->with(
                'appCombination.firstApp',
                'appCombination.secondApp',
                'firstAppToken',
                'secondAppToken'
            )->first();
            $this->form->fill();
        } else {
            abort(404);
        }
    }

    public function boot()
    {
        $this->baseWizardStep = app(BaseWizardStep::class);

        $this->firstAppWizardStep = $this->createWizardStep(
            $this->integration->appCombination->firstApp,
            $this->integration->first_app_token_id,
            $this->integration->id,
            json_decode($this->integration->first_app_settings, true),
            $this->integration->step,
            Constant::FIRST_APP
        );
        $this->secondAppWizardStep = $this->createWizardStep(
            $this->integration->appCombination->secondApp,
            $this->integration->second_app_token_id,
            $this->integration->id,
            json_decode($this->integration->second_app_settings, true),
            $this->integration->step,
            Constant::SECOND_APP
        );

        $this->mappedItems = $this->integration->appCombination->firstApp->app_code . '_' . $this->integration->appCombination->secondApp->app_code;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    $this->firstAppWizardStep,
                    $this->secondAppWizardStep,
                    Forms\Components\Wizard\Step::make('field_mapping')
                        ->label('Field Mapping')
                        ->schema([
                            Forms\Components\Section::make('Pre-mapped Fields')
                                ->description('These are the pre-mapped fields that make sure data moves smoothly from one app to another, simplifying the transfer.')
                                ->schema([
                                    Forms\Components\ViewField::make('default_items')
                                        ->label('')
                                        ->view('forms.components.field-mapping')
                                        ->viewData([
                                            'mappedItems' => DefaultMappedItems::$mappedItems[$this->mappedItems]
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
                                        ->required()
                                        ->options(function () {
                                            if ($this->integration->firstAppToken) {
                                                return $this->getFieldMappingOptions(
                                                    $this->integration->appCombination->firstApp->name,
                                                    $this->integration->firstAppToken,
                                                    json_decode($this->integration->first_app_settings, true)
                                                );
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
                                        ->reactive()
                                        ->native(false),
                                    Forms\Components\Select::make('direction')
                                        ->label('Sync Direction')
                                        ->required()
                                        ->options([
                                            'left' => "<div class='flex items-center justify-between gap-4'>
                                                <span>Left</span>
                                                <span><--</span>
                                            </div>",
                                            'right' => "<div class='flex items-center justify-between gap-4'>
                                            <span>Right</span>
                                            <span>--></span>
                                        </div>",
                                            'bidirectional' => "<div class='flex items-center justify-between gap-4'>
                                            <span>Bidirectional</span>
                                            <span><--></span>
                                        </div>",
                                        ])
                                        ->allowHtml()
                                        ->native(false),
                                    Forms\Components\Select::make('second_app_fields')
                                        ->required()
                                        ->options([
                                            'email' => 'Email',
                                            'phone_number' => 'Phone Number',
                                            'first_name' => 'First Name',
                                            'last_name' => 'Last Name',
                                            'address' => 'Address'
                                        ])
                                        ->disableOptionWhen(
                                            fn (Get $get, string $value, mixed $state) => collect($get('../../custom_field_mapping'))
                                                ->pluck('second_app_fields')
                                                ->diff([$state])
                                                ->filter()
                                                ->contains($value)
                                        )
                                        ->reactive()
                                        ->native(false),
                                ])
                                ->required(fn (Get $get) => $get('field_mapping_enabled') == true ? true : false)
                                ->hidden(fn (Get $get) => $get('field_mapping_enabled') == false ? true : false)
                                ->itemLabel(function (array $state): ?string {
                                    if (isset($state['first_app_fields']) && isset($state['second_app_fields'])) {
                                        return $state['first_app_fields'] . ' => ' . $state['second_app_fields'];
                                    }
                                    return 'Select Field';
                                })
                                ->collapsible()
                                ->columns(3)
                                ->addActionLabel('Add Another Field'),
                        ]),
                    Forms\Components\Wizard\Step::make('schedule')
                        ->label('Schedule')
                        ->schema([]),
                ])
                    ->nextAction(
                        fn (Action $action) => $action->label('Next Step')->icon('heroicon-o-chevron-right'),
                    )
                    ->previousAction(
                        fn (Action $action) => $action->label('Go Back')->icon('heroicon-o-chevron-left'),
                    )
                    ->startOnStep($this->integration->step)
            ])
            ->statePath('data');
    }

    private function createWizardStep($app, $token_id = null, $integration_id, $settings, $step, $type)
    {
        $class = $this->getClassForApp($app->name);
        return $this->baseWizardStep->wizardStep(app($class), $app, $token_id, $integration_id, $settings, $step, $type);
    }

    private function getClassForApp($appName)
    {
        $classMap = [
            Constant::SALESFORCE => \App\Forms\WizardStep\SalesforceWizardStep::class,
            Constant::MAILCHIMP => \App\Forms\WizardStep\MailchimpWizardStep::class,
            // more here
        ];

        return $classMap[$appName] ?? null;
    }

    private function getFieldMappingOptions($appName, $token, $settings)
    {
        $classMap = [
            Constant::SALESFORCE => \App\Services\SalesforceApi::make(domain: $settings['domain'], accessToken: Crypt::decryptString($token->token), refreshToken: Crypt::decryptString($token->refresh_token))
                ->apiVersion($settings['api_version'])
                ->type(ucfirst($settings['sync_data_type']))
                ->getCustomField(),
        ];

        return $classMap[$appName] ?? null;
    }
}
