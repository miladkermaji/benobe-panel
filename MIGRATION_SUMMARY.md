# خلاصه تغییرات Migration و Model برای تبدیل clinics به medical_centers

## تغییرات انجام شده:

### 1. Migration های جدید ایجاد شده:
- `2025_07_28_000001_add_prescription_tariff_to_medical_centers_table.php` - اضافه کردن فیلد prescription_tariff
- `2025_07_28_000002_update_appointments_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در appointments
- `2025_07_28_000003_update_prescription_requests_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در prescription_requests
- `2025_07_28_000004_update_doctor_notes_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در doctor_notes
- `2025_07_28_000005_update_vacations_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در vacations
- `2025_07_28_000006_update_order_visits_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در order_visits
- `2025_07_28_000007_update_counseling_appointments_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در counseling_appointments
- `2025_07_28_000008_update_best_doctors_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در best_doctors
- `2025_07_28_000009_update_clinic_deposit_settings_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در clinic_deposit_settings
- `2025_07_28_000010_update_doctor_selected_clinics_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در doctor_selected_clinics
- `2025_07_28_000011_update_doctor_clinic_table_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در doctor_clinic
- `2025_07_28_000012_rename_doctor_clinic_to_doctor_medical_center.php` - تغییر نام جدول doctor_clinic به doctor_medical_center
- `2025_07_28_000013_rename_clinic_deposit_settings_to_medical_center_deposit_settings.php` - تغییر نام جدول clinic_deposit_settings به medical_center_deposit_settings
- `2025_07_28_000014_rename_doctor_selected_clinics_to_doctor_selected_medical_centers.php` - تغییر نام جدول doctor_selected_clinics به doctor_selected_medical_centers
- `2025_07_28_000015_drop_clinics_table_and_related_tables.php` - حذف جداول clinics و clinic_galleries
- `2025_07_28_000016_update_manual_appointment_settings_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در manual_appointment_settings
- `2025_07_28_000017_update_manual_appointments_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در manual_appointments
- `2025_07_28_000018_update_consultations_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در consultations
- `2025_07_28_000019_update_doctor_appointment_configs_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در doctor_appointment_configs
- `2025_07_28_000020_update_doctor_counseling_configs_clinic_to_medical_center.php` - تغییر clinic_id به medical_center_id در doctor_counseling_configs

### 2. Migration های جدید برای بازسازی جداول:
- `2025_07_28_000021_create_doctor_appointment_configs_table.php` - بازسازی جدول doctor_appointment_configs
- `2025_07_28_000022_create_doctor_counseling_configs_table.php` - بازسازی جدول doctor_counseling_configs
- `2025_07_28_000023_create_manual_appointment_settings_table.php` - بازسازی جدول manual_appointment_settings
- `2025_07_28_000024_create_manual_appointments_table.php` - بازسازی جدول manual_appointments
- `2025_07_28_000025_create_consultations_table.php` - بازسازی جدول consultations
- `2025_07_28_000026_create_order_visits_table.php` - بازسازی جدول order_visits

### 3. Migration های قدیمی به‌روزرسانی شده:
تمام migration های قدیمی که به جدول clinics اشاره می‌کردند، به‌روزرسانی شدند تا به medical_centers اشاره کنند.

