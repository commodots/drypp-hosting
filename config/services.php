<?php

return [
    'paystack' => [
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        // Restrict checkout to currencies enabled for this Paystack merchant account.
        'currencies' => array_values(array_filter(array_map('trim', explode(',', env('PAYSTACK_CURRENCIES', 'NGN,USD'))))),
    ],
    'porkbun' => [
        'api_key' => env('PORKBUN_API_KEY'),
        'secret_key' => env('PORKBUN_SECRET_KEY'),
        'sandbox' => env('PORKBUN_SANDBOX', true),
        'base_url' => env('PORKBUN_BASE_URL', 'https://api.porkbun.com/api/json/v3'),
        'markup_percent' => env('PORKBUN_MARKUP_PERCENT', 40),
    ],
    'verpex' => [
        'whm_host' => env('VERPEX_WHM_HOST'),
        'whm_username' => env('VERPEX_WHM_USERNAME'),
        'whm_api_token' => env('VERPEX_WHM_API_TOKEN'),
    ],
    'postmark' => ['key' => env('POSTMARK_API_KEY')],
    'resend' => ['key' => env('RESEND_API_KEY')],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
];
