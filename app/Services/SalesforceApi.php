<?php

namespace App\Services;

use App\Forms\FieldMapping\DefaultMappedItems;
use App\Models\Token;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SalesforceApi
{
    protected Client $client;
    protected string $domain;
    protected string $accessToken;
    protected string $refreshToken;
    protected string $type;
    protected string $apiVersion;
    protected string $rawRefreshToken;

    public function __construct(string $domain, string $accessToken, string $refreshToken, bool $isCrypt = true)
    {
        $this->client = new Client();
        $this->domain = $domain;
        $this->accessToken = $isCrypt ? Crypt::encryptString($accessToken) : $accessToken;
        $this->refreshToken = $isCrypt ? Crypt::decryptString($refreshToken) : $refreshToken;
        $this->rawRefreshToken = $refreshToken;
    }

    public static function make(string $domain, string $accessToken, string $refreshToken, bool $isCrypt = true): self
    {
        return new self($domain, $accessToken, $refreshToken, $isCrypt);
    }

    public function apiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    public function type(string $type): self
    {
        $validTypes = ['Contact', 'Lead', 'Account'];

        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException('Invalid type. Allowed types are: Contact, Lead, and Account.');
        }

        $this->type = $type;
        return $this;
    }

    public function getApiVersion(): string
    {
        try {
            $response = $this->client->get($this->domain . '/services/data', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
            ]);

            $versions = json_decode($response->getBody(), true);

            if (!empty($versions)) {
                usort($versions, function ($a, $b) {
                    return version_compare($b['version'], $a['version']);
                });

                return $versions[0]['version'];
            } else {
                throw new \RuntimeException('Unable to retrieve Salesforce API versions.');
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Handle token expiration and refresh the token
            if ($e->getResponse()->getStatusCode() === 401) {
                $this->refreshAccessToken($this->rawRefreshToken);
                // Retry the API call
                return $this->getApiVersion();
            }
            throw $e;
        }
    }


    public function getFields(int $integrationId, string $mappedItems, bool $isRefresh = false): array
    {
        if ($isRefresh) {
            Cache::forget($integrationId . '_salesforce_fields');
        }

        return Cache::remember($integrationId . '_salesforce_fields', now()->addHour(), function () use ($integrationId, $mappedItems) {
            try {
                $response = $this->client->get("{$this->domain}/services/data/v{$this->apiVersion}/sobjects/{$this->type}/describe", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                    ],
                ]);

                $fieldsMetadata = json_decode($response->getBody());

                $fields = [
                    'Custom Fields' => [],
                    'Default Fields' => [],
                ];

                $mappedItems = DefaultMappedItems::$mappedItems[$mappedItems];

                foreach ($fieldsMetadata->fields as $field) {
                    $fieldName = $field->name;
                    $fieldLabel = $field->label;

                    if (in_array($fieldName, array_keys($mappedItems['FIRST_APP_FIELDS']))) {
                        continue;
                    }

                    if ($field->custom) {
                        $fields['Custom Fields'][$fieldName] = $fieldLabel;
                    } else {
                        $fields['Default Fields'][$fieldName] = $fieldLabel;
                    }
                }

                return $fields;
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // Handle token expiration and refresh the token
                if ($e->getResponse()->getStatusCode() === 401) {
                    $this->refreshAccessToken($this->rawRefreshToken);
                    // Retry the API call
                    return $this->getFields($integrationId, $mappedItems);
                }
                throw $e;
            }
        });
    }

    public function revokeSalesforceAccessToken(): bool
    {
        $response = $this->client->post($this->domain . '/services/oauth2/revoke', [
            'form_params' => [
                'token' => $this->refreshToken
            ],
        ]);

        // Check the response status code to ensure the token was successfully revoked.
        if ($response->getStatusCode() == 200) {
            return true;
        }

        return false;
    }

    private function refreshAccessToken(string $refreshToken): string
    {
        $response = $this->client->post($this->domain . '/services/oauth2/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => Crypt::decryptString($refreshToken),
                'client_id' => env('SALESFORCE_CLIENT_ID'),
                'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
            ],
        ]);

        $tokenData = json_decode($response->getBody(), true);

        if (isset($tokenData['access_token'])) {

            $tokenModel = Token::where('refresh_token', $refreshToken)->first();

            if ($tokenModel) {

                $tokenModel->update([
                    'token' => Crypt::encryptString($tokenData['access_token']),
                ]);

                $this->accessToken = $tokenData['access_token'];

                return $this->accessToken;
            } else {
                throw new \RuntimeException('Token not found in the database.');
            }
        } else {
            throw new \RuntimeException('Failed to refresh the Salesforce access token.');
        }
    }
}
