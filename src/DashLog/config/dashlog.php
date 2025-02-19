<?php
return [
    /*
    |--------------------------------------------------------------------------
    | DashLog Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('DASHLOG_ENABLED', true),

    'storage' => [
        'driver' => env('DASHLOG_STORAGE_DRIVER', 'mysql'),
        
        'drivers' => [
            'mysql' => [
                'connection' => env('DB_CONNECTION', 'mysql'),
                'table' => 'request_logs',
            ],
            
            'elasticsearch' => [
                'host' => env('ELASTICSEARCH_HOST', 'localhost:9200'),
                'index' => env('ELASTICSEARCH_INDEX', 'request_logs'),
            ],
            
            'mongodb' => [
                'host' => env('MONGODB_HOST', 'mongodb://localhost:27017'),
                'database' => env('MONGODB_DATABASE', 'dashlog'),
                'collection' => 'request_logs',
            ],
            
            'redis' => [
                'connection' => env('REDIS_CONNECTION', 'default'),
                'key_prefix' => 'dashlog:logs:',
                'ttl' => 604800, // 7 days
            ],
        ],
    ],

    'logging' => [
        'fields' => [
            'request_body' => env('DASHLOG_LOG_REQUEST_BODY', true),
            'response_body' => env('DASHLOG_LOG_RESPONSE_BODY', true),
            'headers' => env('DASHLOG_LOG_HEADERS', false),
            'cookies' => env('DASHLOG_LOG_COOKIES', false),
            'session' => env('DASHLOG_LOG_SESSION', false),
            'stack_trace' => env('DASHLOG_LOG_STACK_TRACE', true),
        ],
        // Sensitive data that should be hidden from the logs
        'sensitive_fields' => [
            'password',
            'password_confirmation',
            'credit_card',
        ],

        'max_body_size' => env('DASHLOG_MAX_BODY_SIZE', 64000), // bytes
        'stack_trace_limit' => env('DASHLOG_STACK_TRACE_LIMIT', 20), // lines
    ],
]; 