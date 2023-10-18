<?php

namespace App\Services;

use MailchimpMarketing\ApiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class MailchimpApi
{
    protected $mailchimpApiClient;
    protected $accessToken;
    protected $region;

    public function __construct(string $accessToken, string $region)
    {
        $this->mailchimpApiClient = new ApiClient();
        $this->accessToken = Crypt::decryptString($accessToken);
        $this->region = $region;
    }

    public static function make(string $accessToken, string $region): self
    {
        return new self($accessToken, $region);
    }

    public function getAudience(int $audienceId): array
    {
        return Cache::remember($audienceId . '_mailchimp_audience', now()->addHour(), function () {
            $this->mailchimpApiClient->setConfig([
                'accessToken' => $this->accessToken,
                'server' => $this->region,
            ]);

            $audience = [];

            foreach ($this->mailchimpApiClient->lists->getAllLists()->lists as $list) {
                $audience[$list->id] = $list->name;
            }

            return $audience;
        });
    }

    public function getAudienceFields(string $audienceId): array
    {
        return Cache::remember($audienceId . '_mailchimp_audience_fields', now()->addHour(), function () use ($audienceId) {
            $this->mailchimpApiClient->setConfig([
                'accessToken' => $this->accessToken,
                'server' => $this->region,
            ]);

            $mergeFields = $this->mailchimpApiClient->lists->getListMergeFields($audienceId);

            $fields = [];

            foreach ($mergeFields->merge_fields as $field) {
                $fields[$field->tag] = $field->name;
            }

            return $fields;
        });
    }
}
