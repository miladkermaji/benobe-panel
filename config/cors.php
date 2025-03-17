<?php

// config/cors.php
return [
    'paths'                => ['api/*'],
    'allowed_methods'      => ['*'],
    'allowed_origins'      => ['*'], // یا دامنه‌های خاص مثل 'http://localhost:3000'
    'allowed_headers'      => ['*'],
    'exposed_headers'      => [],
    'max_age'              => 0,
    'supports_credentials' => false,
];

