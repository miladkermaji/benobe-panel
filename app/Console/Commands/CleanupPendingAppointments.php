<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\CounselingAppointment;
use Carbon\Carbon;

class CleanupPendingAppointments extends Command
{
    protected $signature = 'appointments:cleanup';
    protected $description = 'Cancel pending appointments older than 30 minutes';

    public function handle()
    {
        $threshold = Carbon::now()->subMinutes(30);

        Appointment::where('payment_status', 'pending')
            ->where('status', 'scheduled')
            ->where('created_at', '<', $threshold)
            ->update([
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
            ]);

        CounselingAppointment::where('payment_status', 'pending')
            ->where('status', 'scheduled')
            ->where('created_at', '<', $threshold)
            ->update([
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
            ]);

        $this->info('Pending appointments cleaned up successfully.');
    }
}
