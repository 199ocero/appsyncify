<?php

namespace App\Services\Combinations;

use App\Models\App;
use App\Models\Integration;
use MailchimpMarketing\ApiClient;
use App\Services\Contracts\HasSynchronizer;
use GuzzleHttp\Client;

class SalesforceMailchimp implements HasSynchronizer
{
    protected $mailchimpApiClient;
    protected $salesforceApiClient;
    protected $integration;
    protected $getFields;

    public function __construct()
    {
        $this->mailchimpApiClient = new ApiClient();
        $this->salesforceApiClient = new Client();
    }

    public function getIntegration(Integration $integration): Integration
    {
        return $this->integration = $integration->load([
            'appCombination.firstApp',
            'appCombination.secondApp',
            'firstAppToken',
            'secondAppToken'
        ]);
    }

    public function getFields(array $defaultFields = []): array
    {
        $mappedDefaultFields = [];

        for ($i = 0; $i < $defaultFields['COUNT']; $i++) {
            $mappedDefaultFields[] = [
                $defaultFields['DIRECTION'][$i],
                array_keys($defaultFields['FIRST_APP_FIELDS'])[$i],
                array_keys($defaultFields['SECOND_APP_FIELDS'])[$i],
            ];
        }

        $mappedCustomFields = [];

        foreach (array_values($this->integration->custom_field_mapping) as $field) {
            $mappedCustomFields[] = [
                $field["direction"],
                $field["first_app_fields"],
                $field["second_app_fields"],
            ];
        }

        return $this->getFields = array_merge(
            $mappedDefaultFields,
            $mappedCustomFields
        );
    }

    public function getFirstAppData(): array
    {
        return [];
    }

    public function getSecondAppData(): array
    {
        return [];
    }

    public function syncData(): void
    {
    }
}
