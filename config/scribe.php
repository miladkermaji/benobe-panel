<?php

use function Knuckles\Scribe\Config\configureStrategy;
use Knuckles\Scribe\Config\AuthIn;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Extracting\Strategies;

return [
    /*
    |-------------------------------------------------------------------------
    | اطلاعات کلی
    |-------------------------------------------------------------------------
    |
    | تنظیمات اصلی مستندات API، شامل عنوان، توضیحات و URL پایه.
    |
    */
    'title'                           => config('app.name') . ' API Documentation',
    'description'                     => 'مستندات رسمی API پروژه، شامل احراز هویت، درخواست‌های کاربر، مدیریت نوبت‌ها و سایر عملیات.',
    'base_url'                        => config('app.url'),

    /*
    |-------------------------------------------------------------------------
    | مسیرهای API
    |-------------------------------------------------------------------------
    |
    | تعریف مسیرهایی که باید در مستندات گنجانده شوند.
    |
    */
    'routes'                          => [
        [
            'match'   => [
                'prefixes' => ['api/auth/*', 'api/zone/*', 'api/appointments/*', 'api/sub_users/*', 'api/orders/*', 'api/wallet/*', 'api/doctors/*', 'api/menus/*'],
                'domains'  => ['*'],
            ],
            'include' => [],
            'exclude' => ['api/sendotp*'],
        ],
    ],

    'strategies'                      => [
        'responses' => configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(
                only: ['GET *', 'POST *'],
                config: ['app.debug' => false]
            )
        ),
    ],

    /*
    |-------------------------------------------------------------------------
    | نوع مستندات
    |-------------------------------------------------------------------------
    |
    | انتخاب روش تولید و نمایش مستندات.
    | - "static" برای تولید صفحه HTML ثابت در /public/docs
    | - "laravel" برای استفاده از Blade و روتینگ پویا
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
    |-------------------------------------------------------------------------
    | دکمه "Try It Out"
    |-------------------------------------------------------------------------
    |
    | فعال‌سازی دکمه "آزمایش" برای تست مستقیم API.
    |
    */
    'try_it_out'                      => [
        'enabled'  => true,
        'base_url' => null,
        'use_csrf' => false,
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    /*
    |-------------------------------------------------------------------------
    | تنظیمات احراز هویت
    |-------------------------------------------------------------------------
    |
    | مشخص کردن نحوه کار احراز هویت، شامل نوع توکن و هدرها.
    |
    */
    'auth'                            => [
        'enabled'     => false,
        'default'     => true,
        'in'          => AuthIn::BEARER->value,
        'name'        => 'Authorization',
        'use_value'   => 'Bearer CCK9WNldS2SHFoIB41AEidML0r6oS9PPojZlOdbmBX4Dt6t3IadgdzvcYchJqO12', // توکن واقعی
        'placeholder' => '{YOUR_AUTH_TOKEN}',
        'extra_info'  => 'برای دریافت توکن، به پنل خود وارد شوید و یک توکن API تولید کنید.',
    ],

    /*
    |-------------------------------------------------------------------------
    | مقدمه مستندات
    |-------------------------------------------------------------------------
    |
    | متنی که در ابتدای مستندات نمایش داده می‌شود.
    |
    */
    'intro_text'                      => <<<INTRO
        این مستندات تمام اطلاعات لازم برای کار با API ما را فراهم می‌کند.

        <aside>در سمت راست، مثال‌های درخواست به زبان‌های مختلف برنامه‌نویسی را مشاهده می‌کنید.</aside>
    INTRO,

    /*
    |-------------------------------------------------------------------------
    | زبان‌های مثال
    |-------------------------------------------------------------------------
    |
    | زبان‌های برنامه‌نویسی که باید در مثال‌ها نمایش داده شوند.
    |
    */
    'example_languages'               =>['bash','javascript','php','python'],

    /*
    |-------------------------------------------------------------------------
    | مجموعه Postman
    |-------------------------------------------------------------------------
    |
    | تولید مجموعه Postman برای تست API.
    |
    */
    'postman'                         =>['enabled'=>true],

    /*
    |-------------------------------------------------------------------------
    | مشخصات OpenAPI
    |-------------------------------------------------------------------------
    |
    | تولید مشخصات OpenAPI (v3.0.1).
    |
    */
    'openapi'                         =>['enabled'=>true],

    /*
    |-------------------------------------------------------------------------
    | گروه‌ها و مرتب‌سازی
    |-------------------------------------------------------------------------
    |
    | تعریف دسته‌بندی‌های API و ترتیب آن‌ها.
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
    |-------------------------------------------------------------------------
    | اطلاعات آخرین به‌روزرسانی
    |-------------------------------------------------------------------------
    |
    | نمایش تاریخ آخرین به‌روزرسانی در مستندات.
    |
    */
    'last_updated'                    =>'Last updated: {date:F j, Y}',

    /*
    |-------------------------------------------------------------------------
    | تولید داده‌های مثال
    |-------------------------------------------------------------------------
    |
    | تنظیم نحوه تولید داده‌های نمونه برای درخواست‌ها و پاسخ‌ها.
    |
    */
    'examples'                        =>[
        'faker_seed'=>null,// حذف Seed برای تنوع بیشتر

        // بازنویسی مقادیر پیش‌فرض با داده‌های واقعی و مرتبط
        'override'  =>[
            'mobile'          =>'09181234567',                            // شماره موبایل ایرانی
            'otpCode'         =>'123456',                                 // کد تأیید 6 رقمی
            'token'           =>'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',// نمونه توکن JWT
            'first_name'      =>'علی',                                 // نام فارسی
            'last_name'       =>'رضایی',                             // نام خانوادگی فارسی
            'national_code'   =>'1234567890',                             // کد ملی نمونه
            'date_of_birth'   =>'1990-05-15',                             // تاریخ تولد نمونه
            'sex'             =>'male',                                   // جنسیت
            'zone_province_id'=>'1',                                      // ID استان
            'zone_city_id'    =>'1',                                      // ID شهر
            'email'           =>'ali@example.com',                        // ایمیل نمونه
            'address'         =>'تهران، خیابان ولیعصر', // آدرس فارسی
            'appointment_date'=>'1402-05-12',                             // تاریخ نمونه نوبت
            'start_time'      =>'14:30:00',                               // ساعت شروع نمونه
            'fee'             =>500000,                                   // مبلغ نمونه
            'doctor_id'       =>1,                                        // ID پزشک نمونه
            'clinic_id'       =>1,                                        // ID کلینیک نمونه
        ],
    ],

    /*
    |-------------------------------------------------------------------------
    | استراتژی‌های استخراج
    |-------------------------------------------------------------------------
    |
    | تعریف نحوه استخراج اطلاعات از مسیرها.
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
                only:['GET *','POST *'],// اضافه کردن POST برای cancelAppointment
                config:['app.debug'=>false]
            )
        ),
        'responseFields' =>[...Defaults::RESPONSE_FIELDS_STRATEGIES],
    ],

    /*
    |-------------------------------------------------------------------------
    | تراکنش‌های دیتابیس
    |-------------------------------------------------------------------------
    |
    | مشخص کردن اتصالات دیتابیسی که باید از تراکنش استفاده کنند.
    |
    */
    'database_connections_to_transact'=>[config('database.default')],
];
