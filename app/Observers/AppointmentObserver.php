<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\DoctorWallet;
use Illuminate\Support\Facades\Cache;
use App\Models\DoctorWalletTransaction;

class AppointmentObserver
{
    public function created(Appointment $appointment)
    {
        $this->invalidateCache($appointment);
    }

    public function updated(Appointment $appointment)
    {
        $this->invalidateCache($appointment);

        if (
            $appointment->payment_status === 'paid' &&
            $appointment->status === 'attended' &&
            $appointment->settlement_status === 'pending' &&
            $appointment->wasChanged('status')
        ) {
            $doctorId = $appointment->doctor_id;
            $amount = $appointment->final_price;

            $wallet = DoctorWallet::firstOrCreate(
                ['doctor_id' => $doctorId],
                ['balance' => 0]
            );
            $wallet->increment('balance', $amount);

            DoctorWalletTransaction::create([
                'doctor_id'    => $doctorId,
                'amount'       => $amount,
                'status'       => 'available',
                'type'         => 'in_person',
                'description'  => 'درآمد نوبت حضوری (کد: ' . ($appointment->tracking_code ?? $appointment->id) . ')',
                'registered_at' => now(),
            ]);
        }

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
