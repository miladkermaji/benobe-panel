<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminSystemSetting;

class AdminSystemSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
         // تنظیمات عمومی
         [
          'key' => 'home_title',
          'value' => 'به نوبه | نوبت دهی اینترنتی و مشاوره آنلاین پزشکان',
          'type' => 'string',
          'group' => 'general',
          'description' => 'عنوان اصلی سایت که در مرورگر و SEO استفاده می‌شود.',
         ],
         [
          'key' => 'home_url',
          'value' => 'https://benobe.ir/',
          'type' => 'string',
          'group' => 'general',
          'description' => 'آدرس اصلی سایت.',
         ],
         [
          'key' => 'charset',
          'value' => 'UTF-8',
          'type' => 'string',
          'group' => 'general',
          'description' => 'انکودینگ سیستم.',
         ],
         [
          'key' => 'admin_mobile',
          'value' => '09181738255',
          'type' => 'string',
          'group' => 'general',
          'description' => 'شماره موبایل برای دریافت پیامک‌های سیستم مدیریت.',
         ],
         [
          'key' => 'admintheme',
          'value' => 'nopardaz',
          'type' => 'string',
          'group' => 'general',
          'description' => 'قالب بخش مدیریت.',
         ],
         [
          'key' => 'theme',
          'value' => 'portal',
          'type' => 'string',
          'group' => 'general',
          'description' => 'قالب سایت.',
         ],
         [
          'key' => 'contactus_matn',
          'value' => 'در صورت داشتن هرگونه انتقاد، پیشنهاد و یا درخواستی می‌توانید از طریق فرم موجود در همین صفحه با ما ارتباط برقرار نمائید. در صورتی که قبلاً پیغام ارسال کرده‌اید برای پیگیری با پشتیبانی به نوبه تماس برقرار کنید.',
          'type' => 'string',
          'group' => 'general',
          'description' => 'متن صفحه تماس با ما.',
         ],
         // سئو
         [
          'key' => 'is_seo',
          'value' => '1',
          'type' => 'boolean',
          'group' => 'seo',
          'description' => 'فعال بودن سئو.',
         ],
         [
          'key' => 'meta_keyword',
          'value' => 'به نوبه نوبت دهی اینترنتی و مشاوره آنلاین پزشکان نوبت دهی آنلاین پزشکان، نوبت اینترنتی دکتر، نوبت دهی مطب‌ها، نوبت دهی مطب‌های پزشکی، نوبت مطب سنندج، نوبت دهی پزشکان کردستان، نوبت دهی دکتر سنندج، نوبت دهی بیمارستان کردستان، نوبت دهی کلینیک و درمانگاه، نوبت دکتر، نوبت دهی به نوبه، سامانه نوبت دهی به نوبه، benobe، نوبت بیمارستان سنندج، نوبت دهی، مشاوره تلفنی با پزشک کردستان، مشاوره آنلاین دکتر، آدرس دکتر سنندج benobe، ژین، نوبت دهی ژین، zhin724، نوبت دهی و مشاوره پزشکان',
          'type' => 'string',
          'group' => 'seo',
          'description' => 'کلمات کلیدی متا برای سئو.',
         ],
         [
          'key' => 'meta_description',
          'value' => 'به نوبه سامانه نوبت دهی بهترین پزشکان متخصص و مراکز درمانی کشور می‌باشد، شما می‌توانید به راحتی از پزشک مورد نظرتون نوبت و مشاوره آنلاین بگیرید.',
          'type' => 'string',
          'group' => 'seo',
          'description' => 'توضیحات متا برای سئو.',
         ],
         // درگاه‌های پرداخت
         [
          'key' => 'zarinpal_key',
          'value' => '1713ab96-0d82-11e9-bf3a-005056a205be',
          'type' => 'string',
          'group' => 'payment',
          'description' => 'کلید درگاه زرین‌پال.',
         ],
         [
          'key' => 'zarinpal_sandbox',
          'value' => '0',
          'type' => 'boolean',
          'group' => 'payment',
          'description' => 'فعال بودن حالت سندباکس زرین‌پال.',
         ],
         [
          'key' => 'sb24_marchent_id',
          'value' => '',
          'type' => 'string',
          'group' => 'payment',
          'description' => 'کد پذیرنده بانک سامان.',
         ],
         [
          'key' => 'sb24_password',
          'value' => '',
          'type' => 'string',
          'group' => 'payment',
          'description' => 'رمز پذیرنده بانک سامان.',
         ],
         // پنل پیامک
         [
          'key' => 'username_faraz',
          'value' => 'prooshe',
          'type' => 'string',
          'group' => 'sms',
          'description' => 'نام کاربر در فراز پیامک.',
         ],
         [
          'key' => 'password_faraz',
          'value' => 'QAZwsx123edc*#',
          'type' => 'string',
          'group' => 'sms',
          'description' => 'رمز عبور در فراز پیامک.',
         ],
         [
          'key' => 'num_faraz',
          'value' => '+983000505',
          'type' => 'string',
          'group' => 'sms',
          'description' => 'شماره ارسال‌کننده پیامک.',
         ],
         // تنظیمات کال می
         [
          'key' => 'username_callmee',
          'value' => 'prooshe@gmail.com',
          'type' => 'string',
          'group' => 'callmee',
          'description' => 'نام کاربر در کال می.',
         ],
         [
          'key' => 'password_callmee',
          'value' => 'sadeghi8255',
          'type' => 'string',
          'group' => 'callmee',
          'description' => 'رمز عبور در کال می.',
         ],
         // تنظیمات برنامه
         [
          'key' => 'type_payment_system',
          'value' => 'membershipfee',
          'type' => 'string',
          'group' => 'program',
          'description' => 'سیستم پرداخت نوبت‌دهی (membershipfee یا onlinepayment).',
         ],
         [
          'key' => 'nobat_per_day',
          'value' => '1',
          'type' => 'integer',
          'group' => 'program',
          'description' => 'حداکثر تعداد دریافت نوبت از پزشک در هر روز.',
         ],
         [
          'key' => 'price_per_nobatsite',
          'value' => '0',
          'type' => 'integer',
          'group' => 'program',
          'description' => 'هزینه حق نوبت سایت (به تومان).',
         ],
         [
          'key' => 'default_price_doctor_nobat',
          'value' => '0',
          'type' => 'integer',
          'group' => 'program',
          'description' => 'تعرفه پیش‌فرض ویزیت پزشک (به تومان).',
         ],
         [
          'key' => 'price_monshi_poorsant',
          'value' => '0',
          'type' => 'integer',
          'group' => 'program',
          'description' => 'تعرفه پورسانت منشی (به تومان).',
         ],
         [
          'key' => 'price_per_minute_moshavere',
          'value' => '1000',
          'type' => 'integer',
          'group' => 'program',
          'description' => 'مبلغ هر دقیقه مکالمه مشاوره آنلاین (به تومان).',
         ],
         [
          'key' => 'price_per_question',
          'value' => '0',
          'type' => 'integer',
          'group' => 'program',
          'description' => 'مبلغ ثبت پرسش در قسمت پرسش و پاسخ (به تومان).',
         ],
         [
          'key' => 'upgrade_price',
          'value' => '780000',
          'type' => 'integer',
          'group' => 'program',
          'description' => 'مبلغ ارتقاء حساب کاربری پزشکان (به تومان).',
         ],
         [
          'key' => 'upgrade_days',
          'value' => '90',
          'type' => 'integer',
          'group' => 'program',
          'description' => 'تعداد روز ارتقاء حساب کاربری پزشکان.',
         ],
         // کاربران
         [
          'key' => 'register_default_usergroup',
          'value' => '2',
          'type' => 'integer',
          'group' => 'user',
          'description' => 'گروه کاربری پیش‌فرض عضویت کاربران (1: مدیران, 2: کاربران, 3: پزشکان, 4: بیمارستان, 5: منشی, 6: منشی درمانگاه, 7: نمایندگان).',
         ],
         // تنظیمات امنیتی
         [
          'key' => 'recaptcha_site_key',
          'value' => '6LcqK_YgAAAAAM4x-mGLOG9P3Uh47nz3YeNmxttu',
          'type' => 'string',
          'group' => 'security',
          'description' => 'کلید سایت reCAPTCHA.',
         ],
         [
          'key' => 'recaptcha_secret_key',
          'value' => '6LcqK_YgAAAAAHklL1oBrecIt-J8qIXcHAOnuPll',
          'type' => 'string',
          'group' => 'security',
          'description' => 'کلید مخفی reCAPTCHA.',
         ],
         [
          'key' => 'admin_path',
          'value' => 'panel.php',
          'type' => 'string',
          'group' => 'security',
          'description' => 'آدرس پنل مدیریت.',
         ],
         [
          'key' => 'only_ssl',
          'value' => '1',
          'type' => 'boolean',
          'group' => 'security',
          'description' => 'استفاده فقط از SSL.',
         ],
         [
          'key' => 'allow_cache',
          'value' => 'yes',
          'type' => 'string',
          'group' => 'security',
          'description' => 'فعال بودن ذخیره‌گاه (yes/no).',
         ],
         [
          'key' => 'clear_cache',
          'value' => '0',
          'type' => 'boolean',
          'group' => 'security',
          'description' => 'پاک کردن خودکار ذخیره‌گاه.',
         ],
         [
          'key' => 'auth_domain',
          'value' => '1',
          'type' => 'boolean',
          'group' => 'security',
          'description' => 'تأیید هویت بازدیدکنندگان بر روی دامنه و زیر دامنه‌ها.',
         ],
         [
          'key' => 'login_log',
          'value' => '5',
          'type' => 'integer',
          'group' => 'security',
          'description' => 'حداکثر تلاش برای ورود به سایت.',
         ],
         [
          'key' => 'log_hash',
          'value' => '1',
          'type' => 'boolean',
          'group' => 'security',
          'description' => 'ریست ورود کاربران با هر رفرش صفحه.',
         ],
         [
          'key' => 'extra_login',
          'value' => '0',
          'type' => 'string',
          'group' => 'security',
          'description' => 'حالت ورود (0: مداوم, 1: پایدار).',
         ],
         [
          'key' => 'ip_control',
          'value' => '0',
          'type' => 'string',
          'group' => 'security',
          'description' => 'امنیت ورود کاربران (0: عادی, 1: متوسط, 2: پیشرفته).',
         ],
         [
          'key' => 'auth_metod',
          'value' => '0',
          'type' => 'string',
          'group' => 'security',
          'description' => 'روش ورود کاربران (0: نام کاربری, 1: پست الکترونیکی).',
         ],
         [
          'key' => 'key',
          'value' => '',
          'type' => 'string',
          'group' => 'security',
          'description' => 'کلید امنیتی.',
         ],
         [
          'key' => 'log_threshold',
          'value' => '4',
          'type' => 'string',
          'group' => 'security',
          'description' => 'نوع ذخیره‌سازی لاگ‌ها (0: غیرفعال, 1: Error, 2: Debug, 3: INFO, 4: All).',
         ],
         [
          'key' => 'log_date_format',
          'value' => 'Y-m-d H:i:s',
          'type' => 'string',
          'group' => 'security',
          'description' => 'فرمت تاریخ لاگ‌ها.',
         ],
         // تنظیمات فایل‌ها
         [
          'key' => 'files_allow',
          'value' => '1',
          'type' => 'boolean',
          'group' => 'files',
          'description' => 'فعال بودن فایل ضمیمه.',
         ],
         [
          'key' => 'files_count',
          'value' => '0',
          'type' => 'boolean',
          'group' => 'files',
          'description' => 'نمایش شمارشگر تعداد دانلود.',
         ],
         [
          'key' => 'files_type',
          'value' => 'zip,rar,pdf,txt,xlsx,xls,docx,docs,mp4,mp3,avi',
          'type' => 'string',
          'group' => 'files',
          'description' => 'فرمت‌های مجاز برای آپلود فایل.',
         ],
         [
          'key' => 't_seite',
          'value' => '1',
          'type' => 'boolean',
          'group' => 'files',
          'description' => 'نوع ساخت تصویر کوچکتر (Thumbnail).',
         ],
         [
          'key' => 'max_image',
          'value' => '600',
          'type' => 'integer',
          'group' => 'files',
          'description' => 'اندازه عرض تصویر اول پس از آپلود (پیکسل).',
         ],
         [
          'key' => 'medium_image',
          'value' => '600',
          'type' => 'string',
          'group' => 'files',
          'description' => 'اندازه تصویر دوم پس از آپلود (عدد یا مثلاً 120x120).',
         ],
         [
          'key' => 'allow_watermark',
          'value' => '1',
          'type' => 'boolean',
          'group' => 'files',
          'description' => 'فعال بودن Watermark.',
         ],
         [
          'key' => 'image_align',
          'value' => 'center',
          'type' => 'string',
          'group' => 'files',
          'description' => 'قرارگیری پیش‌فرض عکس (left, center, right).',
         ],
         [
          'key' => 'max_up_size',
          'value' => '4194304',
          'type' => 'integer',
          'group' => 'files',
          'description' => 'حداکثر حجم آپلود فایل (به کیلوبایت).',
         ],
         [
          'key' => 'jpeg_quality',
          'value' => '100',
          'type' => 'integer',
          'group' => 'files',
          'description' => 'کیفیت تصاویر (0 تا 100).',
         ],
         [
          'key' => 'max_watermark',
          'value' => '150',
          'type' => 'integer',
          'group' => 'files',
          'description' => 'حداقل اندازه برای قرارگیری Watermark (پیکسل).',
         ],
         [
          'key' => 'max_up_side',
          'value' => '',
          'type' => 'string',
          'group' => 'files',
          'description' => 'بیشترین اندازه مجاز برای آپلود تصاویر (0 برای حذف محدودیت).',
         ],
         [
          'key' => 'max_file_count',
          'value' => '',
          'type' => 'string',
          'group' => 'files',
          'description' => 'بیشترین مقدار فایل آپلود همزمان (0 برای حذف محدودیت).',
         ],
         [
          'key' => 'o_seite',
          'value' => '0',
          'type' => 'string',
          'group' => 'files',
          'description' => 'تنظیمات پیش‌فرض تصویر اصلی (0: کامل, 1: طول, 2: عرض).',
         ],
         // اطلاعات تماس
         [
          'key' => 'company_name',
          'value' => 'سامانه به نوبه',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'نام شرکت.',
         ],
         [
          'key' => 'contact_address',
          'value' => 'سنندج، بهاران، بلوار محمد زکریای رازی، ساختمان پارک علم و فناوری کردستان، طبقه دوم، واحد ۲۰۵ - کد پستی: 6617739474',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'آدرس پستی.',
         ],
         [
          'key' => 'contact_email',
          'value' => 'info@benobe.ir',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'ایمیل تماس.',
         ],
         [
          'key' => 'contact_emailsupport',
          'value' => 'info@benobe.ir',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'ایمیل پشتیبانی.',
         ],
         [
          'key' => 'contact_phone',
          'value' => '09181738255 - 08733730514',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'تلفن تماس.',
         ],
         [
          'key' => 'contact_fax',
          'value' => '09181738255',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'فاکس.',
         ],
         [
          'key' => 'copyright',
          'value' => 'تمامی حقوق برای به نوبه محفوظ است و هر گونه کپی‌برداری پیگرد قانونی دارد.',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'متن کپی‌رایت.',
         ],
         [
          'key' => 'contact_telegram',
          'value' => 'https://t.me/benobeir',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'آدرس تلگرام.',
         ],
         [
          'key' => 'contact_instagram',
          'value' => 'https://www.instagram.com/benobe.ir',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'آدرس اینستاگرام.',
         ],
         [
          'key' => 'contact_linkdin',
          'value' => 'https://www.linkedin.com/in/benobe',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'آدرس لینکدین.',
         ],
         [
          'key' => 'contact_pinterest',
          'value' => 'https://www.pinterest.com/prooshe',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'آدرس پینترست.',
         ],
         [
          'key' => 'contact_gplus',
          'value' => '#',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'آدرس گوگل پلاس.',
         ],
         [
          'key' => 'contact_facebook',
          'value' => 'https://m.facebook.com/wwwbenobeir',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'آدرس فیسبوک.',
         ],
         [
          'key' => 'contact_twitter',
          'value' => '#',
          'type' => 'string',
          'group' => 'contact',
          'description' => 'آدرس توییتر.',
         ],
         // تنظیمات ایمیل
         [
          'key' => 'admin_mail',
          'value' => 'info@benobe.ir',
          'type' => 'string',
          'group' => 'mail',
          'description' => 'ایمیل ادمین.',
         ],
         [
          'key' => 'mail_title',
          'value' => 'به نوبه',
          'type' => 'string',
          'group' => 'mail',
          'description' => 'سربرگ ایمیل‌ها هنگام ارسال.',
         ],
         [
          'key' => 'mail_metod',
          'value' => 'php',
          'type' => 'string',
          'group' => 'mail',
          'description' => 'سیستم ایمیل (php یا smtp).',
         ],
         [
          'key' => 'smtp_host',
          'value' => 'mail.nobat.com',
          'type' => 'string',
          'group' => 'mail',
          'description' => 'هاست SMTP.',
         ],
         [
          'key' => 'smtp_port',
          'value' => '587',
          'type' => 'integer',
          'group' => 'mail',
          'description' => 'پورت SMTP.',
         ],
         [
          'key' => 'smtp_user',
          'value' => 'info@benobe.ir',
          'type' => 'string',
          'group' => 'mail',
          'description' => 'نام کاربری SMTP.',
         ],
         [
          'key' => 'smtp_pass',
          'value' => 'qfqytNAnGPJx',
          'type' => 'string',
          'group' => 'mail',
          'description' => 'رمز عبور SMTP.',
         ],
         [
          'key' => 'smtp_secure',
          'value' => '',
          'type' => 'string',
          'group' => 'mail',
          'description' => 'پروتکل امن SMTP (خالی، ssl، tls).',
         ],
         [
          'key' => 'smtp_mail',
          'value' => 'info@benobe.ir',
          'type' => 'string',
          'group' => 'mail',
          'description' => 'ایمیل برای تأیید هویت SMTP.',
         ],
         [
          'key' => 'mail_bcc',
          'value' => '1',
          'type' => 'boolean',
          'group' => 'mail',
          'description' => 'استفاده از BCC برای ارسال ایمیل‌ها.',
         ],
        ];

        foreach ($settings as $setting) {
            AdminSystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
     'value' => $setting['value'],
     'type' => $setting['type'],
     'group' => $setting['group'],
     'description' => $setting['description'],
    ]
            );
        }
    }
}
