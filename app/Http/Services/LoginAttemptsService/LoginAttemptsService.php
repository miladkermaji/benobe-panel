<?php

namespace App\Http\Services\LoginAttemptsService;

use App\Models\LoginAttempt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LoginAttemptsService
{
    // تنظیمات پیش‌فرض برای محدودیت‌های تلاش
    private const MAX_ATTEMPTS_BEFORE_LOCK = 3;
    private const MAX_DAILY_ATTEMPTS = 20;
    private const CACHE_PREFIX = 'login_attempts_';
    private const CACHE_DURATION = 86400; // 24 hours

    public function incrementLoginAttempt($userId, $mobile, $doctorId = null, $secretaryId = null, $managerId = null)
    {
        // بررسی محدودیت روزانه
        if ($this->isDailyLimitExceeded($mobile)) {
            Log::warning("Daily login attempts limit exceeded for mobile: $mobile");
            return false;
        }

        $attempt = LoginAttempt::firstOrCreate(
            ['mobile' => $mobile],
            [
                'doctor_id' => $doctorId ?: null,
                'secratary_id' => $secretaryId ?: null,
                'manager_id' => $managerId ?: null,
                'attempts' => 0,
                'last_attempt_at' => null,
                'lockout_until' => null
            ]
        );

        // بررسی قفل فعلی
        if ($attempt->lockout_until && $attempt->lockout_until > now()) {
            return false;
        }

        // ریست تلاش‌ها بعد از اتمام قفل
        if ($attempt->lockout_until && $attempt->lockout_until <= now()) {
            $attempt->attempts = 2;
            $attempt->lockout_until = null;
        }

        $attempt->doctor_id = $doctorId ?: null;
        $attempt->secratary_id = $secretaryId ?: null;
        $attempt->manager_id = $managerId ?: null;

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

    // محاسبه زمان قفل بر اساس تعداد تلاش‌ها
    private function calculateLockDuration($attempts)
    {
        return match ($attempts) {
            3 => 5,      // 5 minutes
            4 => 15,     // 15 minutes
            5 => 30,     // 30 minutes
            6 => 60,     // 1 hour
            7 => 120,    // 2 hours
            8 => 240,    // 4 hours
            9 => 480,    // 8 hours
            default => 1440 // 24 hours
        };
    }
}
