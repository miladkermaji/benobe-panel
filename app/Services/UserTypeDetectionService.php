<?php

namespace App\Services;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Manager;
use App\Models\MedicalCenter;

class UserTypeDetectionService
{
    /**
     * تشخیص نوع کاربر بر اساس شماره موبایل
     *
     * @param string $mobile
     * @return array
     */
    public function detectUserType(string $mobile): array
    {
        $formattedMobile = $this->formatMobile($mobile);

        // بررسی در جدول پزشکان
        $doctor = Doctor::where('mobile', $formattedMobile)->first();
        if ($doctor) {
            return [
                'type' => 'doctor',
                'model' => $doctor,
                'model_class' => Doctor::class,
                'model_id' => $doctor->id,
                'is_active' => $doctor->status == 1,
            ];
        }

        // بررسی در جدول منشی‌ها
        $secretary = Secretary::where('mobile', $formattedMobile)->first();
        if ($secretary) {
            return [
                'type' => 'secretary',
                'model' => $secretary,
                'model_class' => Secretary::class,
                'model_id' => $secretary->id,
                'is_active' => $secretary->is_active == 1,
            ];
        }

        // بررسی در جدول مدیران
        $manager = Manager::where('mobile', $formattedMobile)->first();
        if ($manager) {
            return [
                'type' => 'manager',
                'model' => $manager,
                'model_class' => Manager::class,
                'model_id' => $manager->id,
                'is_active' => $manager->is_active == 1,
            ];
        }

        // بررسی در جدول مراکز درمانی
        $medicalCenter = MedicalCenter::where('phone_number', $formattedMobile)->first();
        if ($medicalCenter) {
            return [
                'type' => 'medical_center',
                'model' => $medicalCenter,
                'model_class' => MedicalCenter::class,
                'model_id' => $medicalCenter->id,
                'is_active' => $medicalCenter->is_active == 1,
            ];
        }

        // بررسی در جدول کاربران عادی
        $user = User::where('mobile', $formattedMobile)->first();
        if ($user) {
            return [
                'type' => 'user',
                'model' => $user,
                'model_class' => User::class,
                'model_id' => $user->id,
                'is_active' => $user->status == 1,
            ];
        }

        // کاربر یافت نشد
        return [
            'type' => null,
            'model' => null,
            'model_class' => null,
            'model_id' => null,
            'is_active' => false,
        ];
    }

    /**
     * تشخیص نوع کاربر با اولویت مدیر (برای لاگین ادمین)
     *
     * @param string $mobile
     * @return array
     */
    public function detectUserTypeForAdmin(string $mobile): array
    {
        $formattedMobile = $this->formatMobile($mobile);

        // ابتدا بررسی در جدول مدیران
        $manager = Manager::where('mobile', $formattedMobile)->first();
        if ($manager) {
            return [
                'type' => 'manager',
                'model' => $manager,
                'model_class' => Manager::class,
                'model_id' => $manager->id,
                'is_active' => $manager->is_active == 1,
            ];
        }

        // اگر مدیر پیدا نشد، بررسی در سایر جداول
        return $this->detectUserType($mobile);
    }

    /**
     * تشخیص نوع کاربر فقط برای لاگین ادمین (فقط از جدول managers)
     *
     * @param string $mobile
     * @return array
     */
    public function detectUserTypeForAdminOnly(string $mobile): array
    {
        $formattedMobile = $this->formatMobile($mobile);

        // فقط بررسی در جدول مدیران
        $manager = Manager::where('mobile', $formattedMobile)->first();
        if ($manager) {
            return [
                'type' => 'manager',
                'model' => $manager,
                'model_class' => Manager::class,
                'model_id' => $manager->id,
                'is_active' => $manager->is_active == 1,
            ];
        }

        // مدیر یافت نشد
        return [
            'type' => null,
            'model' => null,
            'model_class' => null,
            'model_id' => null,
            'is_active' => false,
        ];
    }

