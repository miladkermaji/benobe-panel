<?php

namespace App\Http\Middleware\Mc;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalCenterPermission;

class CheckMedicalCenterPermission
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        // اگر مرکز درمانی وارد شده باشد، بررسی دسترسی
        if (Auth::guard('medical_center')->check()) {
            $user = Auth::guard('medical_center')->user();

            // استفاده از متد getPermissionsAttribute که خودکار دسترسی‌های پیش‌فرض را ایجاد می‌کند
            $permissions = $user->permissions->permissions ?? [];

            // اگر مرکز درمانی مجوز لازم را دارد، اجازه‌ی عبور داده شود
            if ($permission && in_array($permission, $permissions, true)) {
                return $next($request);
            }

            return abort(403, 'شما اجازه‌ی دسترسی به این بخش را ندارید.');
        }

        // اگر پزشک یا منشی وارد شده باشد، بدون بررسی دیگر، درخواست را عبور بدهد
        if (Auth::guard('doctor')->check() || Auth::guard('secretary')->check()) {
            return $next($request);
        }

        return abort(403, 'مرکز درمانی احراز هویت نشده است.');
    }
}
