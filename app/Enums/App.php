<?php

namespace App\Enums;

enum App: string
{
    case SALESFORCE = 'salesforce';
    case MAILCHIMP = 'mailchimp';

    public static function all(): array
    {
        return [
            self::SALESFORCE->value,
            self::MAILCHIMP->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::SALESFORCE => 'Salesforce',
            self::MAILCHIMP => 'Mailchimp',
        };
    }
}
