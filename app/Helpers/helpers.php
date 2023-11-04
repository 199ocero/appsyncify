<?php

use App\Enums\AppType;
use App\Models\Integration;

if (!function_exists('getTokenId')) {
    function getTokenId(string $type, Integration $integration): int | null
    {
        if ($type == getEnumValue(AppType::FIRST_APP)) {
            return $integration->first_app_token_id;
        }

        return $integration->second_app_token_id;
    }
}

if (!function_exists('getSettings')) {
    function getSettings(string $type, Integration $integration): array | null
    {
        if ($type == getEnumValue(AppType::FIRST_APP)) {
            return json_decode($integration->first_app_settings, true);
        }

        return json_decode($integration->second_app_settings, true);
    }
}

if (!function_exists('getDaysArrangement')) {
    function getDaysArrangement(array $days): array
    {
        $dayToNumber = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];

        // Sort the days based on their numeric values.
        usort($days, function ($a, $b) use ($dayToNumber) {
            return $dayToNumber[strtolower($a)] - $dayToNumber[strtolower($b)];
        });

        return $days;
    }
}

if (!function_exists('getEnumValue')) {
    function getEnumValue($enum): string | int
    {
        return $enum->value;
    }
}
