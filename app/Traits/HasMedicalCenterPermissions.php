<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasMedicalCenterPermissions
{
    public function hasPermission($permission)
    {
        // اگر مدیر وارد شده باشد، همه دسترسی‌ها را دارد
        if (Auth::guard('manager')->check()) {
            return true;
        }

        // اگر پزشک یا منشی وارد شده باشد، همه دسترسی‌ها را دارد
        if (Auth::guard('doctor')->check() || Auth::guard('secretary')->check()) {
            return true;
        }

        // اگر مرکز درمانی وارد شده باشد، بررسی دسترسی
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $permissionRecord = $medicalCenter->permissions;
            $permissionsArray = $permissionRecord ? ($permissionRecord->permissions ?? []) : [];

            return in_array($permission, $permissionsArray, true);
        }

        return false;
    }
}
