# خلاصه تغییرات Migration - تبدیل Clinics به Medical Centers

## هدف
تبدیل جدول `clinics` به جدول `medical_centers` با اضافه کردن فیلد `prescription_tariff` و تغییر تمام روابط مرتبط.

## تغییرات انجام شده

### 1. اضافه کردن فیلد prescription_tariff به جدول medical_centers
- **فایل**: `2025_07_28_000001_add_prescription_tariff_to_medical_centers_table.php`
- **تغییر**: اضافه کردن فیلد `prescription_tariff` به جدول `medical_centers`

### 2. تغییر کلیدهای خارجی از clinics به medical_centers

#### جداول اصلی:
- **appointments**: `clinic_id` → `medical_center_id`
- **prescription_requests**: `clinic_id` → `medical_center_id`
- **doctor_notes**: `clinic_id` → `medical_center_id`
- **vacations**: `clinic_id` → `medical_center_id`
- **order_visits**: `clinic_id` → `medical_center_id`
- **counseling_appointments**: `clinic_id` → `medical_center_id`
- **best_doctors**: `clinic_id` → `medical_center_id`
- **clinic_deposit_settings**: `clinic_id` → `medical_center_id`
- **doctor_selected_clinics**: `clinic_id` → `medical_center_id`
- **doctor_clinic**: `clinic_id` → `medical_center_id`
- **manual_appointment_settings**: `clinic_id` → `medical_center_id`
- **manual_appointments**: `clinic_id` → `medical_center_id`
- **consultations**: `clinic_id` → `medical_center_id`
- **doctor_appointment_configs**: `clinic_id` → `medical_center_id`
- **doctor_counseling_configs**: `clinic_id` → `medical_center_id`

#### جداول اضافی:
- **secretaries**: `clinic_id` → `medical_center_id`
- **doctor_holidays**: `clinic_id` → `medical_center_id`
- **doctor_counseling_work_schedules**: `clinic_id` → `medical_center_id`
- **secretary_permissions**: `clinic_id` → `medical_center_id`
- **user_blockings**: `clinic_id` → `medical_center_id`
- **special_daily_schedules**: `clinic_id` → `medical_center_id`
- **counseling_daily_schedules**: `clinic_id` → `medical_center_id`
- **counseling_holidays**: `clinic_id` → `medical_center_id`
- **doctor_counseling_holidays**: `clinic_id` → `medical_center_id`
- **doctor_work_schedules**: `clinic_id` → `medical_center_id`
- **insurances**: `clinic_id` → `medical_center_id`
- **doctor_wallet_transactions**: `clinic_id` → `medical_center_id`

### 3. تغییر نام جداول
- **doctor_clinic** → **doctor_medical_center**
- **clinic_deposit_settings** → **medical_center_deposit_settings**
- **doctor_selected_clinics** → **doctor_selected_medical_centers**

### 4. حذف جداول قدیمی
- جدول `clinics` و تمام جداول مرتبط با آن
- جدول `clinic_galleries`

### 5. به‌روزرسانی مدل‌ها

#### مدل‌های به‌روزرسانی شده:
- **MedicalCenter**: اضافه کردن فیلد `prescription_tariff` و روابط جدید
- **Appointment**: تغییر رابطه `clinic` به `medicalCenter`
- **PrescriptionRequest**: تغییر رابطه `clinic` به `medicalCenter`
- **DoctorSelectedMedicalCenter**: تغییر نام و روابط
- **DoctorNote**: تغییر رابطه `clinic` به `medicalCenter`
- **Vacation**: تغییر رابطه `clinic` به `medicalCenter`
- **BestDoctor**: تغییر رابطه `clinic` به `medicalCenter`
- **MedicalCenterDepositSetting**: مدل جدید
- **OrderVisit**: مدل جدید
- **CounselingAppointment**: تغییر رابطه `clinic` به `medicalCenter`
- **Consultation**: تغییر رابطه `clinic` به `medicalCenter`
- **ManualAppointment**: تغییر رابطه `clinic` به `medicalCenter`
- **ManualAppointmentSetting**: تغییر رابطه `clinic` به `medicalCenter`
- **DoctorAppointmentConfig**: تغییر رابطه `clinic` به `medicalCenter`
- **DoctorCounselingConfig**: تغییر رابطه `clinic` به `medicalCenter`
- **Doctor**: تغییر روابط `selectedClinic` به `selectedMedicalCenter` و حذف متدهای مربوط به clinic

