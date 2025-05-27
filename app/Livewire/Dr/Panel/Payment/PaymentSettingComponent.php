<?php

namespace App\Livewire\Dr\Panel\Payment;

use App\Models\Appointment;
use App\Models\CounselingAppointment;
use App\Models\DoctorPaymentSetting;
use App\Models\DoctorSettlementRequest;
use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PaymentSettingComponent extends Component
{
    public $visit_fee = 20000;
    public $card_number;
    public $availableAmount;
    public $requests = [];

    public function mount()
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }

        if (!$doctor) {
            return redirect()->route('login');
        }

        $doctorId = $doctor->id;
        $settings = DoctorPaymentSetting::where('doctor_id', $doctorId)->first();

        if (!$settings) {
            DoctorPaymentSetting::create([
                'doctor_id'   => $doctorId,
                'visit_fee'   => $this->visit_fee,
                'card_number' => null,
            ]);
        } else {
            $this->visit_fee   = $settings->visit_fee;
            $this->card_number = $settings->card_number;
        }
    }

    public function render()
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }

        if (!$doctor) {
            return redirect()->route('login');
        }

        $doctorId = $doctor->id;
        $totalIncome = $this->calculateTotalIncome($doctorId);
        $paid = DoctorSettlementRequest::where('doctor_id', $doctorId)
            ->where('status', 'paid')
            ->sum('amount');
        $available = $this->calculateAvailableAmount($doctorId);
        $this->loadData();

        return view('livewire.dr.panel.payment.payment-setting-component', [
            'totalIncome'         => $totalIncome,
            'paid'                => $paid,
            'available'           => $available,
            'formatted_visit_fee' => number_format($this->visit_fee),
        ]);
    }

    public function deleteRequest($requestId)
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }

        if (!$doctor) {
            return redirect()->route('login');
        }

        $doctorId = $doctor->id;
        $request = DoctorSettlementRequest::where('doctor_id', $doctorId)->where('id', $requestId)->first();

        if (!$request) {
            $this->dispatch('toast', message: 'درخواست یافت نشد!', type: 'error');
            return;
        }

        if ($request->status !== 'pending') {
            $this->dispatch('toast', message: 'فقط درخواست‌های در انتظار قابل حذف هستند.', type: 'error');
            return;
        }

        // بازگشت settlement_status نوبت‌ها به pending
        Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['attended'])
            ->where('settlement_status', 'settled')
            ->where('updated_at', '>=', $request->created_at)
            ->update(['settlement_status' => 'pending']);

        CounselingAppointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['call_completed', 'video_completed', 'text_completed'])
            ->where('settlement_status', 'settled')
            ->where('updated_at', '>=', $request->created_at)
            ->update(['settlement_status' => 'pending']);

        // بازگشت مبلغ به کیف پول
        $wallet = DoctorWallet::firstOrCreate(
            ['doctor_id' => $doctorId],
            ['balance' => 0]
        );
        $wallet->increment('balance', $request->amount);

        // حذف یا غیرفعال کردن تراکنش تسویه
        DoctorWalletTransaction::where('doctor_id', $doctorId)
            ->where('type', 'settlement')
            ->where('amount', $request->amount)
            ->where('created_at', '>=', $request->created_at)
            ->delete();

        // حذف درخواست تسویه
        $request->delete();

        $this->dispatch('toast', message: 'درخواست با موفقیت حذف شد و مبلغ به موجودی بازگشت.', type: 'success');
        $this->loadData();
    }

    public function requestSettlement()
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }

        if (!$doctor) {
            return redirect()->route('login');
        }

        $doctorId = $doctor->id;
        $settings = DoctorPaymentSetting::where('doctor_id', $doctorId)->first();

        if (empty($this->card_number)) {
            $this->dispatch('toast', message: 'لطفاً شماره کارت را وارد کنید.', type: 'error');
            return;
        }

        $existingRequest = DoctorSettlementRequest::where('doctor_id', $doctorId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
        if ($existingRequest) {
            $this->dispatch('toast', message: 'شما یک درخواست تسویه فعال دارید. لطفاً منتظر پردازش باشید.', type: 'error');
            return;
        }

        $availableAmount = $this->calculateAvailableAmount($doctorId);
        if ($availableAmount <= 0) {
            $this->dispatch('toast', message: 'مبلغ قابل برداشت وجود ندارد.', type: 'error');
            return;
        }

        $wallet = DoctorWallet::firstOrCreate(
            ['doctor_id' => $doctorId],
            ['balance' => 0]
        );
        if ($wallet->balance < $availableAmount) {
            $this->dispatch('toast', message: 'موجودی کیف پول کافی نیست.', type: 'error');
            return;
        }

        $settings->update([
            'visit_fee'   => $this->visit_fee,
            'card_number' => $this->card_number,
        ]);

        DoctorSettlementRequest::create([
            'doctor_id'   => $doctorId,
            'amount'      => $availableAmount,
            'status'      => 'pending',
            'requested_at' => now(),
        ]);

        DoctorWalletTransaction::create([
            'doctor_id'    => $doctorId,
            'amount'       => $availableAmount,
            'status'       => 'requested',
            'type'         => 'settlement',
            'description'  => 'درخواست تسویه حساب',
            'registered_at' => now(),
        ]);

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

        $wallet->decrement('balance', $availableAmount);

        $this->dispatch('toast', message: 'درخواست تسویه حساب با موفقیت ثبت شد.', type: 'success');
        $this->loadData();
    }

    protected function loadData()
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }

        if (!$doctor) {
            return redirect()->route('login');
        }

        $doctorId = $doctor->id;
        $this->requests = DoctorSettlementRequest::where('doctor_id', $doctorId)
            ->latest()
            ->with('doctor')
            ->get();
        $this->availableAmount = $this->calculateAvailableAmount($doctorId);
    }

    protected function calculateAvailableAmount($doctorId)
    {
        $inPersonAmount = Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['attended'])
            ->where('settlement_status', 'pending')
            ->whereNull('deleted_at')
            ->sum('final_price');

        $onlineAmount = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['call_completed', 'video_completed', 'text_completed'])
            ->where('settlement_status', 'pending')
            ->whereNull('deleted_at')
            ->sum('final_price');

        return $inPersonAmount + $onlineAmount;
    }

    protected function calculateTotalIncome($doctorId)
    {
        $inPersonIncome = Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['attended'])
            ->whereNull('deleted_at')
            ->sum('final_price');

        $onlineIncome = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->whereIn('status', ['call_completed', 'video_completed', 'text_completed'])
            ->whereNull('deleted_at')
            ->sum('final_price');

        return $inPersonIncome + $onlineIncome;
    }
}