    /**
     * تشخیص نوع کاربر فقط برای لاگین پزشک (از جداول doctor, secretary, medical_center)
     *
     * @param string $mobile
     * @return array
     */
    public function detectUserTypeForDoctorOnly(string $mobile): array
    {
        $formattedMobile = $this->formatMobile($mobile);

        // بررسی در جدول پزشکان
        $doctor = Doctor::where('mobile', $formattedMobile)->first();
        if ($doctor) {
            return [
                'type' => 'doctor',
                'model' => $doctor,
                'model_class' => Doctor::class,
                'model_id' => $doctor->id,
                'is_active' => $doctor->status == 1,
            ];
        }

        // بررسی در جدول منشی‌ها
        $secretary = Secretary::where('mobile', $formattedMobile)->first();
        if ($secretary) {
            return [
                'type' => 'secretary',
                'model' => $secretary,
                'model_class' => Secretary::class,
                'model_id' => $secretary->id,
                'is_active' => $secretary->is_active == 1,
            ];
        }

        // بررسی در جدول مراکز درمانی
        $medicalCenter = MedicalCenter::where('phone_number', $formattedMobile)->first();
        if ($medicalCenter) {
            return [
                'type' => 'medical_center',
                'model' => $medicalCenter,
                'model_class' => MedicalCenter::class,
                'model_id' => $medicalCenter->id,
                'is_active' => $medicalCenter->is_active == 1,
            ];
        }

        // کاربر یافت نشد
        return [
            'type' => null,
            'model' => null,
            'model_class' => null,
            'model_id' => null,
            'is_active' => false,
        ];
    }

    /**
     * فرمت کردن شماره موبایل
     *
     * @param string $mobile
     * @return string
     */
    public function formatMobile(string $mobile): string
    {
        $mobile = preg_replace('/^(\+98|98|0)/', '', $mobile);
        return '0' . $mobile;
    }

    /**
     * بررسی وجود کاربر در هر جدول
     *
     * @param string $mobile
     * @return array
     */
    public function checkUserExists(string $mobile): array
    {
        $formattedMobile = $this->formatMobile($mobile);

        return [
            'doctor' => Doctor::where('mobile', $formattedMobile)->exists(),
            'secretary' => Secretary::where('mobile', $formattedMobile)->exists(),
            'manager' => Manager::where('mobile', $formattedMobile)->exists(),
            'medical_center' => MedicalCenter::where('phone_number', $formattedMobile)->exists(),
            'user' => User::where('mobile', $formattedMobile)->exists(),
        ];
    }

    /**
     * دریافت مدل کاربر بر اساس نوع
     *
     * @param string $mobile
     * @param string $type
     * @return mixed
     */
    public function getUserByType(string $mobile, string $type)
    {
        $formattedMobile = $this->formatMobile($mobile);

        return match ($type) {
            'doctor' => Doctor::where('mobile', $formattedMobile)->first(),
            'secretary' => Secretary::where('mobile', $formattedMobile)->first(),
            'manager' => Manager::where('mobile', $formattedMobile)->first(),
            'medical_center' => MedicalCenter::where('phone_number', $formattedMobile)->first(),
            'user' => User::where('mobile', $formattedMobile)->first(),
            default => null,
        };
    }

    /**
     * دریافت guard مناسب بر اساس نوع کاربر
     *
     * @param string $type
     * @return string
     */
    public function getGuardByType(string $type): string
    {
        return match ($type) {
            'doctor' => 'doctor-api',
            'secretary' => 'secretary-api',
            'manager' => 'manager-api',
            'medical_center' => 'medical_center-api',
            'user' => 'api',
            default => 'api',
        };
    }

    /**
     * دریافت مسیر ریدایرکت مناسب بر اساس نوع کاربر
     *
     * @param string $type
     * @return string
     */
    public function getRedirectRouteByType(string $type): string
    {
        return match ($type) {
            'doctor' => route('dr-panel'),
            'secretary' => route('dr-panel'),
            'manager' => route('admin-panel'),
            'medical_center' => '/mc/panel',
            'user' => route('dashboard'),
            default => route('dashboard'),
        };
    }
}
