<?php

namespace App\Services\Context;

use App\Models\App;
use Illuminate\Support\Collection;
use App\Services\Contracts\HasSynchronizer;

class BaseSynchronizer
{
    public function getFirstAppData(HasSynchronizer $synchronizer, App $app): array
    {
        return $synchronizer->getFirstAppData($app);
    }

    public function getSecondAppData(HasSynchronizer $synchronizer, App $app): array
    {
        return $synchronizer->getSecondAppData($app);
    }

    public function getFields(HasSynchronizer $synchronizer, array $defaultFields = [], array $customFields = []): array
    {
        return $synchronizer->getFields($defaultFields, $customFields);
    }

    public function syncData(HasSynchronizer $synchronizer): array
    {
        return $synchronizer->syncData();
    }
}
