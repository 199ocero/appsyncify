<?php

namespace App\Services\Context;

use App\Models\App;
use Illuminate\Support\Collection;
use App\Services\Contracts\HasSynchronizer;

class BaseSynchronizer
{
    public static function make(): self
    {
        return new self();
    }
    public function usingSynchronizer(HasSynchronizer $synchronizer): self
    {
        $this->synchronizer = $synchronizer;
        return $this;
    }

    public function withFirstApp(App $app): self
    {
        $this->firstApp = $app;
        return $this;
    }

    public function withSecondApp(App $app): self
    {
        $this->secondApp = $app;
        return $this;
    }

    public function withDefaultFields(array $defaultFields): self
    {
        $this->defaultFields = $defaultFields;
        return $this;
    }

    public function withCustomFields(array $customFields): self
    {
        $this->customFields = $customFields;
        return $this;
    }

    public function syncData(): array
    {
        $this->synchronizer->getFirstAppData($this->firstApp);
        $this->synchronizer->getSecondAppData($this->secondApp);
        $this->synchronizer->getFields($this->defaultFields, $this->customFields);
        return $this->synchronizer->syncData();
    }
}