#### مدل‌های حذف شده:
- **Clinic**: حذف کامل
- **ClinicDepositSetting**: حذف کامل
- **ClinicGallery**: حذف کامل

### 6. Migration های جدید ایجاد شده
- `2025_07_28_000021_create_doctor_appointment_configs_table.php`
- `2025_07_28_000022_create_doctor_counseling_configs_table.php`
- `2025_07_28_000023_create_manual_appointment_settings_table.php`
- `2025_07_28_000024_create_manual_appointments_table.php`
- `2025_07_28_000025_create_consultations_table.php`
- `2025_07_28_000026_create_order_visits_table.php`

### 7. Migration های حذف شده
- `2024_09_19_062139_create_clinics_table.php`
- `2025_07_21_000002_add_prescription_fee_to_clinics_table.php`
- `2025_03_20_100000_create_doctor_clinic_table.php`
- `2025_06_16_082822_create_doctor_selected_clinics_table.php`
- `2025_01_26_113551_create_clinic_deposit_settings_table.php`
- `2025_01_11_181906_create_doctor_appointment_configs_table.php`
- `2025_01_28_085320_create_doctor_counseling_configs_table.php`
- `2025_01_19_143416_create_manual_appointment_settings_table.php`
- `2025_03_02_130303_create_order_visits_table.php`

### 8. Migration های قدیمی به‌روزرسانی شده
تمام migration های قدیمی که به جدول `clinics` اشاره می‌کردند، به‌روزرسانی شدند تا به جدول `medical_centers` اشاره کنند.

## نحوه اجرا

### مرحله 1: اجرای migration ها
```bash
php artisan migrate
```

### مرحله 2: بررسی خطاها
```bash
php artisan migrate:status
```

### مرحله 3: تست عملکرد
- بررسی عملکرد API ها
- بررسی عملکرد پنل ادمین
- بررسی عملکرد پنل پزشک

## نکات مهم

1. **Backup**: قبل از اجرای migration ها، حتماً از دیتابیس backup بگیرید
2. **Data Migration**: اگر داده‌ای در جداول clinics وجود دارد، باید به جداول medical_centers منتقل شود
3. **Code Review**: تمام فایل‌های کد که از مدل Clinic استفاده می‌کنند باید به‌روزرسانی شوند
4. **Testing**: پس از اجرای migration ها، تمام عملکردها باید تست شوند

## فایل‌های نیازمند به‌روزرسانی

### Controllers:
- تمام controller هایی که از مدل Clinic استفاده می‌کنند
- API controllers
- Admin controllers
- Doctor panel controllers

### Views:
- تمام view هایی که از متغیرهای clinic استفاده می‌کنند
- فرم‌های ایجاد و ویرایش
- لیست‌ها و جداول

### Livewire Components:
- تمام component هایی که با clinics کار می‌کنند
- فرم‌ها و لیست‌ها

### Routes:
- بررسی route هایی که ممکن است به clinic ها اشاره کنند

## وضعیت فعلی
✅ تمام migration ها ایجاد شده
✅ تمام migration های قدیمی به‌روزرسانی شده
✅ تمام مدل‌ها به‌روزرسانی شده
✅ تمام مدل‌های قدیمی حذف شده
❌ نیاز به به‌روزرسانی controllers
❌ نیاز به به‌روزرسانی views
❌ نیاز به به‌روزرسانی Livewire components
❌ نیاز به data migration (اگر داده‌ای وجود دارد)