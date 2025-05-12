<?php


namespace App\Observers;

use App\Models\Appointment;
use Illuminate\Support\Facades\Cache;

class AppointmentObserver
{
    public function created(Appointment $appointment)
    {
        $this->invalidateCache($appointment);
    }

    public function updated(Appointment $appointment)
    {
        $this->invalidateCache($appointment);
    }

    public function deleted(Appointment $appointment)
    {
        $this->invalidateCache($appointment);
    }

    protected function invalidateCache(Appointment $appointment)
    {
        $doctorId = $appointment->doctor_id;
        $clinicId = $appointment->clinic_id ?? 'default';
        // به جای تگ‌ها، کلیدهای مرتبط رو پاک می‌کنیم
        Cache::forget("appointments_doctor_{$doctorId}_clinic_{$clinicId}_*");
    }
}
