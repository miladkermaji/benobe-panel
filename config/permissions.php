<?php

return [
    'dashboard' => [
        'title' => 'داشبورد',
        'icon' => 'i-dashboard',
        'routes' => ['dr-panel' => 'داشبورد'],
    ],
    'appointments' => [
        'title' => 'نوبت اینترنتی',
        'icon' => 'i-courses',
        'routes' => [
            'dr-appointments' => 'مراجعین من',
            'dr-workhours' => 'ساعت کاری',
            'dr.panel.doctornotes.index' => 'توضیحات نوبت',
            'dr-mySpecialDays' => 'روزهای خاص',
            'dr-manual_nobat_setting' => 'تنظیمات نوبت دستی',
            'dr-manual_nobat' => 'ثبت نوبت دستی',
            'dr-scheduleSetting' => 'تنظیمات نوبت',
            'dr-vacation' => 'تعطیلات',
            'doctor-blocking-users.index' => 'کاربران مسدود',
        ],
    ],
    'consult' => [
        'title' => 'مشاوره',
        'icon' => 'i-moshavere',
        'routes' => [
            'dr-moshavere_setting' => 'برنامه‌ریزی مشاوره',
            'dr-moshavere_waiting' => 'گزارش مشاوره',
            'dr.panel.doctornotes.index' => 'توضیحات نوبت',
            'dr-mySpecialDays-counseling' => 'روزهای خاص',
            'consult-term.index' => 'قوانین مشاوره',
        ],
    ],
    'services' => [
        'title' => 'خدمات',
        'icon' => 'i-checkout__request',
        'routes' => ['dr.panel.doctor-services.index' => 'خدمات'],
    ],
    'prescription' => [
        'title' => 'نسخه الکترونیک',
        'icon' => 'i-banners',
        'routes' => [
            'dr-patient-records' => 'پرونده پزشکی',
            'prescription.index' => 'نسخه‌های ثبت شده',
            'providers.index' => 'بیمه‌های من',
            'favorite.templates.index' => 'نسخه پراستفاده',
            'templates.favorite.service.index' => 'اقلام پراستفاده',
        ],
    ],
    'financial_reports' => [
        'title' => 'گزارش مالی',
        'icon' => 'i-my__peyments',
        'routes' => [
            'dr-payment-setting' => 'پرداخت',
            'dr-wallet-charge' => 'شارژ کیف‌پول',
        ],
    ],
    'communication' => [
        'title' => 'ارتباط با بیماران',
        'icon' => 'i-users',
        'routes' => [
            'dr.panel.send-message' => 'ارسال پیام',
        ],
    ],
    'patient_records' => [
        'title' => 'پرونده الکترونیک',
        'icon' => 'i-checkout__request',
        'routes' => ['dr-patient-records' => 'پرونده الکترونیک'],
    ],
    'secretary_management' => [
        'title' => 'منشی',
        'icon' => 'i-user__secratary',
        'routes' => [
            'dr-secretary-management' => 'مدیریت منشی‌ها',
        ],
    ],
    'clinic_management' => [
        'title' => 'مطب',
        'icon' => 'i-clinic',
        'routes' => [
            'dr-clinic-management' => 'مدیریت مطب',
            'dr.panel.clinics.medical-documents' => 'مدارک من',
        ],
    ],
    'insurance' => [
        'title' => 'بیمه‌ها',
        'icon' => 'i-checkout__request',
        'routes' => ['dr-bime' => 'بیمه'],
    ],
    'permissions' => [
        'title' => 'دسترسی‌ها',
        'icon' => 'i-checkout__request',
        'routes' => ['dr-secretary-permissions' => 'سطح دسترسی منشی'],
    ],
    'profile' => [
        'title' => 'حساب کاربری',
        'icon' => 'i-users',
        'routes' => [
            'dr-edit-profile' => 'حساب کاربری',
            'my-dr-appointments' => 'نوبت‌های من',
            'dr-edit-profile-security' => 'امنیت',
            'dr-edit-profile-upgrade' => 'ارتقا حساب',
            'dr-my-performance' => 'عملکرد و رتبه من',
            'dr-subuser' => 'کاربران زیرمجموعه',
        ],
    ],

    'messages' => [
        'title' => 'پیام',
        'icon' => 'i-comments',
        'routes' => [
            'dr-panel-tickets' => 'تیکت‌ها',
            '#' => 'صفحه گفتگو',
        ],
    ],
    'statistics' => [
        'title' => 'گزارش‌ها و آمار',
        'icon' => 'i-transactions',
        'routes' => [
            'dr-my-performance-chart' => 'آمار و نمودار'
        ],
    ]
];
