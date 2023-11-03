<?php

namespace App\Forms\WizardStep;

use App\Models\Token;
use App\Enums\Constant;
use App\Models\Integration;
use App\Services\SalesforceApi;

class GeneralWizardStep
{
    protected $integrationId;
    protected $type;
    protected $step;
    protected $tokenId;

    public function __construct(int $integrationId, string $type, int $step, string $tokenId)
    {
        $this->integrationId = $integrationId;
        $this->type = $type;
        $this->step = $step;
        $this->tokenId = $tokenId;
    }

    public static function make(int $integrationId, string $type, int $step, string $tokenId): self
    {
        return new self($integrationId, $type, $step, $tokenId);
    }

    public function disconnectApp(): void
    {
        $integration = Integration::query()->with(
            'firstAppToken',
            'secondAppToken',
            'appCombination.firstApp',
            'appCombination.secondApp',
        )
            ->find($this->integrationId);

        if ($this->type == Constant::FIRST_APP && $this->step >= 1) {
            $integration->update([
                'step' => 1,
            ]);
        }

        if ($this->type == Constant::SECOND_APP && !$integration->first_app_token_id) {
            $integration->update([
                'step' => 1,
            ]);
        }

        if ($this->type == Constant::SECOND_APP && $integration->first_app_token_id && $this->step >= 2) {
            $integration->update([
                'step' => 2,
            ]);
        }

        $token = $this->type == Constant::FIRST_APP ? $integration->firstAppToken : $integration->secondAppToken;

        $settings = $this->type == Constant::FIRST_APP ? json_decode($integration->first_app_settings, true) : json_decode($integration->second_app_settings, true);

        $appCode = $this->type == Constant::FIRST_APP ? $integration->appCombination->firstApp->app_code : $integration->appCombination->secondApp->app_code;

        $this->revokeAccessToken($appCode, $token, $settings);

        $updateDataKey = $this->type == Constant::FIRST_APP ? 'first_app' : 'second_app';

        $integration->update([
            "{$updateDataKey}_settings" => null,
            'custom_field_mapping' => null,
            'tab_step' => 1,
            'is_finished' => 0
        ]);

        Token::query()->find($this->tokenId)->delete();
    }

    private function revokeAccessToken($appCode, $token, $settings)
    {
        return match ($appCode) {
            Constant::APP_CODE[Constant::SALESFORCE] => SalesforceApi::make(domain: $settings['domain'], accessToken: $token->token, refreshToken: $token->refresh_token)->revokeSalesforceAccessToken(),
            default => throw new \Exception('App code not found.', 404),
        };
    }
}
