<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
    |--------------------------------------------------------------------------
    | API Path
    |--------------------------------------------------------------------------
    |
    | Path where the API documentation will be available.
    | Default: /docs/api
    |
    */
    'path' => 'docs/api',

    /*
    |--------------------------------------------------------------------------
    | API Info
    |--------------------------------------------------------------------------
    |
    | Information about your API that will be displayed in the documentation.
    |
    */
    'info' => [
        'title' => 'Groupe Ka Library API',
        'description' => 'API for managing books, users, and authentication in Groupe Ka digital library',
        'version' => '1.0.0',
        'contact' => [
            'name' => 'Groupe Ka Support',
            'email' => 'support@groupeka.com',
            'url' => 'https://groupeka.com',
        ],
        'license' => [
            'name' => 'Private',
            'url' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Servers
    |--------------------------------------------------------------------------
    |
    | API servers for different environments.
    |
    */
    'servers' => [
        [
            'url' => env('APP_URL', 'http://localhost:8000'),
            'description' => env('APP_ENV') === 'production' ? 'Production' : 'Local Development',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Domain
    |--------------------------------------------------------------------------
    |
    | By default, Scramble docs UI will only be available in local environment.
    | Set to null to make it available in all environments.
    |
    */
    'middleware' => [
        'web',
        // Uncomment to restrict access in production
        // RestrictedDocsAccess::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes to Document
    |--------------------------------------------------------------------------
    |
    | Define which routes should be included in the documentation.
    |
    */
    'api_domain' => null,
    'api_path' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Security Schemes
    |--------------------------------------------------------------------------
    |
    | Define authentication methods used in your API.
    | Routes with @authenticated annotation will require this security scheme.
    |
    */
    'security_schemes' => [
        'sanctum' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'token',
            'description' => 'Laravel Sanctum token authentication. Obtain token from /api/auth/login or /api/auth/register',
            'name' => 'Authorization',
            'in' => 'header',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Security
    |--------------------------------------------------------------------------
    |
    | Automatically apply security scheme to routes with auth middleware.
    |
    */
    'default_security' => [],

    /*
    |--------------------------------------------------------------------------
    | Tags
    |--------------------------------------------------------------------------
    |
    | Group endpoints by tags for better organization.
    | Scramble will auto-detect tags from route groups.
    |
    */
    'tags' => [
        [
            'name' => 'Admin - Users',
            'description' => 'Admin endpoints for user management, role assignment, and account operations',
        ],
        [
            'name' => 'Admin - Audits',
            'description' => 'View audit logs, activity logs, security events, and statistics',
        ],
        [
            'name' => 'Books',
            'description' => 'Browse, search, and purchase books (coming soon)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Extensions
    |--------------------------------------------------------------------------
    |
    | Additional OpenAPI extensions.
    |
    */
    'extensions' => [
        'x-logo' => [
            'url' => '/images/logo.png',
            'altText' => 'Groupe Ka Library',
        ],
    ],
];