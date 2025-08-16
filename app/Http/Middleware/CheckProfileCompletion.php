<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // گرفتن کاربر احراز هویت‌شده
        $user = null;

        // بررسی انواع مختلف احراز هویت
        if (Auth::guard('manager-api')->check()) {
            $user = Auth::guard('manager-api')->user();
        } elseif (Auth::guard('secretary-api')->check()) {
            $user = Auth::guard('secretary-api')->user();
        } elseif (Auth::guard('doctor-api')->check()) {
            $user = Auth::guard('doctor-api')->user();
        } elseif (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        }

        // اگر کاربر احراز هویت نشده، اجازه ادامه بده
        if (!$user) {
            return $next($request);
        }

        // بررسی کامل بودن اطلاعات پروفایل
        $isProfileComplete = $this->isProfileComplete($user);

        if (!$isProfileComplete) {
            // ساخت URL پروفایل با استفاده از config جدید
            $profileUrl = config('frontend.profile_url');

            return response()->json([
                'status' => 'error',
                'message' => 'لطفاً ابتدا اطلاعات پروفایل خود را تکمیل کنید.',
                'redirect_url' => $profileUrl,
                'data' => null,
            ], 403);
        }

        return $next($request);
    }

    /**
     * بررسی کامل بودن اطلاعات پروفایل کاربر
     */
    private function isProfileComplete($user): bool
    {
        // بررسی فیلدهای ضروری
        $requiredFields = [
            'first_name',
            'last_name',
            'national_code',
            'mobile',
            'birth_date',
            'gender',
            'province_id',
            'city_id'
        ];

        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                return false;
            }
        }

        // بررسی اضافی برای آدرس (اختیاری اما توصیه شده)
        if (empty($user->address)) {
            return false;
        }

        return true;
    }
}
