<?php

namespace App\Livewire\Dr\Panel\Payment;

use App\Models\DoctorPaymentSetting;
use App\Models\DoctorSettlementRequest;
use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PaymentSettingComponent extends Component
{
    public $visit_fee = 20000; // پیش‌فرض 20 هزار تومان
    public $card_number;
    public $requests = [];
    public function mount()
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $settings = DoctorPaymentSetting::where('doctor_id', $doctorId)->first();

        if (! $settings) {
            DoctorPaymentSetting::create([
                'doctor_id'   => $doctorId,
                'visit_fee'   => $this->visit_fee,
                'card_number' => null,
            ]);
        } else {
            $this->visit_fee   = $settings->visit_fee; // مقدار خام
            $this->card_number = $settings->card_number;
        }
    }

    public function render()
    {
        $doctorId    = Auth::guard('doctor')->user()->id;
        $totalIncome = DoctorWallet::where('doctor_id', $doctorId)->sum('balance');
        $paid        = DoctorWalletTransaction::where('doctor_id', $doctorId)->where('status', 'paid')->sum('amount');
        $available   = DoctorWallet::where('doctor_id', $doctorId)->sum('balance');
        $this->loadData();
        return view('livewire.dr.panel.payment.payment-setting-component', [
            'totalIncome'         => $totalIncome,
            'paid'                => $paid,
            'available'           => $available,
            'formatted_visit_fee' => number_format($this->visit_fee), // فرمت‌شده برای نمایش
        ]);
    }
    public function deleteRequest($requestId)
    {
        $doctorId    = Auth::guard('doctor')->user()->id;
        $transaction = DoctorSettlementRequest::where('doctor_id', $doctorId)->where('id', $requestId)->first();

        if ($transaction) {
            $transaction->delete(); // حذف نرم
            $this->dispatch('toast', message: 'درخواست با موفقیت حذف شد.');
        } else {
            $this->dispatch('toast', message: 'درخواست یافت نشد!');
        }

        $this->loadData();
    }
    public function requestSettlement()
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $settings = DoctorPaymentSetting::where('doctor_id', $doctorId)->first();

        if (empty($this->card_number)) {
            $this->dispatch('toast', message: 'لطفاً شماره کارت را وارد کنید.');
            return;
        }

        // چک کردن درخواست فعال
        $existingRequest = DoctorSettlementRequest::where('doctor_id', $doctorId)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
        if ($existingRequest) {
            $this->dispatch('toast', message: 'شما یک درخواست تسویه فعال دارید. لطفاً منتظر پردازش باشید.');
            return;
        }

        $availableAmount = DoctorWallet::where('doctor_id', $doctorId)

            ->sum('balance');
        if ($availableAmount <= 0) {
            $this->dispatch('toast', message: 'مبلغ قابل برداشت وجود ندارد.');
            return;
        }

        $settings->update([
            'visit_fee'   => $this->visit_fee,
            'card_number' => $this->card_number,
        ]);

        DoctorSettlementRequest::create([
            'doctor_id' => $doctorId,
            'amount'    => $availableAmount,
            'status'    => 'pending',
        ]);

        DoctorWalletTransaction::where('doctor_id', $doctorId)
            ->where('status', 'available')
            ->update(['status' => 'requested']);

        $this->dispatch('toast', message: 'درخواست تسویه حساب با موفقیت ثبت شد.');
    }
    protected function loadData()
    {
        $doctorId       = Auth::guard('doctor')->user()->id;
        $this->requests = DoctorSettlementRequest::where('doctor_id', $doctorId)
            ->latest()->with('doctor')
            ->get();
        $wallet                = DoctorWallet::where('doctor_id', $doctorId)->firstOrCreate(['doctor_id' => $doctorId], ['balance' => 0]);
        $this->availableAmount = $wallet->balance;
    }
}
