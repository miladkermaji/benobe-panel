<?php

namespace Modules\Payment\Services;

use Shetabit\Multipay\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Shetabit\Payment\Facade\Payment;
use Illuminate\Http\RedirectResponse;
use Modules\Payment\App\Http\Models\Transaction;

class PaymentService
{
    /**
     * دریافت درگاه فعال از دیتابیس
     */
    protected function getActiveGateway()
    {
        $activeGateway = DB::table('payment_gateways')->where('is_active', true)->first();
        return $activeGateway ? $activeGateway->name : 'zarinpal'; // زرین‌پال به‌عنوان پیش‌فرض
    }

    /**
     * ایجاد پرداخت و هدایت کاربر به درگاه
     */
    public function pay($amount, $callbackUrl = null, $meta = [])
    {
        $gateway = $this->getActiveGateway();
        $callbackUrl = $callbackUrl ?? route('payment.callback');

        // تعیین نوع و شناسه موجودیت
        $transactableType = 'App\Models\User'; // پیش‌فرض
        $transactableId = null;

        if (isset($meta['doctor_id']) && ($meta['type'] === 'profile_upgrade' || $meta['type'] === 'wallet_charge')) {
            $transactableType = 'App\Models\Doctor';
            $transactableId = $meta['doctor_id'];
        } else {
            // استفاده از کاربر احراز هویت‌شده
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('کاربر احراز هویت نشده است.');
            }
            $transactableId = $user->id;
        }

        // ایجاد تراکنش در دیتابیس
        $transaction = Transaction::create([
            'transactable_type' => $transactableType,
            'transactable_id'   => $transactableId,
            'amount'            => $amount,
            'gateway'           => $gateway,
            'status'            => 'pending',
            'meta'              => json_encode($meta),
        ]);

        // ایجاد فاکتور پرداخت
        $invoice = new Invoice();
        $invoice->amount($amount);

        // اجرای پرداخت
        $redirection = Payment::via($gateway)
            ->callbackUrl($callbackUrl)
            ->purchase(
                $invoice,
                function ($driver, $transactionId) use ($transaction) {
                    $transaction->update(['transaction_id' => $transactionId]);
                }
            )->pay();

        // بررسی پاسخ درگاه
        if ($redirection instanceof RedirectResponse) {
            return $redirection;
        }

        if (method_exists($redirection, 'getAction')) {
            return redirect()->away($redirection->getAction());
        }

        if (is_string($redirection)) {
            return redirect()->away($redirection);
        }

        return redirect()->route('doctor.upgrade')->with('error', 'خطا در انتقال به درگاه پرداخت');
    }

    /**
     * تأیید تراکنش
     */
    public function verify()
    {
        try {
            $receipt = Payment::verify();
            $transactionId = $receipt->getReferenceId();

            $transaction = Transaction::where('transaction_id', $transactionId)->first();

            if ($transaction && $transaction->status === 'pending') {
                $transaction->update(['status' => 'paid']);
                return $transaction;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
