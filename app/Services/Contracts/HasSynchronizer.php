<?php

namespace App\Services\Contracts;

use App\Models\App;
use App\Models\Integration;

interface HasSynchronizer
{
    public function getIntegration(Integration $integration): Integration;
    public function getFirstAppData(): array;
    public function getSecondAppData(): array;
    public function getFields(array $defaultFields = []): array;
    public function syncData(): void;
}
