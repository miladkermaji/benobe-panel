# سیستم مدیریت خطاهای پایگاه داده

## بررسی مشکل

این سیستم برای مدیریت خطاهای اتصال به پایگاه داده طراحی شده است. به جای نمایش خطاهای فنی پیچیده، صفحات خطای زیبا و کاربرپسند نمایش داده می‌شود.

## فایل‌های ایجاد شده

### 1. Exception Handler (`app/Exceptions/Handler.php`)
- مدیریت خطاهای `QueryException` و `PDOException`
- تشخیص خطاهای اتصال (Connection refused)
- نمایش صفحات خطای مناسب

### 2. Middleware (`app/Http/Middleware/CheckDatabaseConnection.php`)
- بررسی وضعیت اتصال به پایگاه داده
- جلوگیری از خطاهای غیرضروری
- نمایش پیام‌های مناسب

### 3. Console Command (`app/Console/Commands/CheckDatabaseStatus.php`)
- بررسی وضعیت اتصال به پایگاه داده
- نمایش اطلاعات مفصل
- ارائه راه‌حل‌های پیشنهادی

### 4. Error Views
- `resources/views/errors/database-connection.blade.php` - خطای اتصال
- `resources/views/errors/database-error.blade.php` - خطاهای عمومی پایگاه داده
- `resources/views/errors/system-status.blade.php` - وضعیت سیستم

## نحوه استفاده

### بررسی وضعیت پایگاه داده
```bash
php artisan db:status
```

### مشاهده وضعیت سیستم
```
http://your-domain.com/system/status
```

### تست خطاها
برای تست سیستم، MySQL را متوقف کنید و سپس صفحه‌ای را بارگذاری کنید که به پایگاه داده نیاز دارد.

## پیام‌های خطا

### خطای اتصال (503)
- "ارتباط با سرور پایگاه داده برقرار نشد"
- راه‌حل‌های پیشنهادی
- آیکون مناسب (🔌)

### خطای عمومی پایگاه داده (500)
- "خطایی در ارتباط با پایگاه داده رخ داده است"
- راهنمایی برای کاربر

## تنظیمات

### Middleware
Middleware در `bootstrap/app.php` ثبت شده و به صورت سراسری اجرا می‌شود.

### Routes
- `/system/status` - نمایش وضعیت سیستم

## عیب‌یابی

### مشکلات رایج
1. **MySQL متوقف شده**: سرور MySQL را راه‌اندازی کنید
2. **تنظیمات نادرست**: فایل `.env` را بررسی کنید
3. **پورت اشتباه**: پورت MySQL را بررسی کنید (معمولاً 3306)

### دستورات مفید
```bash
# بررسی وضعیت MySQL
sudo systemctl status mysql

# راه‌اندازی MySQL
sudo systemctl start mysql

# بررسی تنظیمات اتصال
php artisan config:show database
```

## بهبودها

- [ ] اضافه کردن monitoring real-time
- [ ] ارسال اعلان به مدیر سیستم
- [ ] لاگ کردن خطاها
- [ ] نمایش آمار خطاها 