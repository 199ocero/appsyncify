<?php

namespace App\Enums;

enum Status: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public static function all(): array
    {
        return [
            self::ACTIVE->value,
            self::INACTIVE->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }
}
