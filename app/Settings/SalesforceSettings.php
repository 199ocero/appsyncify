<?php

namespace App\Settings;

class SalesforceSettings
{
    protected $domain;

    public static function make(): self
    {
        return new self();
    }

    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function getSettings(): array
    {
        return [
            'domain' => $this->domain
        ];
    }
}
