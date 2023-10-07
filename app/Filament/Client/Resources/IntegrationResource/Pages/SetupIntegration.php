<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use App\Enums\Constant;
use Filament\Forms;
use Filament\Forms\Form;
use App\Models\Integration;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Client\Resources\IntegrationResource;
use App\Forms\Context\BaseWizardStep;

class SetupIntegration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = IntegrationResource::class;

    protected static string $view = 'filament.client.resources.integration-resource.pages.setup-integration';

    public Integration $integration;

    protected $baseWizardStep;

    protected $firstAppWizardStep;

    protected $secondAppWizardStep;

    public ?array $data = [];

    public function mount(Integration $integration)
    {
        if ($integration) {
            $this->integration = $integration->with('appCombination.firstApp', 'appCombination.secondApp')->first();
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
}
