<?php

namespace App\Livewire\Dr\Panel\Payment;

use App\Models\DoctorWallet;
use App\Models\Transaction;
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
        $transactions = Transaction::where('transactable_type', 'App\Models\Doctor')
            ->where('transactable_id', $doctorId)
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

            Log::info('Attempting payment with gateway:', [
                'gateway'  => $activeGateway->name,
                'settings' => $activeGateway->settings,
                'amount'   => $this->amount,
                'callback' => $callbackUrl,
            ]);

            $paymentResponse = $this->paymentService->pay(
                $this->amount,
                $callbackUrl,
                [
                    'doctor_id'   => $doctorId,
                    'type'        => 'wallet_charge',
                    'description' => 'شارژ کیف پول',
                ]
            );

            Log::info('Payment Response:', [
                'response' => $paymentResponse,
                'type'     => gettype($paymentResponse),
                'class'    => is_object($paymentResponse) ? get_class($paymentResponse) : null,
            ]);

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
            Log::error('Payment Error Details:', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
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

            // چک کردن اینکه تراکنش برای این دکتر و شارژ کیف‌پول باشه
            $meta = json_decode($transaction->meta, true);
            if (
                $transaction->transactable_type === 'App\Models\Doctor' &&
                $transaction->transactable_id === $doctorId &&
                ($meta['type'] ?? '') === 'wallet_charge'
            ) {
                $wallet = DoctorWallet::firstOrCreate(['doctor_id' => $doctorId], ['balance' => 0]);
                $wallet->increment('balance', $transaction->amount);

                return redirect()->route('doctor.wallet')->with('success', 'کیف‌پول شما با موفقیت شارژ شد.');
            }

            return redirect()->route('doctor.wallet')->with('error', 'تراکنش یافت شد اما با شارژ کیف‌پول شما مطابقت ندارد.');
        }

        return redirect()->route('doctor.wallet')->with('error', 'پرداخت ناموفق بود.');
    }

    public function deleteTransaction($transactionId)
    {
        $doctorId    = Auth::guard('doctor')->user()->id;
        $transaction = Transaction::where('transactable_type', 'App\Models\Doctor')
            ->where('transactable_id', $doctorId)
            ->where('id', $transactionId)
            ->first();

        if ($transaction) {
            $transaction->delete();
            $this->dispatch('toast', message: 'تراکنش با موفقیت حذف شد.', type: 'success');
        } else {
            $this->dispatch('toast', message: 'تراکنش یافت نشد!', type: 'error');
        }
    }

    public function updatedDisplayAmount($value)
    {
        $this->amount = $value ? (int) str_replace(',', '', $value) : 0;
    }
}
