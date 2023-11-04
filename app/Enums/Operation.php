<?php

namespace App\Enums;

enum Operation: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public static function all(): array
    {
        return [
            self::PENDING->value,
            self::IN_PROGRESS->value,
            self::COMPLETED->value,
            self::FAILED->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }
}
