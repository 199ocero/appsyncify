<?php

namespace App\Enums;

enum Log: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case DEBUG = 'debug';
    case AUDIT = 'audit';
    case REQUEST = 'request';
    case RESPONSE = 'response';
    case SECURITY = 'security';
    case PERFORMANCE = 'performance';
    case CUSTOM = 'custom';

    public static function all(): array
    {
        return [
            self::INFO->value,
            self::WARNING->value,
            self::ERROR->value,
            self::DEBUG->value,
            self::AUDIT->value,
            self::REQUEST->value,
            self::RESPONSE->value,
            self::SECURITY->value,
            self::PERFORMANCE->value,
            self::CUSTOM->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::INFO => 'Info',
            self::WARNING => 'Warning',
            self::ERROR => 'Error',
            self::DEBUG => 'Debug',
            self::AUDIT => 'Audit',
            self::REQUEST => 'Request',
            self::RESPONSE => 'Response',
            self::SECURITY => 'Security',
            self::PERFORMANCE => 'Performance',
            self::CUSTOM => 'Custom',
        };
    }
}
