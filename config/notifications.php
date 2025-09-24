<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notifications Configuration
    |--------------------------------------------------------------------------
    */
    
    'enabled' => env('NOTIFICATIONS_ENABLED', true),
    
    'channels' => [
        'database' => [
            'enabled' => true,
            'table' => 'notificaciones',
        ],
        'websocket' => [
            'enabled' => env('WEBSOCKET_NOTIFICATIONS', true),
            'real_time' => true,
        ],
        'email' => [
            'enabled' => env('EMAIL_NOTIFICATIONS', true),
            'queue' => true,
            'templates_path' => 'emails.notifications',
        ],
        'sms' => [
            'enabled' => env('SMS_NOTIFICATIONS', false),
            'provider' => env('SMS_PROVIDER', 'twilio'),
            'queue' => true,
        ],
    ],
    
    'types' => [
        'critical_alert' => [
            'channels' => ['database', 'websocket', 'email'],
            'immediate' => true,
            'sound' => true,
            'escalation_time' => 900, // 15 minutes
        ],
        'case_timeout' => [
            'channels' => ['database', 'websocket', 'email'],
            'immediate' => true,
            'sound' => true,
        ],
        'automatic_response' => [
            'channels' => ['database', 'websocket'],
            'immediate' => false,
            'sound' => false,
        ],
        'system_alert' => [
            'channels' => ['database', 'websocket', 'email'],
            'immediate' => true,
            'sound' => false,
        ],
        'info' => [
            'channels' => ['database', 'websocket'],
            'immediate' => false,
            'sound' => false,
        ],
    ],
    
    'user_preferences' => [
        'default' => [
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'sound_enabled' => true,
            'critical_alerts_only' => false,
            'notification_frequency' => 'immediate',
        ],
        'roles' => [
            'administrador' => [
                'email_notifications' => true,
                'sms_notifications' => true,
                'critical_alerts_only' => false,
            ],
            'medico' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'critical_alerts_only' => true,
            ],
            'ips' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'critical_alerts_only' => false,
            ],
        ],
    ],
    
    'rate_limiting' => [
        'enabled' => true,
        'max_per_minute' => 10,
        'max_per_hour' => 100,
        'cooldown_period' => 300, // 5 minutes
    ],
    
    'cleanup' => [
        'enabled' => true,
        'retention_days' => 90,
        'batch_size' => 1000,
        'schedule' => 'daily',
    ],
];