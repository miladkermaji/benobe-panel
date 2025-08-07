<?php

return [
    'dashboard' => [
        'title' => 'داشبورد',
        'icon' => 'i-dashboard',
        'routes' => [
            'admin-panel' => 'داشبورد',
        ],
    ],
    'tools' => [
        'title' => 'ابزارها',
        'icon' => 'i-courses',
        'routes' => [
            'admin.panel.tools.file-manager' => 'مدیریت فایل',
            'admin.tools.data-migration.index' => 'انتقال داده‌ها',
            'admin.panel.tools.payment_gateways.index' => 'درگاه پرداخت',
            'admin.panel.tools.sms-gateways.index' => 'پیامک',
            'admin.panel.tools.telescope' => 'تلسکوپ',
            'admin.panel.tools.redirects.index' => 'ابزار ریدایرکت',
            'admin.tools.sitemap.index' => 'نقشه سایت',
            'admin.tools.page-builder.index' => 'صفحه‌ساز',
            'admin.panel.tools.mail-template.index' => 'قالب ایمیل',
            'admin.tools.news-latter.index' => 'خبرنامه',
            'admin.panel.tools.notifications.index' => 'مدیریت اعلان‌ها',
        ],
    ],
    'user_management' => [
        'title' => 'مدیریت کاربران',
        'icon' => 'i-users',
        'routes' => [
            'admin.panel.users.index' => 'لیست کاربران',
            'admin.panel.user-groups.index' => 'گروه‌های کاربری',
            'admin.panel.user-blockings.index' => 'مدیریت مسدودیت‌ها',
        ],
    ],
    'manager_management' => [
        'title' => 'مدیریت مدیران',
        'icon' => 'i-users',
        'routes' => [
            'admin.panel.managers.index' => 'مدیران',
            'admin.panel.managers.permissions' => 'مدیریت دسترسی مدیران',
        ],
    ],
    'doctor_management' => [
        'title' => 'مدیریت پزشکان',
        'icon' => 'i-users',
        'routes' => [
            'admin.panel.doctors.index' => 'لیست پزشکان',
            'admin.panel.best-doctors.index' => 'پزشک برتر',
            'admin.panel.doctor-documents.index' => 'تأیید مدارک',
            'admin.panel.doctor-specialties.index' => 'تخصص های پزشک',
            'admin.panel.doctor-comments.index' => 'نظرات بیماران',
            'admin.panel.doctors.permissions' => 'مدیریت دسترسی‌ها',
        ],
    ],
    'membership' => [
        'title' => 'حق عضویت',
        'icon' => 'i-courses',
        'routes' => [
            'admin.panel.user-subscriptions.index' => 'اشتراک‌ها',
            'admin.panel.user-membership-plans.index' => 'طرح‌های عضویت',
            'admin.panel.user-appointment-fees.index' => 'هزینه‌های نوبت‌دهی',
        ],
    ],
    'secretary_management' => [
        'title' => 'مدیریت منشی‌ها',
        'icon' => 'i-users',
        'routes' => [
            'admin.panel.secretaries.index' => 'لیست منشی‌ها',
            'admin.panel.secretaries.secreteries-permission' => 'دسترسی‌های منشی',
        ],
    ],
    'patient_management' => [
        'title' => 'مدیریت بیماران',
        'icon' => 'i-users',
        'routes' => [
            'admin.panel.users.index' => 'لیست بیماران',
            'admin.panel.sub-users.index' => 'کاربران زیرمجموعه',
        ],
    ],
    'medical_centers' => [
        'title' => 'مراکز درمانی',
        'icon' => 'i-courses',
        'routes' => [
            'admin.panel.hospitals.index' => 'مدیریت بیمارستان',
            'admin.panel.laboratories.index' => 'مدیریت آزمایشگاه',
            'admin.panel.clinics.index' => 'مدیریت کلینیک',
            'admin.panel.treatment-centers.index' => 'مدیریت درمانگاه',
            'admin.panel.imaging-centers.index' => 'مراکز تصویربرداری',
            'admin.panel.medical-centers.permissions' => 'دسترسی‌های مراکز درمانی',
        ],
    ],
    'service_management' => [
        'title' => 'مدیریت خدمات',
        'icon' => 'i-courses',
        'routes' => [
            'admin.panel.services.index' => 'لیست خدمات',
            'admin.panel.doctor-services.index' => 'خدمات پزشکان',
        ],
    ],
    'financial_management' => [
        'title' => 'مدیریت مالی',
        'icon' => 'i-courses',
        'routes' => [
            'admin.panel.transactions.index' => 'تراکنش‌ها',
            'admin.panel.doctor-wallets.index' => 'کیف‌پول',
        ],
    ],
    'content_management' => [
        'title' => 'مدیریت محتوا',
        'icon' => 'i-courses',
        'routes' => [
            'admin.panel.blogs.index' => 'مدیریت بلاگ',
            'admin.panel.specialties.index' => 'مدیریت تخصص ها',
            'admin.panel.zones.index' => 'شهر و استان',
            'admin.panel.reviews.index' => 'مدیریت نظرات',
        ],
    ],
    'site_settings' => [
        'title' => 'تنظیمات سایت',
        'icon' => 'i-courses',
        'routes' => [
            'admin.panel.menus.index' => 'منوها',
            'admin.panel.banner-texts.index' => 'بنر صفحه اصلی',
            'admin.panel.footer-contents.index' => 'فوتر',
            'admin.panel.setting.index' => 'تنظیمات عمومی',
        ],
    ],
    'support' => [
        'title' => 'پشتیبانی',
        'icon' => 'i-courses',
        'routes' => [
            'admin.panel.tickets.index' => 'تیکت‌های پشتیبانی',
        ],
    ],
];
