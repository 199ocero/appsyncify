<?php

namespace App\Forms\FieldMapping;

use App\Enums\App;
use App\Enums\Combination;

class DefaultMappedItems
{
    private static function getSalesforceMailchimpMappedItems()
    {
        return [
            'FIRST_APP_NAME' => App::SALESFORCE->label(),
            'SECOND_APP_NAME' => App::MAILCHIMP->label(),
            'FIRST_APP_FIELDS' => [
                'Email' => 'Email',
                'FirstName' => 'First Name',
                'LastName' => 'Last Name'
            ],
            'SECOND_APP_FIELDS' => [
                'email_address' => 'Email',
                'FNAME' => 'First Name',
                'LNAME' => 'Last Name'
            ],
            'DIRECTION' => [
                'right',
                'right',
                'right'
            ],
            'FIRST_APP_LOGO' => 'images/logo/salesforce.svg',
            'SECOND_APP_LOGO' => 'images/logo/mailchimp.svg',
            'COUNT' => 3
        ];
    }

    public static function make(string $combination)
    {
        return match ($combination) {
            getEnumValue(Combination::SALESFORCE_MAILCHIMP) => self::getSalesforceMailchimpMappedItems(),
            default => throw new \Exception('Invalid combination', 500),
        };
    }
}
