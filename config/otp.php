<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Default Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default otp "userProvider" for your application.
    | You may change this option, but it's a perfect start fot most applications.
    |
    */
    'default_provider' => 'users',

    /*
     |--------------------------------------------------------------------------
     | User Providers
     |--------------------------------------------------------------------------
     |
     | Here you should specify your user providers. This defines how the users are actually retrieved out of your
     | database or other storage mechanisms used by this application to persist your user's data.
     |
     */
    'user_providers'   => [
        'users' => [
            'table'      => 'users',
            'model'      => App\Models\User::class,
        ],
        'doctors' => [
            'table'      => 'doctors',
            'model'      => App\Models\Doctor::class,
        ],
        'managers' => [
            'table'      => 'managers',
            'model'      => App\Models\Manager::class,
        ],
        'secretaries' => [
            'table'      => 'secretaries',
            'model'      => App\Models\Secretary::class,
        ],
        'medical_centers' => [
            'table'      => 'medical_centers',
            'model'      => App\Models\MedicalCenter::class,
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Default Mobile Column
     |--------------------------------------------------------------------------
     |
     | Here you should specify name of your column (in users table) which user
     | mobile number reside in.
     |
     */
    'mobile_column'    => 'mobile',

    /*
     |--------------------------------------------------------------------------
     | Default OTP Tokens Table Name
     |--------------------------------------------------------------------------
     |
     | Here you should specify name of your OTP tokens table in database.
     | This table will held all information about created OTP tokens for users.
     |
     */
    'token_table'      => 'otp_tokens',

    /*
     |--------------------------------------------------------------------------
     | Verification Token Length
     |--------------------------------------------------------------------------
     |
     | Here you can specify length of OTP tokens which will send to users.
     |
     */
    'token_length'     => env('OTP_TOKEN_LENGTH', 4),

    /*
     |--------------------------------------------------------------------------
     | Verification Token Lifetime
     |--------------------------------------------------------------------------
     |
     | Here you can specify lifetime of OTP tokens (in minutes) which will send to users.
     |
     */
    'token_lifetime'   => env('OTP_TOKEN_LIFE_TIME', 2),

    /*
   |--------------------------------------------------------------------------
   | OTP Prefix
   |--------------------------------------------------------------------------
   |
   | Here you can specify prefix of OTP tokens for adding to cache.
   |
   */
    'prefix'           => 'otp_',

    /*
     |--------------------------------------------------------------------------
     | SMS Client (REQUIRED)
     |--------------------------------------------------------------------------
     |
     | Here you should specify your implemented "SMS Client" class. This class is responsible
     | for sending SMS to users. You may use your own sms channel, so this is not a required option anymore.
     |
     */
    'sms_client'       => '',

    /*
    |--------------------------------------------------------------------------
    |  Token Storage Driver
    |--------------------------------------------------------------------------
    |
    | Here you may define token "storage" driver. If you choose the "cache", the token will be stored
    | in a cache driver configured by your application. Otherwise, a table will be created for storing tokens.
    |
    | Supported drivers: "cache", "database"
    |
    */
    'token_storage'    => env('OTP_TOKEN_STORAGE', 'database'),
];
