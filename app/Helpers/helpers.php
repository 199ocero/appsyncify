<?php

use App\Enums\Constant;
use App\Models\Integration;

if (!function_exists('getTokenId')) {
    function getTokenId(string $type, Integration $integration): int | null
    {
        if ($type == Constant::FIRST_APP) {
            return $integration->first_app_token_id;
        }

        return $integration->second_app_token_id;
    }
}

if (!function_exists('getSettings')) {
    function getSettings(string $type, Integration $integration): array | null
    {
        if ($type == Constant::FIRST_APP) {
            return json_decode($integration->first_app_settings, true);
        }

        return json_decode($integration->second_app_settings, true);
    }
}
