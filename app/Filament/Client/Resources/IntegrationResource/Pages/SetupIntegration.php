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

    public function mount(Integration $integration)
    {
        if ($integration) {
            $this->integration = $integration->with('appCombination.firstApp', 'appCombination.secondApp')->first();
        } else {
            abort(404);
        }
    }

    public function boot()
    {
        $this->baseWizardStep = app(BaseWizardStep::class);

        $this->firstAppWizardStep = $this->createWizardStep($this->integration->appCombination->firstApp);
        $this->secondAppWizardStep = $this->createWizardStep($this->integration->appCombination->secondApp);
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
            ])
            ->statePath('data');
    }

    private function createWizardStep($app)
    {
        $class = $this->getClassForApp($app->name);
        return $this->baseWizardStep->wizardStep(app($class), $app);
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
