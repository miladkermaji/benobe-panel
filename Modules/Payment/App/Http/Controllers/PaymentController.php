<?php

namespace Modules\Payment\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Services\PaymentService;

class PaymentController
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * ارسال کاربر به درگاه پرداخت
     */
    public function pay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'success_redirect' => 'nullable|url',
            'error_redirect' => 'nullable|url',
        ]);

        $amount = $request->input('amount');
        $successRedirect = $request->input('success_redirect');
        $errorRedirect = $request->input('error_redirect');

        return $this->paymentService->pay($amount, null, [], $successRedirect, $errorRedirect);
    }

    /**
     * بررسی و تأیید تراکنش
     */
    public function callback(Request $request)
    {
        // اعتبارسنجی اولیه درخواست
        if (!$request->has('Authority') || !$request->has('Status')) {
            Log::error('PaymentController::callback - Missing Authority or Status in callback request');
            return response()->json([
                'status' => 'error',
                'message' => 'پارامترهای مورد نیاز در درخواست وجود ندارد.',
            ], 400);
        }

        $transaction = $this->paymentService->verify();

        $meta = $transaction ? json_decode($transaction->meta, true) : [];
        $successRedirect = $meta['success_redirect'] ?? route('appointment.payment.result');
        $errorRedirect = $meta['error_redirect'] ?? route('appointment.payment.result');

        // لاگ‌گذاری برای دیباگ
        Log::info('PaymentController::callback - Redirecting', [
            'transaction_id' => $transaction ? $transaction->transaction_id : null,
            'success_redirect' => $successRedirect,
            'error_redirect' => $errorRedirect,
        ]);

        if ($transaction) {
            Log::info("PaymentController::callback - Payment successful for transaction: {$transaction->transaction_id}");
            $redirectUrl = $successRedirect . '?transaction_id=' . urlencode($transaction->transaction_id);
            // بررسی دسترسی به URL
            if ($this->isValidUrl($successRedirect)) {
                return redirect()->away($redirectUrl);
            } else {
                Log::warning("PaymentController::callback - Invalid success_redirect URL: {$successRedirect}");
                return redirect()->route('appointment.payment.result', ['transaction_id' => $transaction->transaction_id]);
            }
        }

        Log::warning("PaymentController::callback - Payment failed or transaction not found");
        $redirectUrl = $errorRedirect . '?error=failed';
        if ($this->isValidUrl($errorRedirect)) {
            return redirect()->away($redirectUrl);
        } else {
            Log::warning("PaymentController::callback - Invalid error_redirect URL: {$errorRedirect}");
            return redirect()->route('appointment.payment.result', ['error' => 'failed']);
        }
    }

    /**
     * اعتبارسنجی منشأ درخواست callback
     */
    protected function validateCallbackOrigin()
    {
        $allowedIps = [
            '185.143.232.0/22', // محدوده IP زرین‌پال
            '127.0.0.1', // برای لوکال‌هاست
        ];

        $clientIp = request()->ip();

        $isValidIp = false;
        foreach ($allowedIps as $allowedIp) {
            if ($this->ipInRange($clientIp, $allowedIp)) {
                $isValidIp = true;
                break;
            }
        }

        if (!$isValidIp) {
            Log::error("PaymentController::validateCallbackOrigin - Invalid callback IP", [
                'client_ip' => $clientIp,
            ]);
            abort(403, 'دسترسی غیرمجاز: IP درخواست‌کننده نامعتبر است.');
        }

        if (!request()->has('Authority') || !request()->has('Status')) {
            Log::error("PaymentController::validateCallbackOrigin - Missing Authority or Status");
            abort(403, 'دسترسی غیرمجاز: پارامترهای مورد نیاز وجود ندارد.');
        }
    }

    /**
     * بررسی اینکه IP در محدوده مجاز است یا خیر
     */
    protected function ipInRange($ip, $range)
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) === $subnet;
    }

    /**
     * بررسی معتبر بودن URL
     */
    protected function isValidUrl($url)
    {
        // بررسی فرمت URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // بررسی دسترسی به URL (اختیاری)
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("PaymentController::isValidUrl - Failed to validate URL: {$url}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
