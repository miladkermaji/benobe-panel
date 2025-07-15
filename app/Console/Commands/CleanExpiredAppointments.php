<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\CounselingAppointment;
use Carbon\Carbon;

class CleanExpiredAppointments extends Command
{
    protected $signature = 'appointments:clean-expired';
    protected $description = 'حذف کامل نوبت‌های منقضی‌شده و پرداخت‌نشده (force delete)';

    public function handle()
    {
        $now = Carbon::now('Asia/Tehran');

        // فقط نوبت‌های scheduled و pending که منقضی شده‌اند حذف شوند
        $expiredAppointments = Appointment::where('status', 'scheduled')
            ->where('payment_status', 'pending')
            ->where('reserved_at', '<', $now->subMinutes(10))
            ->get();

        $count = 0;
        foreach ($expiredAppointments as $appointment) {
            $appointment->forceDelete();
            $count++;
        }

        $expiredCounseling = CounselingAppointment::where('status', 'scheduled')
            ->where('payment_status', 'pending')
            ->where('reserved_at', '<', $now->subMinutes(10))
            ->get();

        foreach ($expiredCounseling as $appointment) {
            $appointment->forceDelete();
            $count++;
        }

        $this->info("تعداد $count نوبت منقضی‌شده به طور کامل حذف شد.");
    }
}
