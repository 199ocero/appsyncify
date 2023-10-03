<?php

namespace App\Filament\Client\Resources\IntegrationResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use App\Models\Integration;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Client\Resources\IntegrationResource;

class SetupIntegration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = IntegrationResource::class;

    protected static string $view = 'filament.client.resources.integration-resource.pages.setup-integration';

    public Integration $integration;

    public ?array $data = [];

    public function mount($id): void
    {
        $integration = Integration::find($id);

        if ($integration) {
            $this->integration = $integration;
            $this->form->fill();
        } else {
            abort(404);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('first_app')
                        ->label('Salesforce')
                        ->afterValidation(function (): void {
                            if (!$this->integration->first_app_token_id) {
                                Notification::make()
                                    ->title('Please connect to Salesforce')
                                    ->danger()
                                    ->color('danger')
                                    ->icon('heroicon-o-x-circle')
                                    ->send();

                                throw ValidationException::withMessages([
                                    'first_app_token_id' => 'Please connect to Salesforce',
                                ]);
                            }
                        })
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('salesforce')
                                    ->label('Connect to Salesforce')
                                    ->url(route('auth.salesforce'))
                                    ->icon('heroicon-o-bolt')
                            ])
                        ]),
                    Forms\Components\Wizard\Step::make('second_app')
                        ->label('Mailchimp')
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('mailchimp')
                                    ->label('Connect to Mailchimp')
                                    ->url(route('auth.salesforce'))
                                    ->icon('heroicon-o-bolt')
                            ])
                        ]),
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
}
