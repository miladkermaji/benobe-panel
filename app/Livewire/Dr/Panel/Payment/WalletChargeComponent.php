<?php

namespace App\Livewire\Dr\Panel\Payment;

use Livewire\Component;
use App\Models\DoctorWallet;
use App\Models\Dr\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorWalletTransaction;
use Modules\Payment\Services\PaymentService;
use Livewire\Features\SupportRedirects\Redirector;

class WalletChargeComponent extends Component
{
    public $amount = 0;
    public $displayAmount = '';
    public $isLoading = false;
    public $transactionId;

    protected $paymentService;

    public function boot(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    protected $rules = [
        'amount' => 'required|integer|min:1000',
    ];

    public function mount()
    {
        $this->amount = 0;
        $this->displayAmount = '';
        $this->transactionId = null;
    }

    public function render()
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $transactions = DoctorWalletTransaction::where('doctor_id', $doctorId)
            ->latest()
            ->take(10)
            ->get();
        $wallet = DoctorWallet::firstOrCreate(['doctor_id' => $doctorId], ['balance' => 0]);
        $availableAmount = $wallet->balance;

        if (session('success')) {
            $this->dispatch('toast', message: session('success'));
        } elseif (session('error')) {
            $this->dispatch('toast', message: session('error'));
        }

        return view('livewire.dr.panel.payment.wallet-charge-component', [
            'transactions' => $transactions,
            'availableAmount' => $availableAmount,
        ]);
    }

    public function chargeWallet()
    {
        $this->validate();

        $this->isLoading = true;

        $doctorId = Auth::guard('doctor')->user()->id;
        $callbackUrl = route('payment.callback');

        $transaction = DoctorWalletTransaction::create([
            'doctor_id' => $doctorId,
            'amount' => $this->amount,
            'status' => 'pending',
            'type' => 'charge',
            'description' => "شارژ کیف پول",
            'registered_at' => now(),
        ]);

        $this->transactionId = $transaction->id;

        try {
            $activeGateway = \App\Models\Dr\PaymentGateway::active()->first();
            Log::info('Attempting payment with gateway:', [
                'gateway' => $activeGateway->name,
                'settings' => $activeGateway->settings,
                'amount' => $this->amount,
                'callback' => $callbackUrl,
            ]);

            $paymentResponse = $this->paymentService->pay(
                $this->amount,
                $callbackUrl,
                [
                    'doctor_id' => $doctorId,
                    'transaction_id' => $transaction->id,
                    'type' => 'wallet_charge',
                ]
            );

            Log::info('Payment Response:', [
                'response' => $paymentResponse,
                'type' => gettype($paymentResponse),
                'class' => is_object($paymentResponse) ? get_class($paymentResponse) : null,
            ]);

            if ($paymentResponse instanceof \Illuminate\Http\RedirectResponse) {
                return $paymentResponse;
            } elseif ($paymentResponse instanceof Redirector) {
                return $paymentResponse; // هندل کردن Redirector برای Livewire
            } elseif (method_exists($paymentResponse, 'getAction')) {
                $this->dispatch('redirect-to-gateway', url: $paymentResponse->getAction());
            } elseif (is_string($paymentResponse)) {
                $this->dispatch('redirect-to-gateway', url: $paymentResponse);
            } else {
                throw new \Exception('پاسخ درگاه پرداخت نامعتبر است: نوع پاسخ پشتیبانی نمی‌شود.');
            }
        } catch (\Exception $e) {
            Log::error('Payment Error Details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->isLoading = false;
            $this->dispatch('toast', message: 'خطایی در فرآیند پرداخت رخ داد: ' . $e->getMessage());
        }

        $this->reset(['amount', 'displayAmount']);
    }

    public function verifyPayment()
    {
        $transaction = $this->paymentService->verify();

        $this->isLoading = false;

        if ($transaction) {
            $doctorId = Auth::guard('doctor')->user()->id;
            $walletTransaction = DoctorWalletTransaction::where('id', $transaction->meta['transaction_id'])
                ->where('doctor_id', $doctorId)
                ->first();

            if ($walletTransaction && $walletTransaction->status === 'pending') {
                $walletTransaction->update(['status' => 'available']);

                $wallet = DoctorWallet::firstOrCreate(['doctor_id' => $doctorId], ['balance' => 0]);
                $wallet->increment('balance', $walletTransaction->amount);

                return redirect()->route('doctor.wallet')->with('success', 'کیف‌پول شما با موفقیت شارژ شد.');
            }

            return redirect()->route('doctor.wallet')->with('error', 'تراکنش قبلاً تأیید شده یا یافت نشد.');
        }

        return redirect()->route('doctor.wallet')->with('error', 'پرداخت ناموفق بود.');
    }

    public function deleteTransaction($transactionId)
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $transaction = DoctorWalletTransaction::where('doctor_id', $doctorId)
            ->where('id', $transactionId)
            ->first();

        if ($transaction) {
            $transaction->delete();
            $this->dispatch('toast', message: 'تراکنش با موفقیت حذف شد.');
        } else {
            $this->dispatch('toast', message: 'تراکنش یافت نشد!');
        }
    }

    public function updatedDisplayAmount($value)
    {
        $this->amount = $value ? (int) str_replace(',', '', $value) : 0;
    }
}
