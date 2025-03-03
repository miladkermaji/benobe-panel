<?php

return [
    'default_gateway' => env('PAYMENT_GATEWAY', 'zarinpal'), // درگاه پیش‌فرض

    'gateways' => [
        'zarinpal' => [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),
            'sandbox' => env('ZARINPAL_SANDBOX', true),
        ],
        'idpay' => [
            'api_key' => env('IDPAY_API_KEY', ''),
            'sandbox' => env('IDPAY_SANDBOX', true),
        ],
        'payir' => [
            'api_key' => env('PAYIR_API_KEY', ''),
        ],
    ],
];
