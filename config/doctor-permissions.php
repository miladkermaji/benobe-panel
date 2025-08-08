<?php

return [
    'dashboard' => [
        'title' => 'داشبورد',
        'icon' => 'i-dashboard',
        'routes' => [
            'dr-panel' => 'داشبورد',
        ],
    ],
    'appointments' => [
        'title' => 'نوبت اینترنتی',
        'icon' => 'i-appointments',
        'routes' => [
            'dr-appointments' => 'لیست نوبت ها',
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
    'my-prescriptions' => [
        'title' => 'نسخه های من',
        'icon' => 'i-prescriptions',
        'routes' => [
            'dr.panel.my-prescriptions' => 'مدیریت نسخه ها',
            'dr.panel.my-prescriptions.settings' => 'تنظیمات درخواست نسخه',
        ],
    ],
    'consult' => [
        'title' => 'مشاوره',
        'icon' => 'i-consultation',
        'routes' => [
            'dr-moshavere_setting' => 'برنامه‌ریزی مشاوره',
            'dr-moshavere_waiting' => 'گزارش مشاوره',
            'dr.panel.doctornotes.index' => 'توضیحات نوبت',
            'dr-mySpecialDays-counseling' => 'روزهای خاص',
            'consult-term.index' => 'قوانین مشاوره',
        ],
    ],
    'insurance' => [
        'title' => 'خدمات و بیمه',
        'icon' => 'i-insurance',
        'routes' => [
            'dr.panel.doctor-services.index' => 'خدمات و بیمه',
        ],
    ],
    'prescription' => [
        'title' => 'نسخه الکترونیک',
        'icon' => 'i-electronic-prescription',
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
        'icon' => 'i-financial-reports',
        'routes' => [
            'dr.panel.financial-reports.index' => 'گزارش مالی',
            'dr-payment-setting' => 'پرداخت',
            'dr-wallet-charge' => 'شارژ کیف‌پول',
        ],
    ],
    'patient_communication' => [
        'title' => 'ارتباط با بیماران',
        'icon' => 'i-patient-communication',
        'routes' => [
            'dr.panel.send-message' => 'ارسال پیام',
        ],
    ],
    'patient_records' => [
        'title' => 'پرونده الکترونیک',
        'icon' => 'i-patient-records',
        'routes' => [
            'dr-patient-records' => 'پرونده الکترونیک',
        ],
    ],
    'secretary_management' => [
        'title' => 'منشی',
        'icon' => 'i-secretary',
        'routes' => [
            'dr-secretary-management' => 'مدیریت منشی‌ها',
            'dr-secretary-permissions' => 'سطح دسترسی منشی',
        ],
    ],
    'clinic_management' => [
        'title' => 'مطب',
        'icon' => 'i-clinic',
        'routes' => [
            'dr-clinic-management' => 'مدیریت مطب',
            'dr.panel.clinics.medical-documents' => 'مدارک من',
            'doctors.clinic.deposit' => 'بیعانه',
        ],
    ],
    'permissions' => [
        'title' => 'دسترسی‌ها',
        'icon' => 'i-permissions',
        'routes' => [
            'dr-secretary-permissions' => 'سطح دسترسی منشی',
        ],
    ],
    'profile' => [
        'title' => 'حساب کاربری',
        'icon' => 'i-profile',
        'routes' => [
            'dr-edit-profile' => 'ویرایش پروفایل',
            'dr-edit-profile-security' => 'امنیت',
            'dr-edit-profile-upgrade' => 'ارتقا حساب',
            'dr-my-performance' => 'عملکرد من',
            'dr-subuser' => 'کاربران زیرمجموعه',
            'my-dr-appointments' => 'نوبت‌های من',
            'dr.panel.doctor-faqs.index' => ' سوالات متداول',
        ],
    ],
    'statistics' => [
        'title' => 'گزارش‌ها و آمار',
        'icon' => 'i-statistics',
        'routes' => [
            'dr-my-performance-chart' => 'آمار و نمودار'
        ],
    ],
    'messages' => [
        'title' => 'پیام',
        'icon' => 'i-messages',
        'routes' => [
            'dr-panel-tickets' => 'تیکت‌ها',
            '#' => 'صفحه گفتگو',
        ],
    ],
];
