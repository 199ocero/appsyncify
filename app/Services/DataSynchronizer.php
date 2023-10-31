<?php

namespace App\Services;

use App\Enums\Constant;
use App\Services\Context\BaseSynchronizer;

class DataSynchronizer
{
    protected $synchronizer;
    protected string $firstAppCode;
    protected string $secondAppCode;
    protected array $defaultFields;
    protected array $customFields;

    public function __construct(
        string $firstAppCode,
        string $secondAppCode,
        array $defaultFields = [],
        array $customFields = []
    ) {
        $this->synchronizer = app(BaseSynchronizer::class);
        $this->firstAppCode = $firstAppCode;
        $this->secondAppCode = $secondAppCode;
        $this->defaultFields = $defaultFields;
        $this->customFields = $customFields;
    }

    public static function make(): self
    {
        return new self(
            $firstAppCode,
            $secondAppCode,
            $defaultFields,
            $customFields
        );
    }

    public static function synchronizer(): void
    {
    }

    private function getSynchronizer()
    {
        return match ($this->firstAppCode . '_' . $this->secondAppCode) {
            Constant::APP_CODE_SYNCHRONIZER[Constant::SALESFORCE . '_' . Constant::MAILCHIMP] => $this->synchronizer,
            default => null
        };
    }
}
