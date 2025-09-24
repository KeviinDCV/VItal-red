<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    // Integraciones Hospitalarias
    'his' => [
        'base_url' => env('HIS_BASE_URL', 'https://his-api.hospital.com'),
        'api_key' => env('HIS_API_KEY'),
        'timeout' => env('HIS_TIMEOUT', 30),
    ],

    'lab' => [
        'base_url' => env('LAB_BASE_URL', 'https://lab-api.hospital.com'),
        'api_key' => env('LAB_API_KEY'),
        'timeout' => env('LAB_TIMEOUT', 30),
    ],

    'pacs' => [
        'base_url' => env('PACS_BASE_URL', 'https://pacs-api.hospital.com'),
        'api_key' => env('PACS_API_KEY'),
        'timeout' => env('PACS_TIMEOUT', 60),
    ],

    // Gemini AI Service
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
    ],

];