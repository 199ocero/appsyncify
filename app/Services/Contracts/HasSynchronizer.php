<?php

namespace App\Services\Contracts;

use App\Models\App;

interface HasSynchronizer
{
    public function getFirstAppData(App $app): array;
    public function getSecondAppData(App $app): array;
    public function getFields(array $defaultFields = [], array $customFields = []): array;
    public function syncData(): void;
}
