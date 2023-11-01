<?php

namespace App\Services\Combinations;

use App\Models\App;
use App\Services\Contracts\HasSynchronizer;

class SalesforceMailchimp implements HasSynchronizer
{
    protected $firstAppData;
    protected $secondAppData;
    protected $getFields;

    public function getFirstAppData(App $app): array
    {
        return $this->firstAppData = ['firstAppData'];
    }

    public function getSecondAppData(App $app): array
    {
        return $this->secondAppData = ['secondAppData'];
    }

    public function getFields(array $defaultFields = [], array $customFields = []): array
    {
        return $this->getFields = ['getFields'];
    }

    public function syncData(): array
    {
        return [
            $this->firstAppData,
            $this->secondAppData,
            $this->getFields
        ];
    }
}
