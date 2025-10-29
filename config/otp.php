<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for OTP functionality.
    |
    */

    // OTP length (number of digits)
    'length' => env('OTP_LENGTH', 4),
    
    // OTP expiry time in minutes
    'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 5),
    
    // SMS message template (use {otp} and {minutes} as placeholders)
    'message_template' => env('OTP_MESSAGE_TEMPLATE', 'Your OTP code is: {otp}. Valid for {minutes} minutes.'),
    
    /*
    |--------------------------------------------------------------------------
    | Test Cases
    |--------------------------------------------------------------------------
    |
    | Define test mobile numbers and their corresponding OTPs for development.
    | These will bypass the actual SMS sending and OTP generation.
    |
    */
    'test_cases' => [
        '+962795970357' => '2025',
        // Add more test cases as needed
        // '+1234567890' => '1234',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for OTP requests per mobile number.
    |
    */
    'rate_limit' => [
        'enabled' => env('OTP_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('OTP_RATE_LIMIT_MAX_ATTEMPTS', 5),
        'decay_minutes' => env('OTP_RATE_LIMIT_DECAY_MINUTES', 60),
    ],
];