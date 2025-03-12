<?php

use function Knuckles\Scribe\Config\configureStrategy;

use Knuckles\Scribe\Config\AuthIn;
use Knuckles\Scribe\Config\Defaults;use Knuckles\Scribe\Extracting\Strategies;

return [
    /*
    |--------------------------------------------------------------------------
    | General Information
    |--------------------------------------------------------------------------
    |
    | Configuration for API documentation, including title, description,
    | and base URL.
    |
    */

    'title'                           => config('app.name') . ' API Documentation',
    'description'                     => 'Official API documentation for your project, including authentication, user requests, and other operations.',
    'base_url'                        => config("app.url"),

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Define which API routes should be included in the documentation.
    |
    */

    'routes'                          => [
        [
            'match'   => [
                'prefixes' => ['api/auth/*'],
                'domains'  => ['*'],
            ],
            'include' => [],
            'exclude' => ['api/sendotp*'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Type
    |--------------------------------------------------------------------------
    |
    | Choose how the API documentation should be generated and displayed.
    | Options:
    | - "static" generates a static HTML page in /public/docs
    | - "laravel" generates Blade views for dynamic routing
    |
    */

    'type'                            => 'laravel',
    'theme'                           => 'default',

    'static'                          => [
        'output_path' => 'public/docs',
    ],

    'laravel'                         => [
        'add_routes'       => true,
        'docs_url'         => '/docs',
        'assets_directory' => null,
        'middleware'       => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | "Try It Out" Button
    |--------------------------------------------------------------------------
    |
    | Enables a "Try It Out" button that allows users to test API endpoints.
    |
    */

    'try_it_out'                      => [
        'enabled'  => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Specify how API authentication works, including token type and headers.
    |
    */

    'auth'                            => [
        'enabled'     => true,
        'default'     => true,
        'in'          => AuthIn::BEARER->value,
        'name'        => 'Authorization',
        'use_value'   => 'Bearer {YOUR_AUTH_TOKEN}',
        'placeholder' => '{YOUR_AUTH_TOKEN}',
        'extra_info'  => 'To obtain your token, log in to your panel and generate an API token.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Introduction
    |--------------------------------------------------------------------------
    |
    | Text that appears at the beginning of the API documentation.
    |
    */

    'intro_text'                      => <<<INTRO
        This documentation provides all the necessary information to work with our API.

        <aside>On the right side, you will see request examples in different programming languages.</aside>
    INTRO,

    /*
    |--------------------------------------------------------------------------
    | Example Request Languages
    |--------------------------------------------------------------------------
    |
    | Specify which programming languages should be shown in examples.
    |
    */

    'example_languages'               =>['bash','javascript','php','python'],

    /*
    |--------------------------------------------------------------------------
    | Postman Collection
    |--------------------------------------------------------------------------
    |
    | Generate a Postman collection for API testing.
    |
    */

    'postman'                         =>['enabled'=>true],

    /*
    |--------------------------------------------------------------------------
    | OpenAPI Specification
    |--------------------------------------------------------------------------
    |
    | Generate an OpenAPI specification (v3.0.1).
    |
    */

    'openapi'                         =>['enabled'=>true],

    /*
    |--------------------------------------------------------------------------
    | API Groups & Sorting
    |--------------------------------------------------------------------------
    |
    | Define categories for API endpoints and control their order.
    |
    */

    'groups'                          =>[
        'default'=>'Endpoints',
        'order'  =>[
            'Authentication',
            'Appointments',
            'User Management',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | "Last Updated" Information
    |--------------------------------------------------------------------------
    |
    | Displays the last update date in the documentation.
    |
    */

    'last_updated'                    =>'Last updated: {date:F j, Y}',

    /*
    |--------------------------------------------------------------------------
    | Example Data Generation
    |--------------------------------------------------------------------------
    |
    | Configure how example data is generated for API requests and responses.
    |
    */

'examples' => [
    'faker_seed' => 1234,

    // Override default example values
    'override' => [
        'mobile' => '09181234567',  // Use a valid phone number
        'otpCode' => '1234',        // Use a realistic OTP format
        'token' => 'sample-token',  // Use a readable token
    ],
],




    /*
    |--------------------------------------------------------------------------
    | Extraction Strategies
    |--------------------------------------------------------------------------
    |
    | Define how Scribe extracts information from your routes.
    |
    */

    'strategies'                      =>[
        'metadata'       =>[...Defaults::METADATA_STRATEGIES],
        'headers'        =>[
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data:[
                'Content-Type'=>'application/json',
                'Accept'      =>'application/json',
            ]),
        ],
        'urlParameters'  =>[...Defaults::URL_PARAMETERS_STRATEGIES],
        'queryParameters'=>[...Defaults::QUERY_PARAMETERS_STRATEGIES],
        'bodyParameters' =>[...Defaults::BODY_PARAMETERS_STRATEGIES],
        'responses'      =>configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(
                only:['GET *'],
                config:['app.debug'=>false]
            )
        ),
        'responseFields' =>[...Defaults::RESPONSE_FIELDS_STRATEGIES],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | Specify which database connections should use transactions.
    |
    */

    'database_connections_to_transact'=>[config('database.default')],
];
