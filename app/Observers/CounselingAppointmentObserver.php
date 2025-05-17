<?php

namespace App\Observers;

use App\Models\CounselingAppointment;
use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;

class CounselingAppointmentObserver
{
    public function updated(CounselingAppointment $appointment)
    {
        if (
            $appointment->payment_status === 'paid' &&
            in_array($appointment->status, ['call_completed', 'video_completed', 'text_completed']) &&
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
                'type'         => 'online',
                'description'  => 'درآمد مشاوره آنلاین (کد: ' . ($appointment->tracking_code ?? $appointment->id) . ')',
                'registered_at' => now(),
            ]);
        }
    }
}
