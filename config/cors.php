<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    // Apply CORS handling to these paths
    'paths' => [
        'api/*',
        'broadcasting/auth',
        // Also allow web routes so Inertia/SPA navigations work if absolute URLs slip in
        '*',
    ],

    // Allow all HTTP methods by default
    'allowed_methods' => ['*'],

    // Explicitly allow local dev origins; adjust for your environment as needed
    'allowed_origins' => [
        '*',
        'http://127.0.0.1:8000',
        'http://localhost:8000',
        // Vite dev server origins (IPv4, IPv6 localhost)
        'http://127.0.0.1:5173',
        'http://localhost:5173',
        'http://[::1]:5173',
        'http://192.168.1.18:8000'
    ],

    'allowed_origins_patterns' => [
        // Optional: allow any local dev origin with any port
        '*',
        '/^https?:\/\/(localhost|127\.0\.0\.1|\[::1\])(\:\d+)?$/',
    ],

    // Allow common headers
    'allowed_headers' => ['*'],

    // Expose no custom headers by default
    'exposed_headers' => [],

    // Cache preflight results
    'max_age' => 3600,

    // Support cookies / session for cross-origin when needed
    'supports_credentials' => true,
];
