<?php

namespace App\Services\Combinations;

use App\Models\App;
use App\Enums\AppType;
use GuzzleHttp\Client;
use App\Models\Integration;
use App\Services\SalesforceApi;
use MailchimpMarketing\ApiClient;
use App\Services\Contracts\HasSynchronizer;

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
        $salesforceFields = [];
        $mailchimpFields = [];

        for ($i = 0; $i < $defaultFields['COUNT']; $i++) {
            $mappedDefaultFields[] = [
                $defaultFields['DIRECTION'][$i],
                array_keys($defaultFields['FIRST_APP_FIELDS'])[$i],
                array_keys($defaultFields['SECOND_APP_FIELDS'])[$i],
            ];
            $salesforceFields[] = array_keys($defaultFields['FIRST_APP_FIELDS'])[$i];
        }

        $mappedCustomFields = [];

        foreach (array_values($this->integration->custom_field_mapping) as $field) {
            $mappedCustomFields[] = [
                $field["direction"],
                $field["first_app_fields"],
                $field["second_app_fields"],
            ];
            $mailchimpFields[] = $field["second_app_fields"];
        }

        return $this->getFields = [
            'all_fields' => array_merge(
                $mappedDefaultFields,
                $mappedCustomFields
            ),
            'salesforce_fields' => $salesforceFields,
            'mailchimp_fields' => $mailchimpFields
        ];
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
        $settings = getSettings(type: getEnumValue(AppType::FIRST_APP), integration: $this->integration);

        SalesforceApi::make(
            domain: $settings['domain'],
            accessToken: $this->integration->firstAppToken->token,
            refreshToken: $this->integration->firstAppToken->refresh_token,
        )
            ->getAllData($this->getFields['salesforce_fields']);
    }
}
