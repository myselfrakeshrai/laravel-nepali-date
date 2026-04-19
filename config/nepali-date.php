<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Format
    |--------------------------------------------------------------------------
    |
    | Used by bs_date() helper and converter->toString() if no format is passed.
    |
    */
    'default_format' => 'Y-m-d',

    /*
    |--------------------------------------------------------------------------
    | Automatic AD -> BS Conversion On HTML Responses
    |--------------------------------------------------------------------------
    |
    | If true, package middleware scans HTML responses and converts date strings
    | that look like YYYY-MM-DD (and with / or . separators) into BS format.
    |
    */
    'auto_convert_response_dates' => false,

    /*
    |--------------------------------------------------------------------------
    | Auto Conversion Output Format
    |--------------------------------------------------------------------------
    */
    'auto_convert_format' => 'Y-m-d',

    /*
    |--------------------------------------------------------------------------
    | Auto Register Middleware In web Group
    |--------------------------------------------------------------------------
    |
    | If true, middleware is automatically pushed to the web middleware group.
    | If false, register middleware manually using alias: auto.bs.date
    |
    */
    'auto_register_web_middleware' => true,
];
