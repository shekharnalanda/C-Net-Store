<?php

return [
    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    'mci_central' => [
        'enabled' => filter_var(env('MCI_CENTRAL_SYNC_ENABLED', false), FILTER_VALIDATE_BOOL),
        'url' => rtrim((string) env('MCI_CENTRAL_URL', 'https://mciedu.in'), '/'),
        'token' => env('MCI_CENTRAL_TOKEN'),
        'business_code' => env('MCI_CENTRAL_BUSINESS_CODE', 'c-net-store'),
        'timeout' => (int) env('MCI_CENTRAL_TIMEOUT', 10),
    ],
];
