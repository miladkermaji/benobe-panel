<?php

namespace App\Http\Services\LoginAttemptsService;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Manager;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoginAttemptsService
{
    // تنظیمات پیش‌فرض برای محدودیت‌های تلاش
    private const MAX_ATTEMPTS_BEFORE_LOCK = 3;
    private const MAX_DAILY_ATTEMPTS = 20;
    private const CACHE_PREFIX = 'login_attempts_';
    private const CACHE_DURATION = 86400; // 24 hours

    public function incrementLoginAttempt($userId, $mobile, $doctorId = null, $secretaryId = null, $managerId = null, $medicalCenterId = null)
    {
        // بررسی محدودیت روزانه
        if ($this->isDailyLimitExceeded($mobile)) {
            Log::warning("Daily login attempts limit exceeded for mobile: $mobile");
            return false;
        }

        // تعیین نوع کاربر و ID مربوطه
        $attemptableType = null;
        $attemptableId = null;

        if ($doctorId) {
            $attemptableType = Doctor::class;
            $attemptableId = $doctorId;
        } elseif ($secretaryId) {
            $attemptableType = Secretary::class;
            $attemptableId = $secretaryId;
        } elseif ($managerId) {
            $attemptableType = Manager::class;
            $attemptableId = $managerId;
        } elseif ($medicalCenterId) {
            $attemptableType = MedicalCenter::class;
            $attemptableId = $medicalCenterId;
        } elseif ($userId) {
            $attemptableType = User::class;
            $attemptableId = $userId;
        } else {
            // اگر هیچ نوع کاربری مشخص نشده، از نوع عمومی استفاده کن
            $attemptableType = 'App\Models\Guest';
            $attemptableId = 0;
        }

        $attempt = LoginAttempt::firstOrCreate(
            ['mobile' => $mobile],
            [
                'attemptable_type' => $attemptableType,
                'attemptable_id' => $attemptableId,
                'attempts' => 0,
                'last_attempt_at' => null,
                'lockout_until' => null
            ]
        );

        // به‌روزرسانی نوع کاربر در صورت تغییر
        if ($attemptableType && $attemptableId) {
            $attempt->attemptable_type = $attemptableType;
            $attempt->attemptable_id = $attemptableId;
        }

        // بررسی قفل فعلی
        if ($attempt->lockout_until && $attempt->lockout_until > now()) {
            return false;
        }

        // ریست تلاش‌ها بعد از اتمام قفل
        if ($attempt->lockout_until && $attempt->lockout_until <= now()) {
            $attempt->attempts = 2;
            $attempt->lockout_until = null;
        }

        $attempt->attempts++;
        $attempt->last_attempt_at = now();

        // افزایش تعداد تلاش‌های روزانه
        $this->incrementDailyAttempts($mobile);

        if ($attempt->attempts >= self::MAX_ATTEMPTS_BEFORE_LOCK) {
            $lockDuration = $this->calculateLockDuration($attempt->attempts);
            $attempt->lockout_until = now()->addMinutes($lockDuration);

            // ثبت لاگ برای تلاش‌های مشکوک
            if ($attempt->attempts >= 5) {
                Log::warning("Suspicious login attempts detected", [
                    'mobile' => $mobile,
                    'attempts' => $attempt->attempts,
                    'lock_duration' => $lockDuration
                ]);
            }
        }

        $attempt->save();
        return $attempt;
    }

    public function resetLoginAttempts($mobile)
    {
        $attempt = LoginAttempt::where('mobile', $mobile)->first();
        if ($attempt) {
            $attempt->update([
                'attempts' => 0,
                'last_attempt_at' => null,
                'lockout_until' => null
            ]);

            // ریست کردن تعداد تلاش‌های روزانه
            $this->resetDailyAttempts($mobile);

            Log::info("Login attempts reset for mobile: $mobile");
        }
    }

    public function isLocked($mobile)
    {
        $attempt = LoginAttempt::where('mobile', $mobile)->first();

        if (!$attempt) {
            return false;
        }

        // بررسی منقضی شدن قفل
        if ($attempt->lockout_until && $attempt->lockout_until <= now()) {
            $this->resetLoginAttempts($mobile);
            Log::info("Lock expired and reset for mobile: $mobile");
            return false;
        }

        $isLocked = $attempt->lockout_until && $attempt->lockout_until > now();
        Log::info("isLocked for mobile $mobile: ", [
            'attempt_exists' => !!$attempt,
            'lockout_until' => $attempt?->lockout_until,
            'now' => now(),
            'is_locked' => $isLocked
        ]);

        return $isLocked;
    }

    public function getRemainingLockTime($mobile)
    {
        $attempt = LoginAttempt::where('mobile', $mobile)->first();
        if ($attempt && $attempt->lockout_until && $attempt->lockout_until > now()) {
            return now()->diffInSeconds($attempt->lockout_until);
        }
        return 0;
    }

    public function getRemainingLockTimeFormatted($mobile)
    {
        $seconds = $this->getRemainingLockTime($mobile);
        if ($seconds <= 0) {
            return "قفل باز شده است";
        }

        $minutes = ceil($seconds / 60);

        if ($minutes > 59) {
            $hours = floor($minutes / 60);
            return "$hours ساعت";
        }

        return "$minutes دقیقه";
    }

    // متدهای جدید برای مدیریت تلاش‌های روزانه
    private function isDailyLimitExceeded($mobile)
    {
        $dailyAttempts = Cache::get(self::CACHE_PREFIX . $mobile, 0);
        return $dailyAttempts >= self::MAX_DAILY_ATTEMPTS;
    }

    private function incrementDailyAttempts($mobile)
    {
        $key = self::CACHE_PREFIX . $mobile;
        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, self::CACHE_DURATION);
    }

    private function resetDailyAttempts($mobile)
    {
        Cache::forget(self::CACHE_PREFIX . $mobile);
    }

    private function calculateLockDuration($attempts)
    {
        // افزایش مدت قفل با افزایش تعداد تلاش‌ها
        if ($attempts >= 10) {
            return 60; // 1 ساعت
        } elseif ($attempts >= 7) {
            return 30; // 30 دقیقه
        } elseif ($attempts >= 5) {
            return 15; // 15 دقیقه
        } else {
            return 5; // 5 دقیقه
        }
    }
}
