# تست هر دو ورژن JWT Authentication

## تست ورژن قدیمی (v1)

### 1. ورود
```bash
curl -X POST http://your-domain/api/auth/login-register \
  -H "Content-Type: application/json" \
  -d '{"mobile":"09123456789"}'
```

### 2. تایید OTP
```bash
curl -X POST http://your-domain/api/auth/login-confirm/YOUR_TOKEN \
  -H "Content-Type: application/json" \
  -d '{"otpCode":"1234"}'
```

### 3. تست احراز هویت
```bash
curl -X GET http://your-domain/api/auth/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## تست ورژن جدید (v2)

### 1. ورود
```bash
curl -X POST http://your-domain/api/v2/auth/login-register \
  -H "Content-Type: application/json" \
  -d '{"mobile":"09123456789"}'
```

### 2. تایید OTP
```bash
curl -X POST http://your-domain/api/v2/auth/login-confirm/YOUR_TOKEN \
  -H "Content-Type: application/json" \
  -d '{"otpCode":"1234"}'
```

### 3. تست احراز هویت
```bash
curl -X GET http://your-domain/api/v2/auth/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 4. تست جستجو (ورژن جدید)
```bash
curl -X GET "http://your-domain/api/v2/search?search_text=test" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## تست Debug Tools (فقط ورژن جدید)

### 1. تست Token Validation
```bash
curl -X POST http://your-domain/api/debug/jwt-validate \
  -H "Content-Type: application/json" \
  -d '{"token":"YOUR_JWT_TOKEN"}'
```

### 2. تست Cleanup Command
```bash
php artisan jwt:cleanup-invalid-tokens --dry-run
```

## مقایسه نتایج

### ورژن قدیمی:
- ✅ ساده و سریع
- ❌ Logging محدود
- ❌ مشکلات null user ID

### ورژن جدید:
- ✅ Logging کامل
- ✅ Error handling بهتر
- ✅ Debug tools
- ⚠️ کمی کندتر

## توصیه

1. **برای production فعلی**: از ورژن قدیمی استفاده کنید
2. **برای testing**: ورژن جدید را تست کنید
3. **برای future**: به تدریج به ورژن جدید منتقل شوید 