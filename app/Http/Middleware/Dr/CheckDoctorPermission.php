<?php

namespace App\Http\Middleware\Dr;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDoctorPermission
{
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $user = Auth::guard('doctor')->user();
        if (!$user) {
            return abort(403, 'پزشک احراز هویت نشده است.');
        }

        // دریافت لیست مجوزهای پزشک
        $permissionsArray = array_filter(json_decode($user->permissions->permissions ?? '[]', true));

        // اگر پزشک مجوز لازم را دارد، اجازه‌ی عبور داده شود
        if ($permission && in_array($permission, $permissionsArray, true)) {
            return $next($request);
        }

        return abort(403, 'شما اجازه‌ی دسترسی به این بخش را ندارید.');
    }
}
