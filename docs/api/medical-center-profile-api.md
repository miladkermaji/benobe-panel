# API مستندات پروفایل مراکز درمانی

## مقدمه

این API برای دریافت اطلاعات کامل پروفایل مراکز درمانی و نظرات آنها طراحی شده است. API شامل دو endpoint اصلی است:

1. **پروفایل مرکز درمانی**: دریافت اطلاعات کامل مرکز درمانی
2. **نظرات مرکز درمانی**: دریافت نظرات با قابلیت pagination و فیلتر

## Base URL

```
https://your-domain.com/api
```

## 1. دریافت پروفایل مرکز درمانی

### Endpoint
```
GET /medical-centers/{centerId}/profile
```

### پارامترهای URL
- `centerId` (integer, required): شناسه یکتای مرکز درمانی

### پاسخ موفق (200)

```json
{
    "success": true,
    "data": {
        "center_details": {
            "id": 1,
            "name": "مرکز درمانی نمونه",
            "title": "مرکز درمانی تخصصی قلب و عروق",
            "city": "تهران",
            "province": "تهران",
            "type": "clinic",
            "is_24_7": false,
            "rating": {
                "value": 4.5,
                "reviews_count": 125
            },
            "recommendation_percentage": 92,
            "total_successful_appointments": 1500,
            "image_url": "https://your-domain.com/storage/centers/1.jpg",
            "description": "توضیحات مرکز درمانی...",
            "working_hours": {
                "start_time": "08:00:00",
                "end_time": "18:00:00",
                "working_days": ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه"]
            },
            "consultation_fee": 150000,
            "prescription_tariff": 50000,
            "payment_methods": "cash",
            "center_tariff_type": "governmental",
            "daycare_centers": "no"
        },
        "address": {
            "full_address": "تهران، خیابان ولیعصر، پلاک 123",
            "phone_number": "021-12345678",
            "secretary_phone": "021-87654321",
            "postal_code": "1234567890",
            "latitude": 35.7219,
            "longitude": 51.3347,
            "location_confirmed": true
        },
        "doctors": [
            {
                "id": 1,
                "name": "دکتر احمد محمدی",
                "specialty": "متخصص قلب و عروق",
                "rating": {
                    "value": 4.8,
                    "reviews_count": 45
                },
                "successful_appointments": 320,
                "first_available_appointment": "2024-01-15 10:00:00"
            }
        ],
        "specialties": [
            {
                "id": 1,
                "name": "قلب و عروق"
            },
            {
                "id": 2,
                "name": "داخلی"
            }
        ],
        "insurances": [
            {
                "id": 1,
                "name": "تأمین اجتماعی",
                "image_url": "https://your-domain.com/storage/insurances/1.png"
            }
        ],
        "services": [
            {
                "id": 1,
                "name": "نوار قلب",
                "description": "انجام نوار قلب با تجهیزات پیشرفته"
            }
        ],
        "gallery": [
            {
                "url": "https://your-domain.com/storage/gallery/1.jpg",
                "alt_text": "تصویر مرکز درمانی"
            }
        ],
        "recent_reviews": [
            {
                "id": 1,
                "user_name": "کاربر نمونه",
                "comment": "تجربه بسیار خوبی داشتم",
                "rating": 5,
                "recommendation": "suggest",
                "waiting_time": "کمتر از 30 دقیقه",
                "created_at": "2024-01-10 14:30:00"
            }
        ],
        "additional_info": {
            "siam_code": "123456789",
            "is_main_center": false,
            "phone_numbers": ["021-12345678", "021-87654321"],
            "documents": ["license.pdf", "certificate.pdf"],
            "slug": "medical-center-sample"
        }
    }
}
```

### خطاها

#### 404 - مرکز درمانی یافت نشد
```json
{
    "success": false,
    "message": "مرکز درمانی یافت نشد"
}
```

#### 500 - خطای سرور
```json
{
    "success": false,
    "message": "خطا در دریافت اطلاعات مرکز درمانی",
    "error": "جزئیات خطا"
}
```

## 2. دریافت نظرات مرکز درمانی

### Endpoint
```
GET /medical-centers/{centerId}/reviews
```

