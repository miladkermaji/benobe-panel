<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UserBlocking;
use Carbon\Carbon;
use App\Jobs\SendSmsNotificationJob;

class CheckUserBlockingsExpiration implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        $now = Carbon::now();

        // Find all active blockings that have reached their end date
        $expiredBlockings = UserBlocking::where('status', true)
            ->whereNotNull('unblocked_at')
            ->where('unblocked_at', '<=', $now)
            ->get();

        foreach ($expiredBlockings as $blocking) {
            $blocking->update([
                'status' => false
            ]);

            // Dispatch notification if needed
            if ($blocking->type === 'user') {
                $user = $blocking->user;
                if ($user) {
                    $message = "کاربر گرامی، مسدودیت شما به پایان رسیده است.";
                    SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
                }
            } else {
                $doctor = $blocking->doctor;
                if ($doctor) {
                    $message = "دکتر گرامی، مسدودیت شما به پایان رسیده است.";
                    SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
                }
            }
        }

        // Find all blockings that should be activated based on start date
        $blockingsToActivate = UserBlocking::where('status', false)
            ->whereNotNull('blocked_at')
            ->where('blocked_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('unblocked_at')
                    ->orWhere('unblocked_at', '>', $now);
            })
            ->get();

        foreach ($blockingsToActivate as $blocking) {
            $blocking->update([
                'status' => true
            ]);

            // Dispatch notification if needed
            if ($blocking->type === 'user') {
                $user = $blocking->user;
                if ($user) {
                    $message = "کاربر گرامی، مسدودیت شما فعال شده است.";
                    SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
                }
            } else {
                $doctor = $blocking->doctor;
                if ($doctor) {
                    $message = "دکتر گرامی، مسدودیت شما فعال شده است.";
                    SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
                }
            }
        }
    }
}
