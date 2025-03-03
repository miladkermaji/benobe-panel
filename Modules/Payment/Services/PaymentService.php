<?php

namespace Modules\Payment\Services;

use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Modules\Payment\App\Http\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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

    // ایجاد تراکنش در دیتابیس
    $transaction = Transaction::create([
      'user_id' => auth()->id(),
      'amount' => $amount,
      'gateway' => $gateway,
      'status' => 'pending',
      'meta' => $meta,
    ]);

    // ایجاد فاکتور پرداخت
    $invoice = new Invoice;
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

      if ($transaction) {
        $transaction->update(['status' => 'paid']);
        return $transaction;
      }
    } catch (\Exception $e) {
      return false;
    }

    return false;
  }
}