### پارامترهای URL
- `centerId` (integer, required): شناسه یکتای مرکز درمانی

### پارامترهای Query
- `page` (integer, optional): شماره صفحه (پیش‌فرض: 1)
- `limit` (integer, optional): تعداد آیتم در هر صفحه (پیش‌فرض: 10, حداکثر: 50)
- `sort` (string, optional): نوع مرتب‌سازی
  - `latest`: جدیدترین (پیش‌فرض)
  - `oldest`: قدیمی‌ترین
  - `rating_high`: بالاترین امتیاز
  - `rating_low`: پایین‌ترین امتیاز
- `filter_rating` (integer, optional): فیلتر بر اساس امتیاز (1-5)
- `filter_recommendation` (string, optional): فیلتر بر اساس پیشنهاد
  - `suggest`: پیشنهاد می‌دهد
  - `not_suggest`: پیشنهاد نمی‌دهد
  - `all`: همه (پیش‌فرض)

### مثال درخواست
```
GET /medical-centers/1/reviews?page=1&limit=10&sort=latest&filter_rating=5&filter_recommendation=suggest
```

### پاسخ موفق (200)

```json
{
    "success": true,
    "data": {
        "reviews": [
            {
                "id": 1,
                "medical_center_id": 1,
                "userable_id": 1,
                "userable_type": "App\\Models\\User",
                "appointment_id": 123,
                "comment": "تجربه بسیار خوبی داشتم. پرسنل خیلی مهربان بودند.",
                "reply": "ممنون از نظر شما",
                "status": true,
                "ip_address": "192.168.1.1",
                "acquaintance": "friend",
                "overall_score": 5,
                "recommend_center": true,
                "score_behavior": 5,
                "score_cleanliness": 4,
                "score_equipment": 5,
                "score_receptionist": 5,
                "score_environment": 4,
                "waiting_time": "کمتر از 30 دقیقه",
                "visit_reason": "معاینه قلبی",
                "receptionist_comment": "منشی خیلی مهربان بود",
                "experience_comment": "تجربه کلی عالی بود",
                "created_at": "2024-01-10T14:30:00.000000Z",
                "updated_at": "2024-01-10T14:30:00.000000Z",
                "user_name": "احمد محمدی",
                "average_score": 4.7
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 10,
            "total": 50,
            "from": 1,
            "to": 10
        }
    }
}
```

### خطاها

#### 400 - پارامترهای نامعتبر
```json
{
    "success": false,
    "message": "پارامترهای نامعتبر",
    "errors": {
        "limit": ["تعداد آیتم باید بین 1 تا 50 باشد"]
    }
}
```

#### 404 - مرکز درمانی یافت نشد
```json
{
    "success": false,
    "message": "مرکز درمانی یافت نشد"
}
```

#### 500 - خطای سرور
```json
{
    "success": false,
    "message": "خطا در دریافت نظرات",
    "error": "جزئیات خطا"
}
```

## کدهای وضعیت HTTP

- **200**: موفق
- **400**: درخواست نامعتبر
- **404**: منبع یافت نشد
- **500**: خطای سرور

## نکات مهم

1. **احراز هویت**: این API ها عمومی هستند و نیازی به احراز هویت ندارند.
2. **Pagination**: برای نظرات، pagination اجباری است و حداکثر 50 آیتم در هر درخواست قابل دریافت است.
3. **فیلترها**: فیلترها اختیاری هستند و می‌توانند ترکیب شوند.
4. **مرتب‌سازی**: مرتب‌سازی پیش‌فرض بر اساس تاریخ (جدیدترین) است.
5. **نظرات**: فقط نظرات تأیید شده (status = true) نمایش داده می‌شوند.

## مثال‌های استفاده

### دریافت پروفایل مرکز درمانی
```bash
curl -X GET "https://your-domain.com/api/medical-centers/1/profile"
```

### دریافت نظرات با فیلتر
```bash
curl -X GET "https://your-domain.com/api/medical-centers/1/reviews?page=1&limit=20&sort=rating_high&filter_rating=5"
```

### دریافت نظرات پیشنهادی
```bash
curl -X GET "https://your-domain.com/api/medical-centers/1/reviews?filter_recommendation=suggest&sort=latest"
``` 