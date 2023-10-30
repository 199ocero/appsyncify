<?php

namespace App\Services;

class DataSynchronizer
{
    protected string $first_app_code;
    public function __construct()
    {
    }
    public static function make(): self
    {
        return new self();
    }
}
