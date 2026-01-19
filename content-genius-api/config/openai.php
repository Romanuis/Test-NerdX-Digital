<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API integration.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    'organization' => env('OPENAI_ORGANIZATION'),

    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),

    'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),

    'temperature' => env('OPENAI_TEMPERATURE', 0.7),

    'timeout' => env('OPENAI_TIMEOUT', 60),

    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
];
