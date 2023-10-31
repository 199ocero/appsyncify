<?php

namespace App\Services\Combinations;

use App\Models\App;
use App\Services\Contracts\HasSynchronizer;

class SalesforceMailchimp implements HasSynchronizer
{
    public function getFirstAppData(App $app): array
    {
        return [];
    }

    public function getSecondAppData(App $app): array
    {
        return [];
    }

    public function getFields(array $defaultFields = [], array $customFields = []): array
    {
        return [];
    }

    public function syncData(): void
    {
    }
}
