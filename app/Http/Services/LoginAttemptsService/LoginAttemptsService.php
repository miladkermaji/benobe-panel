<?php

namespace App\Http\Services\LoginAttemptsService;

use App\Models\Dr\LoginAttempt;

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

    // بررسی اینکه آیا قبلاً قفل شده است
    if ($attempt->lockout_until && $attempt->lockout_until > now()) {
      return false;
    }

    // به‌روزرسانی مقادیر
    $attempt->doctor_id = $doctorId ?: null;
    $attempt->secratary_id = $secretaryId ?: null;
    $attempt->manager_id = $managerId ?: null;

    // افزایش تعداد تلاش‌ها
    $attempt->attempts++;
    $attempt->last_attempt_at = now();

    if ($attempt->attempts >= 3) {
      $lockDuration = match ($attempt->attempts) {
        3 => 5,
        4 => 30,
        5 => 60,
        6 => 120,
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

 // متد برای بررسی اینکه آیا کاربر قفل شده است
 public function isLocked($mobile)
 {
  $attempt = LoginAttempt::where('mobile', $mobile)->first();

  return $attempt &&
   $attempt->lockout_until &&
   $attempt->lockout_until > now();
 }

 // متد برای دریافت زمان باقی‌مانده تا رفع قفل
 public function getRemainingLockTime($mobile)
 {
  $attempt = LoginAttempt::where('mobile', $mobile)->first();
  if ($attempt && $attempt->lockout_until && $attempt->lockout_until > now()) {
   return now()->diffInSeconds($attempt->lockout_until);
  }
  return 0;
 }
}