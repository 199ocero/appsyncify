<?php

namespace App\Forms\WizardStep;

use App\Models\Token;
use App\Enums\Constant;
use App\Models\Integration;

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
        $integration = Integration::query()->find($this->integrationId);

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

        $updateDataKey = $this->type == Constant::FIRST_APP ? 'first_app' : 'second_app';

        $integration->update([
            "{$updateDataKey}_settings" => null,
        ]);

        Token::query()->find($this->tokenId)->delete();
    }
}
