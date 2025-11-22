<?php

return [
<<<<<<< HEAD
=======

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

>>>>>>> 7ec9b89e4bab8bf781093f170c6dbda7f0d47387
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

<<<<<<< HEAD
    // Allow the Vite dev server and local backend
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173')),

    'allowed_origins_patterns' => [],
=======
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
        'https://ohsansi.vercel.app',
    ],

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
    ],
>>>>>>> 7ec9b89e4bab8bf781093f170c6dbda7f0d47387

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

<<<<<<< HEAD
    // Allow cookies (credentials)
    'max_age' => 0,

    'supports_credentials' => true,
];
=======
    'max_age' => 0,

    'supports_credentials' => false,

];

>>>>>>> 7ec9b89e4bab8bf781093f170c6dbda7f0d47387
