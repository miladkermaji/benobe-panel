<?php

namespace Modules\Payment\Services;

use App\Models\Appointment;
use Shetabit\Multipay\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Shetabit\Payment\Facade\Payment;
use App\Models\CounselingAppointment;
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
        return $activeGateway ? $activeGateway->name : 'zarinpal';
    }

    /**
     * ایجاد پرداخت و هدایت کاربر به درگاه
     */
    public function pay($amount, $callbackUrl = null, $meta = [], $successRedirect = null, $errorRedirect = null)
    {
        $gateway = $this->getActiveGateway();
        $callbackUrl = $callbackUrl ?? route('payment.callback');

        // تعیین نوع و شناسه موجودیت
        $transactableType = 'App\Models\User';
        $transactableId = null;

        if (isset($meta['doctor_id']) && ($meta['type'] === 'profile_upgrade' || $meta['type'] === 'wallet_charge')) {
            $transactableType = 'App\Models\Doctor';
            $transactableId = $meta['doctor_id'];
        } else {
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('کاربر احراز هویت نشده است.');
            }
            $transactableId = $user->id;
        }

        // ذخیره URLهای موفقیت و شکست در متا
        $meta['success_redirect'] = $successRedirect;
        $meta['error_redirect'] = $errorRedirect;

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

        try {
            // اجرای پرداخت
            $redirection = Payment::via($gateway)
                ->callbackUrl($callbackUrl)
                ->purchase(
                    $invoice,
                    function ($driver, $transactionId) use ($transaction) {
                        Log::info('PaymentService::pay - Purchase successful', [
                            'transaction_id' => $transactionId,
                            'amount' => $transaction->amount,
                            'gateway' => $transaction->gateway,
                        ]);
                        $transaction->update(['transaction_id' => $transactionId]);
                    }
                )->pay();

            // لاگ‌گذاری پاسخ
            Log::info('PaymentService::pay - Redirection response', [
                'redirection' => $redirection instanceof RedirectResponse ? 'RedirectResponse' : (is_string($redirection) ? $redirection : get_class($redirection)),
            ]);

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

            throw new \Exception('خطا در انتقال به درگاه پرداخت');
        } catch (\Exception $e) {
            Log::error('PaymentService::pay - Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'amount' => $amount,
                'gateway' => $gateway,
                'callback_url' => $callbackUrl,
            ]);
            $transaction->update(['status' => 'failed']);
            return redirect()->route('doctor.upgrade')->with('error', 'خطا در انتقال به درگاه پرداخت: ' . $e->getMessage());
        }
    }

    /**
     * تأیید تراکنش
     */
    public function verify()
    {
        try {
            // دریافت Authority از درخواست
            $authority = request()->input('Authority');
            if (!$authority) {
                Log::error('PaymentService::verify - No Authority provided in callback request');
                return false;
            }

            // بررسی وجود تراکنش با transaction_id
            $transaction = Transaction::where('transaction_id', $authority)->first();
            if (!$transaction) {
                Log::error("PaymentService::verify - No transaction found for Authority: {$authority}");
                return false;
            }

            // بررسی وضعیت تراکنش
            if ($transaction->status !== 'pending') {
                Log::warning("PaymentService::verify - Transaction {$authority} is not in pending status: {$transaction->status}");
                return $transaction->status === 'paid' ? $transaction : false;
            }

            // تأیید پرداخت از درگاه
            $receipt = Payment::amount($transaction->amount)->transactionId($authority)->verify();
            $transactionId = $receipt->getReferenceId();

            // استخراج اطلاعات رسید به صورت دستی
            $receiptDetails = [
                'reference_id' => $receipt->getReferenceId(),
                'transaction_id' => $authority,
            ];

            // به‌روزرسانی تراکنش
            $transaction->update([
                'status' => 'paid',
                'meta' => json_encode(array_merge(json_decode($transaction->meta, true), [
                    'verified_at' => now()->toDateTimeString(),
                    'receipt_details' => $receiptDetails,
                ])),
            ]);

            // به‌روزرسانی وضعیت نوبت
            $meta = json_decode($transaction->meta, true);
            $appointmentId = $meta['appointment_id'] ?? null;
            $appointmentType = $meta['appointment_type'] ?? null;

            if ($appointmentId && $appointmentType) {
                $model = $appointmentType === 'in_person'
                    ? Appointment::class
                    : CounselingAppointment::class;

                $model::where('id', $appointmentId)->update([
                    'payment_status' => 'paid',
                    'status' => 'pending_review',
                ]);
            }

            Log::info("PaymentService::verify - Transaction {$authority} verified successfully");
            return $transaction;
        } catch (\Shetabit\Multipay\Exceptions\InvalidPaymentException $e) {
            Log::error("PaymentService::verify - InvalidPaymentException: {$e->getMessage()}", [
                'authority' => request()->input('Authority'),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("PaymentService::verify - General error: {$e->getMessage()}", [
                'authority' => request()->input('Authority'),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
