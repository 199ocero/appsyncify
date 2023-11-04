<?php

namespace App\Enums;

enum Actor: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case SYSTEM = 'system';

    public static function all(): array
    {
        return [
            self::USER->value,
            self::ADMIN->value,
            self::SYSTEM->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::USER => 'User',
            self::ADMIN => 'Admin',
            self::SYSTEM => 'System',
        };
    }
}
