<?php

namespace App\Settings;

class SalesforceSettings
{
    protected $domain;
    protected $syncDataType;
    protected $apiVersion;

    public static function make(): self
    {
        return new self();
    }

    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function apiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    public function syncDataType(string $syncDataType): self
    {
        $this->syncDataType = $syncDataType;
        return $this;
    }

    public function getSettings(): array
    {
        return [
            'domain' => $this->domain,
            'api_version' => $this->apiVersion,
            'sync_data_type' => $this->syncDataType
        ];
    }
}
