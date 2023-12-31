<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use App\Enums\App as EnumsApp;
use App\Enums\AppType;
use App\Models\App;
use Filament\Forms;
use Filament\Forms\Form;
use App\Models\Integration;
use Filament\Notifications;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use App\Forms\Context\BaseWizardStep;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
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

    protected $isFinished;

    protected $isFinishedLabel;

    protected $schedule;

    public ?array $data = [];

    protected $listeners = ['updateSetupTab' => '$refresh'];

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
            $this->integration,
            AppType::FIRST_APP->value,
        );

        $this->secondAppWizardStep = $this->createWizardStep(
            $this->integration->appCombination->secondApp,
            $this->integration,
            AppType::SECOND_APP->value
        );

        $this->fieldMappingWizardStep = $this->baseWizardStep->fieldMappingWizardStep(
            app(FieldMappingWizardStep::class),
            $this->integration,
            $this->integration->appCombination->firstApp->app_code . '_' . $this->integration->appCombination->secondApp->app_code
        );

        $this->isFinished = $this->integration->is_finished == 1 ? "wire:click='editSetup' icon='heroicon-o-pencil-square'" : "type='submit' icon='heroicon-o-check'";
        $this->isFinishedLabel = $this->integration->is_finished == 1 ? 'Edit Setup' : 'Finish Setup';
        $this->schedule = $this->integration->schedule ? json_decode($this->integration->schedule, true) : null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    $this->firstAppWizardStep,
                    $this->secondAppWizardStep,
                    $this->fieldMappingWizardStep,
                    SyncScheduleWizardStep::make($this->integration)->schedule(),
                ])
                    ->nextAction(
                        fn (Action $action) => $action->label('Next Step')->icon('heroicon-o-chevron-right'),
                    )
                    ->previousAction(
                        fn (Action $action) => $action->label('Go Back')->icon('heroicon-o-chevron-left'),
                    )
                    ->startOnStep($this->integration->step)
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                    <x-filament::button
                        size="lg"
                        $this->isFinished
                    >
                       $this->isFinishedLabel
                    </x-filament::button>
                BLADE)))
                    ->startOnStep($this->integration->step)
                    ->live()
            ])
            ->statePath('data');
    }

    /**
     * To save the final step
     * sync scheduling
     */
    public function create(): void
    {
        $state = $this->form->getState();

        if ($state['schedule_enabled']) {
            Integration::query()->find($this->integration->id)->update([
                'schedule' => json_encode([
                    'schedule_enabled' => $state['schedule_enabled'],
                    'is_fixed_time' => $state['is_fixed_time'],
                    'is_fixed_time_value' => isset($state['is_fixed_time_value']) ? $state['is_fixed_time_value'] : null,
                    'day_value' => $state['day_value']
                ]),
                'tab_step' => 2,
                'is_finished' => 1
            ]);
        } else {
            Integration::query()->find($this->integration->id)->update([
                'schedule' => null,
                'tab_step' => 2,
                'is_finished' => 1
            ]);
        }

        $this->dispatch('updateSetupTab');

        Notification::make()
            ->title('Syncify Setup')
            ->body('Your syncify setup is complete. You can now sync your data.')
            ->icon('heroicon-o-check-circle')
            ->success()
            ->persistent()
            ->actions([
                Notifications\Actions\Action::make('launch_sync')
                    ->label('Launch Integration')
                    ->icon('heroicon-o-rocket-launch')
                    ->button()
                    ->url(route('filament.client.resources.integrations.launch', $this->integration->id))
            ])
            ->send();
    }

    public function editSetup(): void
    {
        Integration::query()->find($this->integration->id)->update([
            'is_finished' => 0
        ]);

        $this->dispatch('updateSetupTab');
    }

    private function createWizardStep(App $app, Integration $integration, string $type)
    {
        $class = $this->getClassForApp($app->app_code);
        return $this->baseWizardStep->wizardStep(app($class), $app, $integration, $type);
    }

    private function getClassForApp($appCode)
    {
        return match ($appCode) {
            getEnumValue(EnumsApp::SALESFORCE) => \App\Forms\WizardStep\Apps\SalesforceWizardStep::class,
            getEnumValue(EnumsApp::MAILCHIMP) => \App\Forms\WizardStep\Apps\MailchimpWizardStep::class,
                // Add more cases as needed
            default => throw new \Exception('App code not found.', 404),
        };
    }
}
