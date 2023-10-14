<?php

namespace App\Services;

use GuzzleHttp\Client;

class SalesforceApi
{
    protected Client $client;
    protected string $domain;
    protected string $accessToken;
    protected string $type;
    protected string $apiVersion;

    public function __construct(string $domain, string $accessToken)
    {
        $this->client = new Client();
        $this->domain = $domain;
        $this->accessToken = $accessToken;
    }

    public static function make(string $domain, string $accessToken): self
    {
        return new self($domain, $accessToken);
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
        $response = $this->client->get($this->domain . '/services/data', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ]
        ]);

        $versions = json_decode($response->getBody(), true);

        if (!empty($versions)) {
            // Sort the versions in descending order and get the first one
            usort($versions, function ($a, $b) {
                return version_compare($b['version'], $a['version']);
            });

            return $versions[0]['version'];
        } else {
            throw new \RuntimeException('Unable to retrieve Salesforce API versions.');
        }
    }


    public function getCustomField(): array
    {
        $response = $this->client->get("{$this->domain}/services/data/v{$this->apiVersion}/sobjects/{$this->type}/describe", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ]
        ]);

        $contactMetadata = json_decode($response->getBody());

        return array_reduce($contactMetadata->fields, function ($customFieldDetails, $field) {
            if ($field->custom) {
                $customFieldDetails[$field->name] = $field->label;
            }
            return $customFieldDetails;
        }, []);
    }
}
