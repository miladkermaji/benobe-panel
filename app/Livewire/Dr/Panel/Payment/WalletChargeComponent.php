<?php

namespace App\Livewire\Dr\Panel\Payment;

use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Payment\Services\PaymentService;

class WalletChargeComponent extends Component
{
    public $amount        = 0;
    public $displayAmount = '';
    public $isLoading     = false;
    public $transactionId;

    protected $paymentService;

    public function boot(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    protected $rules = [
        'amount' => 'required|integer|min:1000',
    ];

    protected $messages = [
        'amount.required' => 'لطفاً مبلغ شارژ را وارد کنید.',
        'amount.integer'  => 'مبلغ شارژ باید یک عدد صحیح باشد.',
        'amount.min'      => 'مبلغ شارژ باید حداقل ۱,۰۰۰ تومان باشد.',
    ];

    public function mount()
    {
        $this->amount        = 0;
        $this->displayAmount = '';
        $this->transactionId = null;
    }

    public function render()
    {
        $doctorId     = Auth::guard('doctor')->user()->id;
        $transactions = DoctorWalletTransaction::where('doctor_id', $doctorId)
            ->latest()
            ->take(10)
            ->get();
        $wallet          = DoctorWallet::firstOrCreate(['doctor_id' => $doctorId], ['balance' => 0]);
        $availableAmount = $wallet->balance;

        if (session('success')) {
            $this->dispatch('toast', message: session('success'), type: 'success');
        } elseif (session('error')) {
            $this->dispatch('toast', message: session('error'), type: 'error');
        }

        return view('livewire.dr.panel.payment.wallet-charge-component', [
            'transactions'    => $transactions,
            'availableAmount' => $availableAmount,
        ]);
    }

    public function chargeWallet()
    {
        $this->validate();

        $this->isLoading = true;

        $doctorId    = Auth::guard('doctor')->user()->id;
        $callbackUrl = route('payment.callback');

        try {
            $activeGateway = \App\Models\PaymentGateway::active()->first();

            if (!$activeGateway) {
                $this->isLoading = false;
                $this->dispatch('toast', message: 'درگاه پرداخت فعالی یافت نشد. لطفاً با پشتیبانی تماس بگیرید.', type: 'error');
                return;
            }

       

            $paymentResponse = $this->paymentService->pay(
                $this->amount,
                $callbackUrl,
                [
                    'doctor_id'   => $doctorId,
                    'type'        => 'wallet_charge',
                    'description' => 'شارژ کیف پول',
                ]
            );


            if ($paymentResponse instanceof \Illuminate\Http\RedirectResponse) {
                return $paymentResponse;
            } elseif ($paymentResponse instanceof Redirector) {
                return $paymentResponse;
            } elseif (method_exists($paymentResponse, 'getAction')) {
                $this->dispatch('redirect-to-gateway', url: $paymentResponse->getAction());
            } elseif (is_string($paymentResponse)) {
                $this->dispatch('redirect-to-gateway', url: $paymentResponse);
            } else {
                throw new \Exception('پاسخ درگاه پرداخت نامعتبر است: نوع پاسخ پشتیبانی نمی‌شود.');
            }
        } catch (\Exception $e) {
       
            $this->isLoading = false;
            $this->dispatch('toast', message: 'خطایی در فرآیند پرداخت رخ داد: ' . $e->getMessage(), type: 'error');
        }

        $this->reset(['amount', 'displayAmount']);
    }

    public function verifyPayment()
    {
        $transaction = $this->paymentService->verify();

        $this->isLoading = false;

        if ($transaction) {
            $doctorId = Auth::guard('doctor')->user()->id;
            $meta = json_decode($transaction->meta, true);

            if (
                $transaction->transactable_type === 'App\Models\Doctor' &&
                $transaction->transactable_id === $doctorId &&
                ($meta['type'] ?? '') === 'wallet_charge'
            ) {
                $wallet = DoctorWallet::firstOrCreate(['doctor_id' => $doctorId], ['balance' => 0]);
                $wallet->increment('balance', $transaction->amount);

                // ثبت تراکنش در جدول DoctorWalletTransaction
                DoctorWalletTransaction::create([
                    'doctor_id'    => $doctorId,
                    'amount'       => $transaction->amount,
                    'status'       => 'paid',
                    'type'         => 'wallet_charge',
                    'description'  => 'شارژ کیف پول',
                    'registered_at' => now(),
                    'paid_at'      => now(),
                ]);

                return redirect()->route('doctor.wallet')->with('success', 'کیف‌پول شما با موفقیت شارژ شد.');
            }

            return redirect()->route('doctor.wallet')->with('error', 'تراکنش یافت شد اما با شارژ کیف‌پول شما مطابقت ندارد.');
        }

        return redirect()->route('doctor.wallet')->with('error', 'پرداخت ناموفق بود.');
    }

    public function updatedDisplayAmount($value)
    {
        $this->amount = $value ? (int) str_replace(',', '', $value) : 0;
    }
}
