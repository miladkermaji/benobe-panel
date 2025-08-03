<?php

namespace App\Livewire\Mc\Panel\Payment;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\DoctorWallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DoctorWalletTransaction;
use Modules\Payment\Services\PaymentService;
use Livewire\Features\SupportRedirects\Redirector;

class WalletChargeComponent extends Component
{
    public $amount        = 0;
    public $displayAmount = '';
    public $isLoading     = false;
    public $transactionId;

    private $paymentService; // از private استفاده می‌کنیم

    public function __construct()
    {
        $this->paymentService = resolve(PaymentService::class); // تزریق دستی
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
        $this->transactionId = request()->query('transaction_id') ?? request()->query('Authority');
    }

    public function render()
    {
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }
            $doctorId = $doctor ? $doctor->id : null;
        }

        if (!$doctorId) {
            if (Auth::guard('medical_center')->check()) {
                return redirect()->route('mc.auth.login-register-form');
            } else {
                return redirect()->route('dr.auth.login-register-form');
            }
        }

        $transactions = DoctorWalletTransaction::where('doctor_id', $doctorId)
            ->latest()
            ->take(10)
            ->get();
        $wallet          = DoctorWallet::firstOrCreate(['doctor_id' => $doctorId], ['balance' => 0]);
        $availableAmount = $wallet->balance;

        // بررسی پیام‌های session
        if (session('success')) {
            $this->dispatch('toast', message: session('success'), type: 'success');
        } elseif (session('error')) {
            $this->dispatch('toast', message: session('error'), type: 'error');
        }

        // بررسی پارامتر from_payment
        $fromPayment = request()->query('from_payment');
        if ($fromPayment === 'success' && $this->transactionId) {
            $transaction = \App\Models\Transaction::where('transaction_id', $this->transactionId)->first();
            if ($transaction && $transaction->status === 'paid' && json_decode($transaction->meta, true)['type'] === 'wallet_charge') {
                $this->dispatch('toast', message: 'کیف‌پول شما با موفقیت شارژ شد.', type: 'success');
            }
        } elseif ($fromPayment === 'error') {
            $this->dispatch('toast', message: 'پرداخت ناموفق بود.', type: 'error');
        }

        return view('livewire.mc.panel.payment.wallet-charge-component', [
            'transactions'    => $transactions,
            'availableAmount' => $availableAmount,
        ]);
    }

    public function chargeWallet()
    {
        $this->validate();

        $this->isLoading = true;

        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }
            $doctorId = $doctor ? $doctor->id : null;
        }

        if (!$doctorId) {
            if (Auth::guard('medical_center')->check()) {
                return redirect()->route('mc.auth.login-register-form');
            } else {
                return redirect()->route('dr.auth.login-register-form');
            }
        }

        $callbackUrl = route('payment.callback');
        $successRedirect = route('mc-wallet-verify');
        $errorRedirect = route('mc-wallet-charge') . '?from_payment=error';

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
                    'doctor_id'       => $doctorId,
                    'type'            => 'wallet_charge',
                    'description'     => 'شارژ کیف پول',
                ],
                $successRedirect,
                $errorRedirect
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
        $this->isLoading = false;

        try {
            $authority = request()->query('Authority');
            if (!$authority) {
                Log::error('WalletChargeComponent::verifyPayment - No Authority provided in request');
                return redirect()->route('mc-wallet-charge')->with('error', 'پارامتر تراکنش نامعتبر است.');
            }

            $transaction = Transaction::where('transaction_id', $authority)->first();
            if (!$transaction) {
                Log::error("WalletChargeComponent::verifyPayment - No transaction found for Authority: {$authority}");
                return redirect()->route('mc-wallet-charge')->with('error', 'تراکنش یافت نشد.');
            }

            if ($transaction->status !== 'paid') {
                Log::warning("WalletChargeComponent::verifyPayment - Transaction {$authority} is not paid: {$transaction->status}");
                return redirect()->route('mc-wallet-charge')->with('error', 'تراکنش تأیید نشده است.');
            }

            // Get doctor_id based on guard
            $doctorId = null;
            if (Auth::guard('medical_center')->check()) {
                // For medical_center guard, get the selected doctor
                $medicalCenter = Auth::guard('medical_center')->user();
                $selectedDoctor = DB::table('medical_center_selected_doctors')
                    ->where('medical_center_id', $medicalCenter->id)
                    ->first();
                $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
            } else {
            $doctor = Auth::guard('doctor')->user();
            if (!$doctor) {
                $secretary = Auth::guard('secretary')->user();
                if ($secretary && $secretary->doctor) {
                    $doctor = $secretary->doctor;
                }
            }
                $doctorId = $doctor ? $doctor->id : null;
            }

            if (!$doctorId) {
                if (Auth::guard('medical_center')->check()) {
                    return redirect()->route('mc.auth.login-register-form');
                } else {
                    return redirect()->route('dr.auth.login-register-form');
                }
            }

            $meta = json_decode($transaction->meta, true);

            if (
                $transaction->transactable_type === 'App\Models\Doctor' &&
                $transaction->transactable_id === $doctorId &&
                ($meta['type'] ?? '') === 'wallet_charge'
            ) {
                $wallet = DoctorWallet::firstOrCreate(['doctor_id' => $doctorId], ['balance' => 0]);
                $wallet->increment('balance', $transaction->amount);

                DoctorWalletTransaction::create([
                    'doctor_id'    => $doctorId,
                    'amount'       => $transaction->amount,
                    'status'       => 'paid',
                    'type'         => 'wallet_charge',
                    'description'  => 'شارژ کیف پول',
                    'registered_at' => now(),
                    'paid_at'      => now(),
                ]);

                return redirect()->route('mc-wallet-charge')->with('success', 'کیف‌پول شما با موفقیت شارژ شد.');
            }

            return redirect()->route('mc-wallet-charge')->with('error', 'تراکنش یافت شد اما با شارژ کیف‌پول شما مطابقت ندارد.');
        } catch (\Exception $e) {
            Log::error("WalletChargeComponent::verifyPayment - Error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('mc-wallet-charge')->with('error', 'خطا در تأیید پرداخت: ' . $e->getMessage());
        }
    }

    public function deleteTransaction($transactionId)
    {
        try {
            // Get doctor_id based on guard
            $doctorId = null;
            if (Auth::guard('medical_center')->check()) {
                // For medical_center guard, get the selected doctor
                $medicalCenter = Auth::guard('medical_center')->user();
                $selectedDoctor = DB::table('medical_center_selected_doctors')
                    ->where('medical_center_id', $medicalCenter->id)
                    ->first();
                $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
            } else {
            $doctor = Auth::guard('doctor')->user();
            if (!$doctor) {
                $secretary = Auth::guard('secretary')->user();
                if ($secretary && $secretary->doctor) {
                    $doctor = $secretary->doctor;
                }
            }
                $doctorId = $doctor ? $doctor->id : null;
            }

            if (!$doctorId) {
                if (Auth::guard('medical_center')->check()) {
                    return redirect()->route('mc.auth.login-register-form');
                } else {
                    return redirect()->route('dr.auth.login-register-form');
                }
            }

            $transaction = DoctorWalletTransaction::where('id', $transactionId)
                ->where('doctor_id', $doctorId)
                ->first();

            if (!$transaction) {
                $this->dispatch('toast', message: 'تراکنش یافت نشد یا متعلق به شما نیست.', type: 'error');
                return;
            }

            // بررسی اینکه آیا تراکنش قابل حذف است
            if ($transaction->status !== 'pending') {
                $this->dispatch('toast', message: 'فقط تراکنش‌های در انتظار قابل حذف هستند.', type: 'error');
                return;
            }

            $transaction->delete();
            $this->dispatch('toast', message: 'تراکنش با موفقیت حذف شد.', type: 'success');
        } catch (\Exception $e) {
            Log::error("DeleteTransaction - Error: {$e->getMessage()}", ['transaction_id' => $transactionId]);
            $this->dispatch('toast', message: 'خطایی در حذف تراکنش رخ داد: ' . $e->getMessage(), type: 'error');
        }
    }

    public function updatedDisplayAmount($value)
    {
        $this->amount = $value ? (int) str_replace(',', '', $value) : 0;
    }
}
