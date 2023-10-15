<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class SalesforceApi
{
    protected Client $client;
    protected string $domain;
    protected string $accessToken;
    protected string $refreshToken;
    protected string $type;
    protected string $apiVersion;

    public function __construct(string $domain, string $accessToken, string $refreshToken)
    {
        $this->client = new Client();
        $this->domain = $domain;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public static function make(string $domain, string $accessToken, string $refreshToken): self
    {
        return new self($domain, $accessToken, $refreshToken);
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
        return Cache::remember('salesforce_api_version', now()->addHour(), function () {
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
                    $this->refreshAccessToken($this->refreshToken);
                    // Retry the API call
                    return $this->getApiVersion();
                }
                throw $e;
            }
        });
    }


    public function getCustomField(): array
    {
        return Cache::remember('salesforce_custom_fields', now()->addHour(), function () {
            try {
                $response = $this->client->get("{$this->domain}/services/data/v{$this->apiVersion}/sobjects/{$this->type}/describe", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                    ],
                ]);

                $contactMetadata = json_decode($response->getBody());

                $customFields = array_reduce($contactMetadata->fields, function ($customFieldDetails, $field) {
                    if ($field->custom) {
                        $customFieldDetails[$field->name] = $field->label;
                    }
                    return $customFieldDetails;
                }, []);

                return $customFields;
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // Handle token expiration and refresh the token
                if ($e->getResponse()->getStatusCode() === 401) {
                    $this->refreshAccessToken($this->refreshToken);
                    // Retry the API call
                    return $this->getCustomField();
                }
                throw $e;
            }
        });
    }

    private function refreshAccessToken(string $refreshToken): string
    {
        $response = $this->client->post($this->domain . '/services/oauth2/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => env('SALESFORCE_CLIENT_ID'),
                'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
            ],
        ]);

        $tokenData = json_decode($response->getBody(), true);

        if (isset($tokenData['access_token'])) {
            // Retrieve the Token model from the database (assuming you have one)
            $tokenModel = Token::where('refresh_token', $refreshToken)->first();

            if ($tokenModel) {
                // Update the access token
                $tokenModel->access_token = $tokenData['access_token'];
                $tokenModel->save();

                // Update the class's access token property
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
