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
