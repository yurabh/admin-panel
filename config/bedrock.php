<?php
return [
    'region' => env('AWS_BEDROCK_REGION', 'us-east-1'),
    'api_key' => env('AWS_BEDROCK_KEY'),
    'secret_key' => env('AWS_BEDROCK_SECRET'),
    'tasks' => [
        'security' => ['max_tokens' => 500],
        'parse' => ['max_tokens' => 1000],
        'transform' => ['max_tokens' => 2000],
        'ranking' => ['max_tokens' => 500],
    ]
];
