<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Knapp API Settings
    |--------------------------------------------------------------------------
    |
    | Aqui você pode configurar as credenciais e URLs para a API Knapp WorkOrderInquiry.
    | É altamente recomendável usar variáveis de ambiente (.env) para armazenar
    | informações sensíveis como client_id e client_secret.
    |
    */

    'client_id' => env('KNAPP_API_CLIENT_ID'),

    'client_secret' => env('KNAPP_API_CLIENT_SECRET'),

    'scope' => env('KNAPP_API_SCOPE'),

    'token_url' => env('KNAPP_API_TOKEN_URL'),

    'api_base_url' => env('KNAPP_API_BASE_URL'),

    'http_timeout' => env('KNAPP_API_HTTP_TIMEOUT', 30),

    'cache' => [
        'token_key' => env('KNAPP_API_CACHE_TOKEN_KEY', 'knapp_api_access_token'),
        'ttl' => env('KNAPP_API_CACHE_TTL', 3300), // Em segundos (55 minutos)
    ],
];

