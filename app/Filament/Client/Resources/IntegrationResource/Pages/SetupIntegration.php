<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use App\Models\App;
use Filament\Forms;
use Filament\Tables;
use App\Enums\Constant;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Integration;
use App\Services\MailchimpApi;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use App\Forms\Context\BaseWizardStep;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Forms\WizardStep\FieldMappingWizardStep;
use App\Forms\WizardStep\SyncScheduleWizardStep;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Client\Resources\IntegrationResource;
use App\Models\SyncLog;

class SetupIntegration extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

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
            Constant::FIRST_APP,
        );

        $this->secondAppWizardStep = $this->createWizardStep(
            $this->integration->appCombination->secondApp,
            $this->integration,
            Constant::SECOND_APP
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
                Forms\Components\Tabs::make('My Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Syncify Setup')
                            ->icon('heroicon-o-cog-6-tooth')
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
                                    ->persistStepInQueryString()

                            ])
                            ->live(),
                        Forms\Components\Tabs\Tab::make('Syncify Run')
                            ->icon('heroicon-o-rocket-launch')
                            ->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('syncify_run')
                                        ->label('Run Sync')
                                        ->icon('heroicon-o-play')
                                        ->requiresConfirmation()
                                        ->modalHeading('Sync Data')
                                        ->modalDescription('Do you want to sync your data?')
                                        ->modalSubmitActionLabel('Yes, sync it')
                                        ->modalIcon('heroicon-o-play')
                                        ->action(function () {
                                            $settings = json_decode($this->integration->second_app_settings, true);
                                            $result = MailchimpApi::make(
                                                accessToken: $this->integration->secondAppToken->token,
                                                region: $settings['region']
                                            )->syncData($settings['audience_id']);

                                            dd($result);
                                        })
                                ])
                                    ->alignEnd(),
                                Forms\Components\Fieldset::make('Sync Details')
                                    ->columns(5)
                                    ->schema([
                                        Forms\Components\Placeholder::make('sync_combination')
                                            ->label('Sync Combination')
                                            ->content(new HtmlString("<span class='text-gray-500'>{$this->integration->appCombination->firstApp->name} - {$this->integration->appCombination->secondApp->name}</span>")),
                                        Forms\Components\Placeholder::make('sync_is_fixed_time_value')
                                            ->label('Sync Fix Time')
                                            ->content(
                                                isset($this->schedule) && $this->schedule['is_fixed_time'] == 1
                                                    ? new HtmlString("<span class='text-gray-500'>Every {$this->schedule['is_fixed_time_value']} Hour/s</span>")
                                                    : ($this->schedule === null
                                                        ? new HtmlString("<span class='text-gray-500'>Manual</span>")
                                                        : new HtmlString("<span class='text-gray-500'>Every 6 Hours By Default</span>")
                                                    )
                                            ),
                                        Forms\Components\Placeholder::make('sync_day_value')
                                            ->label('Sync Day')
                                            ->content(
                                                isset($this->schedule) && isset($this->schedule['day_value'])
                                                    ? (count($this->schedule['day_value']) == 7
                                                        ? new HtmlString("<span class='text-gray-500'>Sync Every Day</span>")
                                                        : new HtmlString("<span class='text-gray-500'>" . implode(', ', array_map('ucwords', getDaysArrangement($this->schedule['day_value']))) . "</span>")
                                                    )
                                                    : new HtmlString("<span class='text-gray-500'>Manual</span>")
                                            ),
                                        Forms\Components\Placeholder::make('batch_id')
                                            ->label('Batch Id')
                                            ->content(
                                                new HtmlString("<span class='text-gray-500'>No Batch Created</span>")
                                            ),
                                        Forms\Components\Placeholder::make('batch_status')
                                            ->label('Batch Status')
                                            ->content(
                                                isset($this->schedule) && isset($this->schedule['day_value'])
                                                    ? (count($this->schedule['day_value']) == 7
                                                        ? new HtmlString("<span class='text-gray-500'>Sync Every Day</span>")
                                                        : new HtmlString("<span class='text-gray-500'>" . implode(', ', array_map('ucwords', getDaysArrangement($this->schedule['day_value']))) . "</span>")
                                                    )
                                                    : new HtmlString("<span class='text-gray-500'>Manual</span>")
                                            ),
                                    ]),
                                Forms\Components\Section::make('Log Viewer')
                                    ->description('You can see all the logs here per operation.')
                                    ->schema([
                                        Forms\Components\ViewField::make('log_viewer')
                                            ->label('Logs')
                                            ->view('tables.components.log-viewer'),
                                    ])
                            ])
                            ->badge('Available')
                            ->hidden(fn () => $this->integration->tab_step == 1 ? true : false),
                    ])
                    ->persistTabInQueryString()
                    ->activeTab($this->integration->tab_step),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SyncLog::query())
            ->columns([
                Tables\Columns\TextColumn::make('operation_id')
                    ->label('Operation ID')
                    ->placeholder('No operation.')
                    ->wrap(),
                Tables\Columns\TextColumn::make('log_type')
                    ->label('Type')
                    ->placeholder('No log type.')
                    ->wrap()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Constant::INFO => 'info',
                        Constant::WARNING => 'warning',
                        Constant::ERROR => 'danger',
                    }),
                Tables\Columns\TextColumn::make('message')
                    ->placeholder('No message.')
                    ->wrap(),
                Tables\Columns\TextColumn::make('api_endpoint')
                    ->label('Endpoint')
                    ->placeholder('No endpoint.')
                    ->wrap(),
                Tables\Columns\TextColumn::make('request_data')
                    ->label('Request')
                    ->placeholder('No request.')
                    ->wrap(),
                Tables\Columns\TextColumn::make('response_data')
                    ->label('Response')
                    ->placeholder('No response.')
                    ->wrap(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
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
            ->color('success')
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
        $class = $this->getClassForApp($app->name);
        return $this->baseWizardStep->wizardStep(app($class), $app, $integration, $type);
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
