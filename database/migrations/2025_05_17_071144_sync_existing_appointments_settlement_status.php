<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Appointment;
use App\Models\CounselingAppointment;
use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;

return new class extends Migration {
    public function up(): void
    {
        // آپدیت settlement_status برای نوبت‌های حضوری
        Appointment::where('payment_status', 'paid')
            ->whereIn('status', ['attended'])
            ->whereNull('settlement_status')
            ->whereNull('deleted_at')
            ->update(['settlement_status' => 'pending']);

        // آپدیت کیف پول برای نوبت‌های حضوری
        $appointments = Appointment::where('payment_status', 'paid')
            ->whereIn('status', ['attended'])
            ->where('settlement_status', 'pending')
            ->whereNull('deleted_at')
            ->get();

        foreach ($appointments as $appointment) {
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

        // آپدیت settlement_status برای مشاوره‌های آنلاین
        CounselingAppointment::where('payment_status', 'paid')
            ->whereIn('status', ['call_completed', 'video_completed', 'text_completed'])
            ->whereNull('settlement_status')
            ->whereNull('deleted_at')
            ->update(['settlement_status' => 'pending']);

        // آپدیت کیف پول برای مشاوره‌های آنلاین
        $counselingAppointments = CounselingAppointment::where('payment_status', 'paid')
            ->whereIn('status', ['call_completed', 'video_completed', 'text_completed'])
            ->where('settlement_status', 'pending')
            ->whereNull('deleted_at')
            ->get();

        foreach ($counselingAppointments as $appointment) {
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

    public function down(): void
    {
        DoctorWalletTransaction::whereIn('type', ['in_person', 'online'])
            ->where('status', 'available')
            ->delete();
        DoctorWallet::query()->update(['balance' => 0]);
        Appointment::where('settlement_status', 'pending')
            ->update(['settlement_status' => null]);
        CounselingAppointment::where('settlement_status', 'pending')
            ->update(['settlement_status' => null]);
    }
};