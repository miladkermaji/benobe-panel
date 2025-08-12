<?php

namespace App\Http\Controllers\Mc\Panel\Profile;

use App\Http\Controllers\Mc\Controller;
use App\Models\DoctorProfileUpgrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Services\PaymentService;

class DrUpgradeProfileController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        Log::info('🔄 PaymentService Injected!');
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        $payments = DoctorProfileUpgrade::where('doctor_id', $doctor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // چک کردن نتیجه پرداخت از سشن (بعد از ریدایرکت از درگاه)
        if (session('success')) {
            session()->flash('success', session('success'));
        } elseif (session('error')) {
            session()->flash('error', session('error'));
        }

        return view('mc.panel.profile.upgrade', compact('payments'));
    }

    public function payForUpgrade(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            return redirect()->back()->with('error', 'ابتدا وارد حساب خود شوید.');
        }

        $amount = 780000; // مقدار ثابت برای ارتقاء
        $callbackUrl = route('payment.callback');

        // ارسال درخواست پرداخت به PaymentService
        $paymentResponse = $this->paymentService->pay($amount, $callbackUrl, [
            'doctor_id'   => $doctor->id,
            'type'        => 'profile_upgrade', // اضافه کردن نوع برای شناسایی بهتر
            'description' => 'پرداخت برای ارتقاء حساب کاربری',
        ]);

        if ($paymentResponse instanceof \Illuminate\Http\RedirectResponse) {
            return $paymentResponse;
        }

        if (is_string($paymentResponse)) {
            return redirect()->away($paymentResponse);
        }

        return redirect()->route('mc-edit-profile-upgrade')->with('error', 'خطا در انتقال به درگاه پرداخت');
    }

    public function confirmPayment()
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            return redirect()->route('mc-edit-profile-upgrade')->with('error', 'ابتدا وارد حساب خود شوید.');
        }

        // تأیید پرداخت از درگاه
        $transaction = $this->paymentService->verify();

        if ($transaction) {
            // چک کردن اینکه تراکنش برای این دکتر و ارتقاء پروفایل باشه
            $meta = $transaction->meta ?? [];
            if (
                $transaction->transactable_type === 'App\Models\Doctor' &&
                $transaction->transactable_id === $doctor->id &&
                ($meta['type'] ?? '') === 'profile_upgrade'
            ) {
                // ثبت ارتقاء پروفایل
                DoctorProfileUpgrade::create([
                    'doctor_id'         => $doctor->id,
                    'payment_reference' => $transaction->transaction_id, // شناسه واقعی از درگاه
                    'payment_status'    => 'paid',
                    'amount'            => $transaction->amount,
                    'days'              => 90,
                    'paid_at'           => now(),
                    'expires_at'        => now()->addDays(90),
                ]);

                return redirect()->route('mc-edit-profile-upgrade')->with('success', 'پرداخت شما با موفقیت انجام شد و حساب شما ارتقاء یافت.');
            }

            return redirect()->route('mc-edit-profile-upgrade')->with('error', 'تراکنش یافت شد اما با ارتقاء حساب شما مطابقت ندارد.');
        }

        return redirect()->route('mc-edit-profile-upgrade')->with('error', 'تأیید پرداخت ناموفق بود.');
    }

    public function deletePayment($id)
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'ابتدا وارد حساب خود شوید.'], 403);
        }

        $payment = DoctorProfileUpgrade::where('doctor_id', $doctor->id)->find($id);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'پرداخت یافت نشد!'], 404);
        }

        $payment->delete();

        return response()->json(['success' => true, 'message' => 'پرداخت با موفقیت حذف شد.']);
    }
}
