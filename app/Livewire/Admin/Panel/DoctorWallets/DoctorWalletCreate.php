<?php

namespace App\Livewire\Admin\Panel\DoctorWallets;

use Livewire\Component;
use App\Models\Doctor;
use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Services\PaymentService;

class DoctorWalletCreate extends Component
{
    public $amount = 0;
    public $displayAmount = '';
    public $isLoading = false;
    public $selectedDoctorId = null; // برای ذخیره پزشک انتخاب‌شده
    public $doctors = []; // لیست پزشکان برای Select2

    protected $paymentService;

    public function boot(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    protected $rules = [
        'amount' => 'required|integer|min:1000',
        'selectedDoctorId' => 'required|exists:doctors,id',
    ];

    protected $messages = [
        'amount.required' => 'لطفاً مبلغ شارژ را وارد کنید.',
        'amount.integer' => 'مبلغ شارژ باید یک عدد صحیح باشد.',
        'amount.min' => 'مبلغ شارژ باید حداقل ۱,۰۰۰ تومان باشد.',
        'selectedDoctorId.required' => 'لطفاً یک پزشک انتخاب کنید.',
        'selectedDoctorId.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
    ];

    public function mount()
    {
        $this->amount = 0;
        $this->displayAmount = '';
        $this->doctors = Doctor::select('id', 'first_name', 'last_name')->get()->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'text' => $doctor->full_name,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.admin.panel.doctor-wallets.doctor-wallet-create');
    }

 public function chargeWallet()
{
    $this->validate();

    $this->isLoading = true;

    $callbackUrl = route('payment.callback');

    try {
        $activeGateway = \App\Models\PaymentGateway::active()->first();

        if (!$activeGateway) {
            $this->isLoading = false;
            $this->dispatch('show-alert', type: 'error', message: 'درگاه پرداخت فعالی یافت نشد. لطفاً با پشتیبانی تماس بگیرید.');
            return;
        }

        $paymentResponse = $this->paymentService->pay(
            $this->amount,
            $callbackUrl,
            [
                'doctor_id' => $this->selectedDoctorId,
                'type' => 'wallet_charge',
                'description' => 'شارژ کیف‌پول توسط ادمین',
            ]
        );

        Log::info('Payment Response:', ['response' => $paymentResponse]);

        // مدیریت پاسخ (فقط رشته انتظار داریم)
        if (is_string($paymentResponse)) {
            $this->dispatch('redirect-to-gateway', url: $paymentResponse);
            return;
        } else {
            throw new \Exception('پاسخ درگاه پرداخت نامعتبر است: ' . json_encode($paymentResponse));
        }
    } catch (\Exception $e) {
  
        $this->isLoading = false;
       /*  $this->dispatch('show-alert', type: 'error', message: 'خطایی در فرآیند پرداخت رخ داد: ' . $e->getMessage()); */
        return;
    }

    $this->reset(['amount', 'displayAmount']);
}

    public function verifyPayment()
    {
        $transaction = $this->paymentService->verify();

        $this->isLoading = false;

        if ($transaction) {
            $meta = json_decode($transaction->meta, true);

            if (($meta['type'] ?? '') === 'wallet_charge' && $meta['doctor_id'] == $this->selectedDoctorId) {
                $wallet = DoctorWallet::firstOrCreate(['doctor_id' => $this->selectedDoctorId], ['balance' => 0]);
                $wallet->increment('balance', $transaction->amount);

                DoctorWalletTransaction::create([
                    'doctor_id' => $this->selectedDoctorId,
                    'amount' => $transaction->amount,
                    'status' => 'paid',
                    'type' => 'wallet_charge',
                    'description' => 'شارژ کیف‌پول توسط ادمین',
                    'registered_at' => now(),
                    'paid_at' => now(),
                ]);

                return redirect()->route('admin.panel.doctor-wallets.index')
                    ->with('success', 'کیف‌پول پزشک با موفقیت شارژ شد.');
            }

            return redirect()->route('admin.panel.doctor-wallets.index')
                ->with('error', 'تراکنش یافت شد اما با شارژ کیف‌پول مطابقت ندارد.');
        }

        return redirect()->route('admin.panel.doctor-wallets.index')
            ->with('error', 'پرداخت ناموفق بود.');
    }

    public function updatedDisplayAmount($value)
    {
        $this->amount = $value ? (int) str_replace(',', '', $value) : 0;
    }
}
