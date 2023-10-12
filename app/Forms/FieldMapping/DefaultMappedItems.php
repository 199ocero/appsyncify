<?php

namespace App\Forms\FieldMapping;

class DefaultMappedItems
{
    const salesforce_mailchimp = [
        'FIRST_APP' => [
            'Email',
            'First Name',
            'Last Name'
        ],
        'SECOND_APP' => [
            'Email',
            'First Name',
            'Last Name'
        ],
        'FIRST_APP_LOGO' => 'images/logo/salesforce.svg',
        'SECOND_APP_LOGO' => 'images/logo/mailchimp.svg',
        'COUNT' => 3
    ];

    public static $mappedItems = [
        'salesforce_mailchimp' => self::salesforce_mailchimp
    ];
}
