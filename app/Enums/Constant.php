<?php

namespace App\Enums;

class Constant
{
    /**
     * Status of apps
     */
    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * Types of apps
     */
    const FIRST_APP = 'first';
    const SECOND_APP = 'second';

    /**
     * App names
     */
    const SALESFORCE = 'Salesforce';
    const MAILCHIMP = 'Mailchimp';
    // others apps here...

    /**
     * App code
     */
    const APP_CODE = [
        self::SALESFORCE => 'salesforce',
        self::MAILCHIMP => 'mailchimp',
        // others apps code here...
    ];
}
