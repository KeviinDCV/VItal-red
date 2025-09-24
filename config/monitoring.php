<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Monitoring Configuration
    |--------------------------------------------------------------------------
    */
    
    'enabled' => env('MONITORING_ENABLED', true),
    
    'metrics' => [
        'collection_interval' => env('METRICS_INTERVAL', 60), // seconds
        'retention_days' => env('METRICS_RETENTION', 30),
        'batch_size' => env('METRICS_BATCH_SIZE', 100),
    ],
    
    'performance' => [
        'response_time_threshold' => 2000, // milliseconds
        'memory_threshold' => 80, // percentage
        'cpu_threshold' => 80, // percentage
        'disk_threshold' => 90, // percentage
    ],
    
    'alerts' => [
        'enabled' => true,
        'channels' => ['database', 'email', 'websocket'],
        'escalation_time' => 900, // 15 minutes
        'max_alerts_per_hour' => 10,
    ],
    
    'health_checks' => [
        'database' => [
            'enabled' => true,
            'timeout' => 5,
            'critical_threshold' => 10, // seconds
        ],
        'cache' => [
            'enabled' => true,
            'timeout' => 2,
            'critical_threshold' => 5, // seconds
        ],
        'queue' => [
            'enabled' => true,
            'max_failed_jobs' => 10,
            'max_queue_size' => 1000,
        ],
        'storage' => [
            'enabled' => true,
            'paths' => [
                storage_path(),
                public_path(),
            ],
        ],
    ],
    
    'logging' => [
        'enabled' => true,
        'level' => env('MONITORING_LOG_LEVEL', 'info'),
        'channel' => env('MONITORING_LOG_CHANNEL', 'monitoring'),
        'structured' => true,
    ],
    
    'external_services' => [
        'gemini_api' => [
            'enabled' => true,
            'timeout' => 30,
            'health_endpoint' => null,
        ],
        'email_service' => [
            'enabled' => true,
            'timeout' => 10,
        ],
    ],
];