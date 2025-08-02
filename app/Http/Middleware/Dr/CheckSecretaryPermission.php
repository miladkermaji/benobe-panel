<?php

namespace App\Http\Middleware\Dr;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSecretaryPermission
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        // اگر پزشک وارد شده باشد، بدون بررسی دیگر، درخواست را عبور بدهد
        if (Auth::guard('doctor')->check()) {
            return $next($request);
        }

        // اگر مرکز درمانی وارد شده باشد، بدون بررسی دیگر، درخواست را عبور بدهد
        if (Auth::guard('medical_center')->check()) {
            return $next($request);
        }

        // بررسی ورود منشی
        $user = Auth::guard('secretary')->user();
        if (!$user) {
            return abort(403, 'منشی احراز هویت نشده است.');
        }

        // دریافت لیست مجوزهای منشی
        $permissionRecord = $user->permissions()->first();
        $permissionsArray = $permissionRecord ? json_decode($permissionRecord->permissions ?? '[]', true) : [];

        // اگر منشی مجوز لازم را دارد، اجازه‌ی عبور داده شود
        if ($permission && in_array($permission, $permissionsArray, true)) {
            return $next($request);
        }

        return abort(403, 'شما اجازه‌ی دسترسی به این بخش را ندارید.');
    }
}
