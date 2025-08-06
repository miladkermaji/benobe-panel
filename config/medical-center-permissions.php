<?php

return [
    'dashboard' => [
        'title' => 'داشبورد',
        'icon' => 'i-dashboard',
        'routes' => [
            'mc-panel' => 'داشبورد',
        ],
    ],
    'medical_center_management' => [
        'title' => 'مرکز درمانی من',
        'icon' => 'i-users',
        'routes' => [
            'mc.panel.doctors.index' => 'مدیریت پزشکان',
            'mc.panel.doctors.create' => 'افزودن پزشک',
            'mc.panel.doctors.edit' => 'ویرایش پزشک',
            'mc.panel.specialties.index' => 'مدیریت تخصص‌ها',
            'mc.panel.specialties.create' => 'افزودن تخصص',
            'mc.panel.specialties.edit' => 'ویرایش تخصص',
            'mc.panel.services.index' => 'مدیریت خدمات',
            'mc.panel.services.create' => 'افزودن خدمت',
            'mc.panel.services.edit' => 'ویرایش خدمت',
            'mc.panel.insurances.index' => 'مدیریت بیمه‌ها',
            'mc.panel.insurances.create' => 'افزودن بیمه',
            'mc.panel.insurances.edit' => 'ویرایش بیمه',
            'mc.panel.profile.edit' => 'ویرایش پروفایل',
        ],
    ],
    'workhours' => [
        'title' => 'ساعت کاری',
        'icon' => 'i-checkout__request',
        'routes' => [
            'mc-workhours' => 'ساعت کاری',
        ],
    ],
    'appointments' => [
        'title' => 'نوبت اینترنتی',
        'icon' => 'i-courses',
        'routes' => [
            'mc-appointments' => 'لیست نوبت ها',
            'mc.panel.doctornotes.index' => 'توضیحات نوبت',
            'mc-mySpecialDays' => 'روزهای خاص',
            'mc-scheduleSetting' => 'تنظیمات نوبت',
            'mc-vacation' => 'تعطیلات',
            'mc-doctor-blocking-users.index' => 'کاربران مسدود',
        ],
    ],
    'prescriptions' => [
        'title' => 'نسخه های من',
        'icon' => 'i-banners',
        'routes' => [
            'mc.panel.my-prescriptions' => 'مدیریت نسخه ها',
            'mc.panel.my-prescriptions.settings' => 'تنظیمات درخواست نسخه',
        ],
    ],
    'consult' => [
        'title' => 'مشاوره',
        'icon' => 'i-moshavere',
        'routes' => [
            'mc-moshavere_setting' => 'برنامه‌ریزی مشاوره',
            'mc-moshavere_waiting' => 'گزارش مشاوره',
            'mc-mySpecialDays-counseling' => 'روزهای خاص',
            'consult-term.index' => 'قوانین مشاوره',
        ],
    ],
    'doctor_services' => [
        'title' => 'خدمات و بیمه',
        'icon' => 'i-checkout__request',
        'routes' => [
            'mc.panel.doctor-services.index' => 'خدمات و بیمه',
        ],
    ],
    'electronic_prescription' => [
        'title' => 'نسخه الکترونیک',
        'icon' => 'i-banners',
        'routes' => [
            'prescription.index' => 'نسخه‌های ثبت شده',
            'providers.index' => 'بیمه‌های من',
            'favorite.templates.index' => 'نسخه پراستفاده',
            'templates.favorite.service.index' => 'اقلام پراستفاده',
            'mc-patient-records' => 'پرونده الکترونیک',
        ],
    ],
    'financial_reports' => [
        'title' => 'گزارش مالی',
        'icon' => 'i-my__peyments',
        'routes' => [
            'mc.panel.financial-reports.index' => 'گزارش مالی',
            'mc-payment-setting' => 'پرداخت',
            'mc-wallet-charge' => 'شارژ کیف‌پول',
        ],
    ],
    'patient_communication' => [
        'title' => 'ارتباط با بیماران',
        'icon' => 'i-users',
        'routes' => [
            'mc.panel.send-message' => 'ارسال پیام',
        ],
    ],
    'secretary_management' => [
        'title' => 'منشی',
        'icon' => 'i-user__secratary',
        'routes' => [
            'mc-secretary-management' => 'مدیریت منشی‌ها',
            'mc-secretary-permissions' => 'دسترسی‌ها',
        ],
    ],
    'clinic_management' => [
        'title' => 'مطب',
        'icon' => 'i-clinic',
        'routes' => [
            'mc-clinic-management' => 'مدیریت مطب',
            'mc.panel.clinics.medical-documents' => 'مدارک من',
            'mc-doctors.clinic.deposit' => 'بیعانه',
        ],
    ],
    'profile' => [
        'title' => 'حساب کاربری',
        'icon' => 'i-users',
        'routes' => [
            'mc-edit-profile' => 'ویرایش پروفایل',
            'mc-edit-profile-security' => 'امنیت',
            'mc-edit-profile-upgrade' => 'ارتقا حساب',
            'mc-my-performance' => 'عملکرد من',
            'mc-subuser' => 'کاربران زیرمجموعه',
            'my-mc-appointments' => 'نوبت‌های من',
            'mc.panel.doctor-faqs.index' => 'سوالات متداول',
        ],
    ],
    'statistics' => [
        'title' => 'آمار و نمودار',
        'icon' => 'i-transactions',
        'routes' => [
            'mc-my-performance-chart' => 'آمار و نمودار'
        ],
    ],
    'messages' => [
        'title' => 'پیام',
        'icon' => 'i-comments',
        'routes' => [
            'mc-panel-tickets' => 'تیکت‌ها',
            '#' => 'صفحه گفتگو',
        ],
    ],
];
