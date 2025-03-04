<?php

return [
 'dashboard' => [
  'title' => 'داشبورد',
  'icon' => 'i-dashboard', // کلاس آیکون برای داشبورد
  'routes' => ['dr-panel' => 'داشبورد'],
 ],
 'appointments' => [
  'title' => 'نوبت‌دهی',
  'icon' => 'i-courses', // کلاس آیکون برای نوبت‌دهی
  'routes' => [
   'dr-appointments' => 'مراجعین من',
   'dr-workhours' => 'ساعت کاری',
   'dr-mySpecialDays' => 'روزهای خاص من',
   'dr-manual_nobat_setting' => 'تنظیمات نوبت دستی',
   'dr-manual_nobat' => 'ثبت نوبت دستی',
   'dr-scheduleSetting' => 'تنظیمات نوبت‌دهی',
  ],
 ],
 'consult' => [
  'title' => 'مشاوره آنلاین',
  'icon' => 'i-moshavere', // کلاس آیکون برای مشاوره
  'routes' => [
   'dr-moshavere_setting' => 'برنامه‌ریزی مشاوره',
   'dr-moshavere_waiting' => 'گزارش مشاوره‌ها',
   'consult-term.index' => 'قوانین مشاوره',
   'dr-mySpecialDays-counseling' => 'روزهای خاص مشاوره',
  ],
 ],
 'dr-services' => [
  'title' => 'خدمات دکتر',
  'icon' => 'i-checkout__request', // کلاس آیکون برای بیمه
  'routes' => [
   'dr-services.index' => 'خدمات دکتر'
  ],
 ],
 'prescription' => [
  'title' => 'نسخه الکترونیک',
  'icon' => 'i-banners', // کلاس آیکون برای نسخه الکترونیک
  'routes' => [
   'prescription.index' => 'نسخه‌های ثبت‌شده',
   'providers.index' => 'بیمه‌های من',
   'favorite.templates.index' => 'نسخه‌های پر استفاده',
   'templates.favorite.service.index' => 'اقلام پر استفاده',
  ],
 ],
 'financial_reports' => [
  'title' => 'گزارش مالی',
  'icon' => 'i-my__peyments', // کلاس آیکون برای گزارش مالی
  'routes' => [
   'dr-payment-setting' => 'تنظیمات پرداخت',
   'dr-wallet-charge' => ' شارژ کیف پول',
  ],
 ],
 'patient_records' => [
  'title' => 'پرونده الکترونیک',
  'icon' => 'i-checkout__request', // کلاس آیکون برای پرونده
  'routes' => [
   'dr-patient-records' => 'پرونده بیماران',
  ],
 ],
 'secretary_management' => [
  'title' => 'مدیریت منشی‌ها',
  'icon' => 'i-user__secratary', // کلاس آیکون برای مدیریت منشی
  'routes' => [
   'dr-secretary-management' => 'مدیریت منشی‌ها',
  ],
 ],
 'clinic_management' => [
  'title' => 'مدیریت مطب',
  'icon' => 'i-clinic', // کلاس آیکون برای مدیریت مطب
  'routes' => [
   'dr-clinic-management' => 'مدیریت مطب',
   'dr-office-gallery' => 'گالری تصاویر',
   'dr-office-medicalDoc' => 'مدارک من',
  ],
 ],
 'insurance' => [
  'title' => 'بیمه‌ها',
  'icon' => 'i-checkout__request', // کلاس آیکون برای بیمه
  'routes' => [
   'dr-bime' => 'بیمه'
  ],
 ],
 /*     'permissions' => [
         'title' => 'دسترسی‌ها',
         'icon' => 'i-checkout__request', // کلاس آیکون برای دسترسی‌ها
         'routes' => [
             'dr-secretary-permissions'
         ],
     ], */
 /*     'profile' => [
         'title' => 'حساب کاربری',
         'icon' => 'i-users', // کلاس آیکون برای حساب کاربری
         'routes' => [
             'dr-edit-profile' => 'ویرایش پروفایل',
             'my-dr-appointments' => 'نوبت‌های من',
             'dr-edit-profile-security' => 'امنیت',
             'dr-edit-profile-upgrade' => 'ارتقا حساب',
             'dr-my-performance' => 'عملکرد و رتبه من',
             'dr-subuser' => 'کاربران زیرمجموعه',
         ],
     ], */
 'messages' => [
  'title' => 'پیام‌ها',
  'icon' => 'i-comments', // کلاس آیکون برای پیام‌ها
  'routes' => [
   'dr-panel-tickets' => 'تیکت‌ها',
  ],
 ],
 'statistics' => [
  'title' => 'آمار و نمودار',
  'icon' => 'i-transactions', // کلاس آیکون برای آمار
  'routes' => [
   'dr-my-performance-chart' => 'آمار و نمودار'
  ],
 ],
];
