<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration - Taksi-k
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for SMS gateway integration.
    | SMS Provider: JOSMS (Taksi-k Account)
    |
    */

    'sender_id' => env('SMS_SENDER_ID', 'Taksi-k'),
    'account_name' => env('SMS_ACCOUNT_NAME', 'taksik'),
    'account_password' => env('SMS_ACCOUNT_PASSWORD', 'pN0gM5yW5nI9zE7c'),
    'base_url' => env('SMS_BASE_URL', 'https://www.josms.net/SMSServices/Clients/Prof/RestSingleSMS/SendSMS'),
    
    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Provider
    |--------------------------------------------------------------------------
    |
    | Currently using: JOSMS platform for Taksi-k
    |
    */
    'provider' => env('SMS_PROVIDER', 'josms'),
    
    /*
    |--------------------------------------------------------------------------
    | Additional Gateway URLs
    |--------------------------------------------------------------------------
    |
    | Other available gateways for different message types
    |
    */
    'gateways' => [
        'otp' => env('SMS_GATEWAY_OTP', 'https://www.josms.net/SMSServices/Clients/Prof/RestSingleSMS/SendSMS'),
        'general' => env('SMS_GATEWAY_GENERAL', 'https://www.josms.net/SMSServices/Clients/Prof/RestSingleSMS_General/SendSMS'),
        'bulk' => env('SMS_GATEWAY_BULK', 'https://www.josms.net/sms/api/SendBulkMessages.cfm'),
        'balance' => env('SMS_GATEWAY_BALANCE', 'https://www.josms.net/SMS/API/GetBalance'),
    ],
];