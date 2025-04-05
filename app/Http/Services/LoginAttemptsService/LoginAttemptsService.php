<?php

namespace App\Http\Services\LoginAttemptsService;

use App\Models\LoginAttempt;

class LoginAttemptsService
{
    public function incrementLoginAttempt($userId, $mobile, $doctorId = null, $secretaryId = null, $managerId = null)
    {
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

        if ($attempt->lockout_until && $attempt->lockout_until > now()) {
            return false;
        }

        $attempt->doctor_id = $doctorId ?: null;
        $attempt->secratary_id = $secretaryId ?: null;
        $attempt->manager_id = $managerId ?: null;

        $attempt->attempts++;
        $attempt->last_attempt_at = now();

        if ($attempt->attempts >= 3) {
            $lockDuration = match ($attempt->attempts) {
                3 => 5,
                4 => 30,
                5 => 60,
                6 => 120,
                7 => 240,
                8 => 360,
                9 => 480,
                default => 240
            };
            $attempt->lockout_until = now()->addMinutes($lockDuration);
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
        }
    }

    public function isLocked($mobile)
    {
        $attempt = LoginAttempt::where('mobile', $mobile)->first();
        return $attempt && $attempt->lockout_until && $attempt->lockout_until > now();
    }

    public function getRemainingLockTime($mobile)
    {
        $attempt = LoginAttempt::where('mobile', $mobile)->first();
        if ($attempt && $attempt->lockout_until && $attempt->lockout_until > now()) {
            return now()->diffInSeconds($attempt->lockout_until);
        }
        return 0;
    }

    // متد جدید برای فرمت کردن زمان باقی‌مانده
    public function getRemainingLockTimeFormatted($mobile)
    {
        $seconds = $this->getRemainingLockTime($mobile);
        if ($seconds <= 0) {
            return "قفل باز شده است";
        }

        $minutes = ceil($seconds / 60); // تبدیل ثانیه به دقیقه و رند به بالا

        if ($minutes > 59) {
            $hours = floor($minutes / 60); // تبدیل به ساعت
            return "$hours ساعت";
        }

        return "$minutes دقیقه";
    }
}
