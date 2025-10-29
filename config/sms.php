<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for SMS gateway integration.
    | Make sure to set the appropriate environment variables.
    |
    */

    'sender_id' => env('SMS_SENDER_ID', 'HabibaStore'),
    'account_name' => env('SMS_ACCOUNT_NAME', 'aliencode'),
    'account_password' => env('SMS_ACCOUNT_PASSWORD', 'jU0nH9pI6mD4vQ2s'),
    'base_url' => env('SMS_BASE_URL', 'https://www.josms.net/SMSServices/Clients/Prof/RestSingleSMS/SendSMS'),
    
    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Provider
    |--------------------------------------------------------------------------
    |
    | Currently supported: 'josms'
    | You can extend this to support multiple SMS providers
    |
    */
    'provider' => env('SMS_PROVIDER', 'josms'),
];