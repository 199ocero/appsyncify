<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use Filament\Forms;
use App\Enums\Constant;
use Filament\Forms\Form;
use App\Models\Integration;
use Filament\Resources\Pages\Page;
use App\Forms\Context\BaseWizardStep;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Forms\WizardStep\FieldMappingWizardStep;
use App\Forms\WizardStep\SyncScheduleWizardStep;
use App\Filament\Client\Resources\IntegrationResource;

class SetupIntegration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = IntegrationResource::class;

    protected static string $view = 'filament.client.resources.integration-resource.pages.setup-integration';

    public Integration $integration;

    protected $baseWizardStep;

    protected $firstAppWizardStep;

    protected $secondAppWizardStep;

    protected $fieldMappingWizardStep;

    public ?array $data = [];

    public function mount($integration)
    {

        if ($integration) {
            $integration->load([
                'appCombination.firstApp',
                'appCombination.secondApp',
                'firstAppToken',
                'secondAppToken'
            ]);
            $this->integration = $integration;
            $this->form->fill();
        } else {
            abort(404);
        }
    }

    public function boot()
    {
        $this->heading = $this->integration->name;
        $this->subheading = $this->integration->description;

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

        $this->fieldMappingWizardStep = $this->baseWizardStep->fieldMappingWizardStep(
            app(FieldMappingWizardStep::class),
            $this->integration,
            $this->integration->appCombination->firstApp->app_code . '_' . $this->integration->appCombination->secondApp->app_code
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('My Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Syncify Setup')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Wizard::make([
                                    $this->firstAppWizardStep,
                                    $this->secondAppWizardStep,
                                    $this->fieldMappingWizardStep,
                                    SyncScheduleWizardStep::make()->schedule(),
                                ])
                                    ->nextAction(
                                        fn (Action $action) => $action->label('Next Step')->icon('heroicon-o-chevron-right'),
                                    )
                                    ->previousAction(
                                        fn (Action $action) => $action->label('Go Back')->icon('heroicon-o-chevron-left'),
                                    )
                                    ->startOnStep($this->integration->step)
                            ])
                            ->live(),
                        Forms\Components\Tabs\Tab::make('Syncify Run')
                            ->icon('heroicon-o-rocket-launch')
                            ->schema([])
                            ->hidden(fn () => $this->integration->step >= 4 ? false : true),
                    ]),
            ])
            ->statePath('data');
    }

    private function createWizardStep($app, $tokenId = null, $integrationId, $settings, $step, $type)
    {
        $class = $this->getClassForApp($app->name);
        return $this->baseWizardStep->wizardStep(app($class), $app, $tokenId, $integrationId, $settings, $step, $type);
    }

    private function getClassForApp($appName)
    {
        return match ($appName) {
            Constant::SALESFORCE => \App\Forms\WizardStep\Apps\SalesforceWizardStep::class,
            Constant::MAILCHIMP => \App\Forms\WizardStep\Apps\MailchimpWizardStep::class,
                // Add more cases as needed
            default => null,
        };
    }
}
