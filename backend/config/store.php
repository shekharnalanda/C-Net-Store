<?php

return [
    'cod_enabled' => filter_var(env('COD_ENABLED', false), FILTER_VALIDATE_BOOL),
    'launch_city' => env('STORE_CITY', 'Bihar Sharif'),
    'currency' => 'INR',
    'payment_provider' => 'razorpay',
];

