<?php

namespace App\Services\Context;

use App\Models\App;
use App\Models\Integration;
use Illuminate\Support\Collection;
use App\Services\Contracts\HasSynchronizer;

class BaseSynchronizer
{
    protected $synchronizer;
    protected $integration;
    protected $firstApp;
    protected $secondApp;
    protected $defaultFields;
    protected $customFields;

    public static function make(): self
    {
        return new self();
    }
    public function usingSynchronizer(HasSynchronizer $synchronizer): self
    {
        $this->synchronizer = $synchronizer;
        return $this;
    }

    public function withIntegration(Integration $integration): self
    {
        $this->integration = $integration;
        return $this;
    }

    public function withDefaultFields(array $defaultFields): self
    {
        $this->defaultFields = $defaultFields;
        return $this;
    }

    public function syncData(): void
    {
        $this->synchronizer->getIntegration($this->integration);
        $this->synchronizer->getFields($this->defaultFields);
        $this->synchronizer->syncData();
    }
}
