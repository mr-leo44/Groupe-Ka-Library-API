<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Status
    |--------------------------------------------------------------------------
    |
    | Enable/disable auditing globally.
    |
    */
    'enabled' => env('AUDITING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Audit Implementation
    |--------------------------------------------------------------------------
    */
    'implementation' => OwenIt\Auditing\Models\Audit::class,

    /*
    |--------------------------------------------------------------------------
    | User Morph prefix & Guards
    |--------------------------------------------------------------------------
    |
    | Define the morph prefix and authentication guards for the User resolver.
    |
    */
    'user' => [
        'morph_prefix' => 'user',
        'guards' => [
            'web',
            'api',
            'sanctum', // Important for API authentication
        ],
        'resolver' => OwenIt\Auditing\Resolvers\UserResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Resolvers
    |--------------------------------------------------------------------------
    */
    'resolvers' => [
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url' => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Events
    |--------------------------------------------------------------------------
    |
    | The Eloquent events that trigger an Audit.
    | For Groupe Ka Library: track created, updated, deleted, restored
    |
    */
    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
        // 'retrieved', // Enable if you want to track reads (can be noisy)
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, empty old/new values won't create an audit record.
    | Keep false for flexibility.
    |
    */
    'strict' => false,

    /*
    |--------------------------------------------------------------------------
    | Global Exclude
    |--------------------------------------------------------------------------
    |
    | Attributes to exclude from ALL models.
    | Never audit passwords, tokens, or sensitive data.
    |
    */
    'exclude' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Empty Values
    |--------------------------------------------------------------------------
    |
    | Should Audit records be stored when old_values & new_values are empty?
    |
    */
    'empty_values' => true,
    'allowed_empty_values' => [
        'retrieved',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Array Values
    |--------------------------------------------------------------------------
    |
    | Should array values be audited?
    | Set to true if you need to track JSON columns.
    |
    */
    'allowed_array_values' => true, // Enable for JSON tracking

    /*
    |--------------------------------------------------------------------------
    | Audit Timestamps
    |--------------------------------------------------------------------------
    |
    | Should created_at, updated_at, deleted_at be audited?
    | Usually not needed since audit has its own timestamps.
    |
    */
    'timestamps' => false,

    /*
    |--------------------------------------------------------------------------
    | Audit Threshold
    |--------------------------------------------------------------------------
    |
    | Maximum number of Audit records per model.
    | 0 = unlimited (recommended for security auditing)
    | Set a number if storage is a concern (e.g., 1000)
    |
    */
    'threshold' => 0, // Unlimited for compliance

    /*
    |--------------------------------------------------------------------------
    | Audit Driver
    |--------------------------------------------------------------------------
    */
    'driver' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Audit Driver Configurations
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'database' => [
            'table' => 'audits',
            'connection' => null, // Use default database connection
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Queue
    |--------------------------------------------------------------------------
    |
    | Enable queue to avoid performance impact on requests.
    | Recommended for production with high traffic.
    |
    */
    'queue' => [
        'enable' => env('AUDIT_QUEUE_ENABLED', false), // Enable in production
        'connection' => env('QUEUE_CONNECTION', 'database'),
        'queue' => 'audits',
        'delay' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Console
    |--------------------------------------------------------------------------
    |
    | Should console commands be audited?
    | Usually false to avoid noise from seeders/migrations.
    |
    */
    'console' => false,
];