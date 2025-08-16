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
        $profileCheck = $this->isProfileComplete($user);

        if (!$profileCheck['is_complete']) {
            // ساخت URL پروفایل با استفاده از config جدید
            $profileUrl = config('frontend.profile_url');

            return response()->json([
                'status' => 'error',
                'message' => 'لطفاً ابتدا اطلاعات پروفایل خود را تکمیل کنید.',
                'redirect_url' => $profileUrl,
                'incomplete_fields' => $profileCheck['incomplete_fields'],
                'data' => null,
            ], 403);
        }

        return $next($request);
    }

    /**
     * بررسی کامل بودن اطلاعات پروفایل کاربر
     */
    private function isProfileComplete($user): array
    {
        $incompleteFields = [];

        // بررسی نوع کاربر و فیلدهای مربوطه
        $userClass = get_class($user);

        if ($userClass === \App\Models\User::class) {
            // برای کاربران عادی
            $requiredFields = [
                'first_name',
                'last_name',
                'national_code',
                'mobile',
                'date_of_birth',
                'sex',
                'zone_province_id',
                'zone_city_id'
            ];

            foreach ($requiredFields as $field) {
                if (empty($user->$field)) {
                    $incompleteFields[] = $field;
                }
            }

            // بررسی اضافی برای آدرس (اختیاری اما توصیه شده)
            if (empty($user->address)) {
                $incompleteFields[] = 'address';
            }

        } elseif ($userClass === \App\Models\Doctor::class) {
            // برای پزشکان
            $requiredFields = [
                'first_name',
                'last_name',
                'national_code',
                'mobile',
                'date_of_birth',
                'sex',
                'province_id',
                'city_id'
            ];

            foreach ($requiredFields as $field) {
                if (empty($user->$field)) {
                    $incompleteFields[] = $field;
                }
            }

            // بررسی اضافی برای آدرس (اختیاری اما توصیه شده)
            if (empty($user->address)) {
                $incompleteFields[] = 'address';
            }

        } elseif ($userClass === \App\Models\Secretary::class) {
            // برای منشی‌ها
            $requiredFields = [
                'first_name',
                'last_name',
                'national_code',
                'mobile',
                'province_id',
                'city_id'
            ];

            foreach ($requiredFields as $field) {
                if (empty($user->$field)) {
                    $incompleteFields[] = $field;
                }
            }

            // بررسی اضافی برای آدرس (اختیاری اما توصیه شده)
            if (empty($user->address)) {
                $incompleteFields[] = 'address';
            }

        } elseif ($userClass === \App\Models\Manager::class) {
            // برای مدیران
            $requiredFields = [
                'first_name',
                'last_name',
                'national_code',
                'mobile'
            ];

            foreach ($requiredFields as $field) {
                if (empty($user->$field)) {
                    $incompleteFields[] = $field;
                }
            }

        } else {
            // برای سایر انواع کاربران، بررسی فیلدهای عمومی
            $requiredFields = [
                'first_name',
                'last_name',
                'national_code',
                'mobile'
            ];

            foreach ($requiredFields as $field) {
                if (empty($user->$field)) {
                    $incompleteFields[] = $field;
                }
            }
        }

        return [
            'is_complete' => empty($incompleteFields),
            'incomplete_fields' => $incompleteFields
        ];
    }
}
