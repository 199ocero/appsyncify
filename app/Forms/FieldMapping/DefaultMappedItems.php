<?php

namespace App\Forms\FieldMapping;

use App\Enums\Constant;

class DefaultMappedItems
{
    const salesforce_mailchimp = [
        'FIRST_APP_NAME' => Constant::SALESFORCE,
        'SECOND_APP_NAME' => Constant::MAILCHIMP,
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
        'FIRST_APP_LOGO' => 'images/logo/salesforce.svg',
        'SECOND_APP_LOGO' => 'images/logo/mailchimp.svg',
        'COUNT' => 3
    ];

    public static $mappedItems = [
        'salesforce_mailchimp' => self::salesforce_mailchimp
    ];
}
