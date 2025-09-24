<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    */
    
    'enabled' => env('AI_ENABLED', true),
    
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-pro'),
        'timeout' => env('GEMINI_TIMEOUT', 30),
        'max_retries' => env('GEMINI_MAX_RETRIES', 3),
    ],
    
    'classification' => [
        'algorithm_version' => '2.0',
        'confidence_threshold' => 0.7,
        'weights' => [
            'age_factor' => 0.25,
            'severity_score' => 0.40,
            'specialty_urgency' => 0.20,
            'symptoms_criticality' => 0.15,
        ],
        'red_threshold' => 0.7,
        'green_threshold' => 0.3,
    ],
    
    'processing' => [
        'max_processing_time' => 10, // seconds
        'batch_size' => 10,
        'queue_enabled' => true,
        'cache_results' => true,
        'cache_ttl' => 3600, // seconds
    ],
    
    'learning' => [
        'enabled' => env('AI_LEARNING_ENABLED', true),
        'feedback_weight' => 0.1,
        'auto_retrain' => env('AI_AUTO_RETRAIN', false),
        'retrain_threshold' => 100, // new feedback entries
    ],
    
    'monitoring' => [
        'log_predictions' => true,
        'track_accuracy' => true,
        'alert_on_low_accuracy' => true,
        'accuracy_threshold' => 0.85,
    ],
    
    'document_processing' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
        'ocr_enabled' => env('OCR_ENABLED', true),
        'tesseract_path' => env('TESSERACT_PATH', '/usr/bin/tesseract'),
    ],
];