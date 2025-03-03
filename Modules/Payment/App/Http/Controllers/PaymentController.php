<?php

namespace Modules\Payment\App\Http\Controllers;

use Illuminate\Http\Request;
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
     *
     * @param Request $request
     * @return mixed
     */
    public function pay(Request $request)
    {
        $amount = $request->input('amount');
        return $this->paymentService->pay($amount);
    }

    /**
     * بررسی و تأیید تراکنش
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $transaction = $this->paymentService->verify();

        if ($transaction) {
            return response()->json([
                'message' => 'پرداخت موفقیت‌آمیز بود.',
                'transaction' => $transaction
            ]);
        }

        return response()->json([
            'message' => 'پرداخت ناموفق بود یا تراکنش یافت نشد.'
        ], 400);
    }
}
