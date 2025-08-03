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

    public function callback(Request $request)
    {
        Log::info('PaymentController::callback - Received callback request', [
            'query' => $request->query(),
            'input' => $request->all(),
        ]);

        if (!$request->has('Authority') || !$request->has('Status')) {
            Log::error('PaymentController::callback - Missing Authority or Status in callback request', [
                'query' => $request->query(),
                'input' => $request->all(),
            ]);
            return redirect()->route('appointment.payment.result', [
                'error' => 'missing_parameters',
                'message' => 'پارامترهای مورد نیاز در درخواست وجود ندارد.',
            ]);
        }

        try {
            $transaction = $this->paymentService->verify();

            if (!$transaction) {
                Log::warning("PaymentController::callback - Payment failed or transaction not found");
                return redirect()->route('appointment.payment.result', [
                    'error' => 'failed',
                    'message' => 'تأیید تراکنش ناموفق بود.',
                ]);
            }

            $meta = json_decode($transaction->meta, true) ?? [];
            Log::info('PaymentController::callback - Transaction meta', ['meta' => $meta]);

            // اگر پرداخت مربوط به رزرو نوبت بود، متد paymentResult را اجرا کن
            if (isset($meta['appointment_id']) && isset($meta['appointment_type'])) {
                try {
                    // فراخوانی داخلی کنترلر و متد paymentResult
                    $controller = app(\App\Http\Controllers\Api\AppointmentBookingController::class);
                    $paymentResultRequest = Request::create(
                        '/appointments/payment/result?transaction_id=' . urlencode($transaction->transaction_id),
                        'GET'
                    );
                    $controller->paymentResult($paymentResultRequest);
                    Log::info('PaymentController::callback - paymentResult called internally for appointment');
                } catch (\Exception $e) {
                    Log::error('PaymentController::callback - Error calling paymentResult internally', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            $successRedirect = $meta['success_redirect'] ?? route('appointment.payment.result');
            $errorRedirect = $meta['error_redirect'] ?? route('appointment.payment.result');

            Log::info('PaymentController::callback - Redirecting', [
                'transaction_id' => $transaction->transaction_id,
                'success_redirect' => $successRedirect,
                'error_redirect' => $errorRedirect,
            ]);

            Log::info("PaymentController::callback - Payment successful for transaction: {$transaction->transaction_id}");

            $redirectUrl = $successRedirect;
            // برای wallet_charge، Authority رو به URL اضافه می‌کنیم
            if (isset($meta['type']) && $meta['type'] === 'wallet_charge') {
                $redirectUrl .= (parse_url($successRedirect, PHP_URL_QUERY) ? '&' : '?') . 'Authority=' . urlencode($transaction->transaction_id);
            } else {
                $redirectUrl .= (parse_url($successRedirect, PHP_URL_QUERY) ? '&' : '?') . 'transaction_id=' . urlencode($transaction->transaction_id);
            }

            if ($this->isValidUrl($successRedirect) || strpos($successRedirect, '127.0.0.1') !== false || strpos($successRedirect, 'localhost') !== false) {
                return redirect()->away($redirectUrl);
            } else {
                Log::warning("PaymentController::callback - Invalid success_redirect URL: {$successRedirect}");
                if (isset($meta['type']) && $meta['type'] === 'wallet_charge') {
                    return redirect()->route('dr-wallet-verify', ['Authority' => $transaction->transaction_id]);
                }
                return redirect()->route('appointment.payment.result', ['transaction_id' => $transaction->transaction_id]);
            }
        } catch (\Exception $e) {
            Log::error("PaymentController::callback - Exception occurred", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('appointment.payment.result', [
                'error' => 'exception',
                'message' => 'خطایی در پردازش پرداخت رخ داد: ' . $e->getMessage(),
            ]);
        }
    }

    protected function validateCallbackOrigin()
    {
        $allowedIps = [
            '185.143.232.0/22', // زرین‌پال
            '127.0.0.1', // لوکال‌هاست
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
    }

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

    protected function isValidUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        if (strpos($url, '127.0.0.1') !== false || strpos($url, 'localhost') !== false) {
            return true;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)->get($url);
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("PaymentController::isValidUrl - Failed to validate URL: {$url}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
