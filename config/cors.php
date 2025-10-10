<?php

return [
    'paths' => ['api/*', 'login', 'logout', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost',
        'https://localhost',
        'capacitor://localhost',
        'http://localhost:8100',
        'http://127.0.0.1:8100',
        'http://192.168.140.233:8100',
        'ionic://localhost',
        'http://10.0.2.2', 
        'https://aplicacion.merlinrod.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];

