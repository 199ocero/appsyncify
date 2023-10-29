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
     * Types of Logs
     */
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const DEBUG = 'debug';
    const AUDIT = 'audit';
    const REQUEST = 'request';
    const RESPONSE = 'response';
    const SECURITY = 'security';
    const PERFORMANCE = 'performance';
    const CUSTOM = 'custom';

    /**
     * All Types of Logs
     */
    const ALL_LOG_TYPES = [
        self::INFO,
        self::WARNING,
        self::ERROR,
        self::DEBUG,
        self::AUDIT,
        self::REQUEST,
        self::RESPONSE,
        self::SECURITY,
        self::PERFORMANCE,
        self::CUSTOM
    ];

    /**
     * Types of actor
     */
    const USER = 'user';
    const ADMIN = 'admin';
    const SYSTEM = 'system';

    /**
     * All Types of actor
     */
    const ALL_ACTOR_TYPES = [
        self::USER,
        self::ADMIN,
        self::SYSTEM
    ];

    /**
     * Types of operation
     */
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * All Types of operation
     */
    const ALL_OPERATION_STATUS = [
        self::STATUS_PENDING,
        self::STATUS_RUNNING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED
    ];

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
