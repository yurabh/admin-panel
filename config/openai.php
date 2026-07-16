<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),
    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

    'models' => [
        'parsing' => env('OPENAI_PARSING_MODEL', 'gpt-4o-mini'),
        'classification' => env('OPENAI_CLASSIFICATION_MODEL', 'gpt-4o-mini'),
        'transform' => env('OPENAI_TRANSFORM_MODEL', 'gpt-4o'),
        'security' => env('OPENAI_SECURITY_MODEL', 'gpt-4o-mini'),
    ],

    'tasks' => [
        'parsing' => [
            'max_tokens' => 200,
        ],
        'classification' => [
            'max_tokens' => 1000,
        ],
        'transform' => [
            'max_tokens' => 4000,
        ],
        'security' => [
            'max_tokens' => 500,
        ],
    ],
];

