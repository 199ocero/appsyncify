<?php

namespace App\Settings;

class MailchimpSettings
{
    protected $audienceId;
    protected $region;

    public static function make(): self
    {
        return new self();
    }

    public function region(string $region): self
    {
        $this->region = $region;
        return $this;
    }

    public function audienceId(string $audienceId): self
    {
        $this->audienceId = $audienceId;
        return $this;
    }

    public function getSettings(): array
    {
        return [
            'region' => $this->region,
            'audience_id' => $this->audienceId,
        ];
    }
}
