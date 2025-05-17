<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Appointment;
use App\Models\CounselingAppointment;
use App\Models\DoctorSettlementRequest;
use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;

return new class () extends Migration {
    public function up(): void
    {
        // پیدا کردن دکترهایی که نوبت settled دارن
        $doctors = Appointment::where('settlement_status', 'settled')
            ->pluck('doctor_id')
            ->merge(CounselingAppointment::where('settlement_status', 'settled')->pluck('doctor_id'))
            ->unique();

        foreach ($doctors as $doctorId) {
            // چک کردن اینکه درخواست تسویه فعال (pending یا approved) وجود نداره
            $hasActiveRequest = DoctorSettlementRequest::where('doctor_id', $doctorId)
                ->whereIn('status', ['pending', 'approved'])
                ->whereNull('deleted_at')
                ->exists();

            if ($hasActiveRequest) {
                continue; // اگه درخواست فعال داره، کاری نمی‌کنیم
            }

            // جمع مبلغ نوبت‌های settled
            $inPersonAmount = Appointment::where('doctor_id', $doctorId)
                ->where('payment_status', 'paid')
                ->whereIn('status', ['attended'])
                ->where('settlement_status', 'settled')
                ->whereNull('deleted_at')
                ->sum('final_price');

            $onlineAmount = CounselingAppointment::where('doctor_id', $doctorId)
                ->where('payment_status', 'paid')
                ->whereIn('status', ['call_completed', 'video_completed', 'text_completed'])
                ->where('settlement_status', 'settled')
                ->whereNull('deleted_at')
                ->sum('final_price');

            $totalAmount = $inPersonAmount + $onlineAmount;

            if ($totalAmount > 0) {
                // بازگشت settlement_status به pending
                Appointment::where('doctor_id', $doctorId)
                    ->where('payment_status', 'paid')
                    ->whereIn('status', ['attended'])
                    ->where('settlement_status', 'settled')
                    ->update(['settlement_status' => 'pending']);

                CounselingAppointment::where('doctor_id', $doctorId)
                    ->where('payment_status', 'paid')
                    ->whereIn('status', ['call_completed', 'video_completed', 'text_completed'])
                    ->where('settlement_status', 'settled')
                    ->update(['settlement_status' => 'pending']);

                // بازگشت مبلغ به کیف پول
                $wallet = DoctorWallet::firstOrCreate(
                    ['doctor_id' => $doctorId],
                    ['balance' => 0]
                );
                $wallet->increment('balance', $totalAmount);

                // ثبت تراکنش برای بازگشت
                DoctorWalletTransaction::create([
                    'doctor_id'    => $doctorId,
                    'amount'       => $totalAmount,
                    'status'       => 'available',
                    'type'         => 'settlement_reversal',
                    'description'  => 'بازگشت مبلغ به دلیل فقدان درخواست تسویه',
                    'registered_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $doctors = Appointment::where('settlement_status', 'pending')
            ->pluck('doctor_id')
            ->merge(CounselingAppointment::where('settlement_status', 'pending')->pluck('doctor_id'))
            ->unique();

        foreach ($doctors as $doctorId) {
            Appointment::where('doctor_id', $doctorId)
                ->where('payment_status', 'paid')
                ->whereIn('status', ['attended'])
                ->where('settlement_status', 'pending')
                ->update(['settlement_status' => 'settled']);

            CounselingAppointment::where('doctor_id', $doctorId)
                ->where('payment_status', 'paid')
                ->whereIn('status', ['call_completed', 'video_completed', 'text_completed'])
                ->where('settlement_status', 'pending')
                ->update(['settlement_status' => 'settled']);
        }

        DoctorWalletTransaction::where('type', 'settlement_reversal')->delete();
        DoctorWallet::query()->update(['balance' => 0]);
    }
};
