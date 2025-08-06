<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\MedicalCenterPermission;

trait HasMedicalCenterPermissions
{
    public function hasPermission($permission)
    {
        // اگر مدیر وارد شده باشد، همه دسترسی‌ها را دارد
        if (Auth::guard('manager')->check()) {
            return true;
        }

        // اگر مرکز درمانی وارد شده باشد، بررسی دسترسی
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();

            // دریافت دسترسی‌ها مستقیماً از دیتابیس
            $permissionRecord = MedicalCenterPermission::where('medical_center_id', $medicalCenter->id)->first();
            $permissionsArray = $permissionRecord ? ($permissionRecord->permissions ?? []) : [];

            return in_array($permission, $permissionsArray, true);
        }

        // اگر پزشک یا منشی وارد شده باشد، دسترسی به منوی مرکز درمانی ندارند
        // چون این trait برای sidebar مرکز درمانی استفاده می‌شود
        if (Auth::guard('doctor')->check() || Auth::guard('secretary')->check()) {
            return false;
        }

        return false;
    }
}
