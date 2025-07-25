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
    protected function getActiveGateway()
    {
        $activeGateway = DB::table('payment_gateways')->where('is_active', true)->first();
        return $activeGateway ? $activeGateway->name : 'zarinpal';
    }

    public function pay($amount, $callbackUrl = null, $meta = [], $successRedirect = null, $errorRedirect = null)
    {
        $gateway = $this->getActiveGateway();
        $callbackUrl = $callbackUrl ?? route('api.v2.subscriptions.payment.callback');

        Log::info('PaymentService::pay - Initiating payment', [
            'amount' => $amount,
            'callback_url' => $callbackUrl,
            'meta' => $meta,
            'success_redirect' => $successRedirect,
            'error_redirect' => $errorRedirect,
        ]);

        $transactableType = 'App\\Models\\User';
        $transactableId = null;

        if (isset($meta['doctor_id'])) {
            $transactableType = 'App\\Models\\Doctor';
            $transactableId = $meta['doctor_id'];
        } elseif (isset($meta['secretary_id'])) {
            $transactableType = 'App\\Models\\Secretary';
            $transactableId = $meta['secretary_id'];
        } else {
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('کاربر احراز هویت نشده است.');
            }
            $transactableType = get_class($user);
            $transactableId = $user->id;
        }

        // اطمینان از اینکه success_redirect و error_redirect از پارامترها اولویت دارن
        $metaData = array_merge($meta, [
            'success_redirect' => $successRedirect ?? ($meta['success_redirect'] ?? null),
            'error_redirect' => $errorRedirect ?? ($meta['error_redirect'] ?? null),
        ]);

        $metaJson = json_encode($metaData, JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('PaymentService::pay - JSON encoding error', [
                'meta' => $metaData,
                'error' => json_last_error_msg(),
            ]);
            throw new \Exception('خطا در ذخیره متا: ' . json_last_error_msg());
        }

        $transaction = Transaction::create([
            'transactable_type' => $transactableType,
            'transactable_id'   => $transactableId,
            'amount'            => $amount,
            'gateway'           => $gateway,
            'status'            => 'pending',
            'meta'              => $metaJson,
        ]);

        $invoice = new Invoice();
        $invoice->amount($amount);

        try {
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

            Log::info('PaymentService::pay - Redirection response', [
                'redirection' => $redirection instanceof RedirectResponse ? 'RedirectResponse' : (is_string($redirection) ? $redirection : get_class($redirection)),
            ]);

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
            if (isset($meta['type']) && $meta['type'] === 'wallet_charge') {
                return redirect()->route('dr-wallet-charge')->with('error', 'خطا در انتقال به درگاه پرداخت: ' . $e->getMessage());
            }
            return redirect()->route('appointment.payment.result')->with('error', 'خطا در انتقال به درگاه پرداخت: ' . $e->getMessage());
        }
    }

    public function verify()
    {
        $transaction = null;
        try {
            $authority = request()->input('Authority');
            if (!$authority) {
                Log::error('PaymentService::verify - No Authority provided in callback request', [
                    'input' => request()->all(),
                ]);
                return false;
            }

            $transaction = Transaction::where('transaction_id', $authority)->first();
            if (!$transaction) {
                Log::error("PaymentService::verify - No transaction found for Authority: {$authority}", [
                    'input' => request()->all(),
                    'authority' => $authority,
                ]);
                return false;
            }

            if ($transaction->status !== 'pending') {
                Log::warning("PaymentService::verify - Transaction {$authority} is not in pending status: {$transaction->status}", [
                    'transaction' => $transaction->toArray(),
                ]);
                return $transaction->status === 'paid' ? $transaction : false;
            }

            try {
                Log::info('PaymentService::verify - About to call gateway verify', [
                    'amount' => $transaction->amount,
                    'authority' => $authority,
                ]);
                $receipt = Payment::amount($transaction->amount)->transactionId($authority)->verify();
                Log::info('PaymentService::verify - Gateway verify response', [
                    'receipt' => $receipt,
                ]);
                if (!$receipt) {
                    Log::error('PaymentService::verify - Gateway returned null receipt', [
                        'authority' => $authority,
                        'transaction' => $transaction->toArray(),
                    ]);
                    $transaction->update(['status' => 'failed']);
                    return false;
                }
            } catch (\Exception $e) {
                Log::error('PaymentService::verify - Exception from gateway verify', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'authority' => $authority,
                    'transaction' => $transaction->toArray(),
                ]);
                $transaction->update(['status' => 'failed']);
                return false;
            }

            $transactionId = $receipt->getReferenceId();

            $receiptDetails = [
                'reference_id' => $receipt->getReferenceId(),
                'transaction_id' => $authority,
            ];

            // حفظ تمام کلیدهای متا
            $meta = json_decode($transaction->meta, true) ?? [];
            $updatedMeta = array_merge($meta, [
                'verified_at' => now()->toDateTimeString(),
                'receipt_details' => $receiptDetails,
            ]);

            $metaJson = json_encode($updatedMeta, JSON_UNESCAPED_UNICODE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('PaymentService::verify - JSON encoding error', [
                    'meta' => $updatedMeta,
                    'error' => json_last_error_msg(),
                ]);
                throw new \Exception('خطا در ذخیره متا: ' . json_last_error_msg());
            }

            $transaction->update([
                'status' => 'paid',
                'meta' => $metaJson,
            ]);

            // استفاده از متا اصلی برای نوبت‌ها
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

            Log::info("PaymentService::verify - Transaction {$authority} verified successfully", [
                'meta' => $updatedMeta,
            ]);
            return $transaction;
        } catch (\Shetabit\Multipay\Exceptions\InvalidPaymentException $e) {
            Log::error("PaymentService::verify - InvalidPaymentException: {$e->getMessage()}", [
                'authority' => request()->input('Authority'),
                'input' => request()->all(),
                'transaction' => $transaction ? $transaction->toArray() : null,
                'trace' => $e->getTraceAsString(),
            ]);
            if ($transaction) {
                $transaction->update(['status' => 'failed']);
            }
            return false;
        } catch (\Exception $e) {
            Log::error("PaymentService::verify - General error: {$e->getMessage()}", [
                'authority' => request()->input('Authority'),
                'input' => request()->all(),
                'transaction' => $transaction ? $transaction->toArray() : null,
                'trace' => $e->getTraceAsString(),
            ]);
            if ($transaction) {
                $transaction->update(['status' => 'failed']);
            }
            return false;
        }
    }
}
