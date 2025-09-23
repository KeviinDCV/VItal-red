<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    */
    
    'enabled' => env('WEBSOCKET_ENABLED', true),
    
    'host' => env('WEBSOCKET_HOST', '127.0.0.1'),
    'port' => env('WEBSOCKET_PORT', 6001),
    
    'ssl' => [
        'enabled' => env('WEBSOCKET_SSL_ENABLED', false),
        'cert' => env('WEBSOCKET_SSL_CERT'),
        'key' => env('WEBSOCKET_SSL_KEY'),
    ],
    
    'channels' => [
        'executive-metrics' => [
            'roles' => ['administrador', 'jefe_urgencias'],
            'update_interval' => 30, // seconds
        ],
        'critical-alerts' => [
            'roles' => ['administrador', 'medico', 'jefe_urgencias'],
            'update_interval' => 5, // seconds
        ],
        'notifications' => [
            'roles' => ['administrador', 'medico', 'ips'],
            'update_interval' => 10, // seconds
        ],
        'real-time-metrics' => [
            'roles' => ['administrador'],
            'update_interval' => 2, // seconds
        ],
    ],
    
    'connection_limits' => [
        'max_connections' => env('WEBSOCKET_MAX_CONNECTIONS', 1000),
        'max_per_user' => env('WEBSOCKET_MAX_PER_USER', 5),
        'timeout' => env('WEBSOCKET_TIMEOUT', 300), // seconds
    ],
    
    'heartbeat' => [
        'enabled' => true,
        'interval' => 30, // seconds
        'timeout' => 60, // seconds
    ],
    
    'logging' => [
        'enabled' => env('WEBSOCKET_LOGGING', true),
        'level' => env('WEBSOCKET_LOG_LEVEL', 'info'),
        'channel' => env('WEBSOCKET_LOG_CHANNEL', 'websocket'),
    ],
];