### 4. Model های به‌روزرسانی شده:
- **MedicalCenter** - اضافه کردن فیلد prescription_tariff و روابط جدید
- **Doctor** - حذف روابط clinics و به‌روزرسانی روابط
- **Appointment** - تغییر رابطه clinic به medicalCenter
- **PrescriptionRequest** - تغییر رابطه clinic به medicalCenter
- **DoctorNote** - تغییر رابطه clinic به medicalCenter
- **Vacation** - تغییر رابطه clinic به medicalCenter
- **BestDoctor** - تغییر رابطه clinic به medicalCenter
- **CounselingAppointment** - تغییر رابطه clinic به medicalCenter
- **Consultation** - تغییر رابطه clinic به medicalCenter
- **ManualAppointment** - تغییر رابطه clinic به medicalCenter
- **ManualAppointmentSetting** - تغییر رابطه clinic به medicalCenter
- **DoctorAppointmentConfig** - تغییر رابطه clinic به medicalCenter
- **DoctorCounselingConfig** - تغییر رابطه clinic به medicalCenter
- **Secretary** - تغییر رابطه clinic به medicalCenter
- **DoctorHoliday** - تغییر رابطه clinic به medicalCenter
- **DoctorService** - تغییر رابطه clinic به medicalCenter
- **Insurance** - تغییر رابطه clinic به medicalCenter
- **DoctorWalletTransaction** - تغییر رابطه clinic به medicalCenter
- **UserBlocking** - تغییر رابطه clinic به medicalCenter
- **SecretaryPermission** - تغییر رابطه clinic به medicalCenter
- **CounselingDailySchedule** - تغییر رابطه clinic به medicalCenter
- **DoctorCounselingHoliday** - تغییر رابطه clinic به medicalCenter
- **SpecialDailySchedule** - تغییر رابطه clinic به medicalCenter
- **CounselingHoliday** - تغییر رابطه clinic به medicalCenter
- **DoctorCounselingWorkSchedule** - تغییر رابطه clinic به medicalCenter

### 5. Model های جدید ایجاد شده:
- **MedicalCenterDepositSetting** - مدل جدید برای جدول medical_center_deposit_settings
- **OrderVisit** - مدل جدید برای جدول order_visits
- **DoctorSelectedMedicalCenter** - مدل جدید برای جدول doctor_selected_medical_centers

### 6. Model های حذف شده:
- **Clinic** - حذف کامل
- **ClinicDepositSetting** - حذف کامل
- **ClinicGallery** - حذف کامل
- **DoctorSelectedClinic** - حذف کامل

### 7. Trait ها و Controller های به‌روزرسانی شده:
- **HasSelectedClinic** - تغییر متدها برای استفاده از medical_center
- **DrPanelController** - تغییر متد getSelectedClinicId به getSelectedMedicalCenterId
- **HeaderComponent** - تغییر کامل برای استفاده از medical_center
- **HeaderComponent View** - تغییر کامل برای استفاده از medical_center

### 8. فایل‌های حذف شده:
- `database/migrations/2024_09_19_062139_create_clinics_table.php`
- `database/migrations/2025_07_21_000002_add_prescription_fee_to_clinics_table.php`
- `app/Models/Clinic.php`
- `app/Models/ClinicDepositSetting.php`
- `app/Models/ClinicGallery.php`
- `app/Models/DoctorSelectedClinic.php`

## مراحل بعدی:

### 1. قبل از اجرای Migration ها:
- **Backup از دیتابیس** تهیه کنید
- **Migration های موجود** را rollback کنید (در صورت نیاز)
- **داده‌های موجود** در جدول clinics را به medical_centers منتقل کنید

### 2. اجرای Migration ها:
```bash
php artisan migrate
```

### 3. بررسی و تست:
- **Controller ها** که از Clinic استفاده می‌کنند را بررسی کنید
- **View ها** که clinic data نمایش می‌دهند را بررسی کنید
- **Livewire Components** که با clinic کار می‌کنند را بررسی کنید
- **Route ها** که به clinic resources اشاره می‌کنند را بررسی کنید

### 4. تست جامع:
- **نوبت‌دهی** در مراکز درمانی
- **مشاوره آنلاین**
- **مدیریت مراکز درمانی**
- **گزارش‌گیری**
- **پروفایل پزشک**

## نکات مهم:
- تمام foreign key ها به درستی به جدول medical_centers اشاره می‌کنند
- فیلد type در جدول medical_centers برای تشخیص نوع مرکز درمانی استفاده می‌شود
- فیلد prescription_tariff به جدول medical_centers اضافه شده است
- تمام روابط Eloquent به‌روزرسانی شده‌اند
- Migration ها به ترتیب صحیح اجرا می‌شوند