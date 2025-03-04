<?php
namespace App\Http\Controllers\Dr\Panel\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Dr\Controller;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Dr\DoctorProfileUpgrade;
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

  // چک کردن نتیجه پرداخت
  if (session('success')) {
   $this->confirmPayment();
  } elseif (session('error')) {
   session()->flash('error', session('error'));
  }

  return view('dr.panel.profile.upgrade', compact('payments'));
 }

 public function payForUpgrade(Request $request)
 {

  $doctor = Auth::guard('doctor')->user();
  if (!$doctor) {
   return redirect()->back()->with('error', 'ابتدا وارد حساب خود شوید.');
  }

  $amount = 780000; // مقدار ثابت برای ارتقاء
  $callbackUrl = route('payment.callback');


  $paymentResponse = $this->paymentService->pay($amount, $callbackUrl, [
   'doctor_id' => $doctor->id,
   'description' => 'پرداخت برای ارتقاء حساب کاربری'
  ]);


  if ($paymentResponse instanceof \Illuminate\Http\RedirectResponse) {
   return $paymentResponse;
  }

  if (is_string($paymentResponse)) {
   return redirect()->away($paymentResponse);
  }

  return redirect()->route('dr-edit-profile-upgrade')->with('error', 'خطا در انتقال به درگاه پرداخت');
 }

 public function confirmPayment()
 {
  $doctor = Auth::guard('doctor')->user();
  if (!$doctor) {
   return;
  }

  DoctorProfileUpgrade::create([
   'doctor_id' => $doctor->id,
   'payment_reference' => 'TEMP_' . now()->timestamp, // این باید از verify برگرده، فعلاً موقت
   'payment_status' => 'paid',
   'amount' => 780000,
   'days' => 90,
   'paid_at' => now(),
   'expires_at' => now()->addDays(90),
  ]);

  session()->flash('success', 'پرداخت شما با موفقیت انجام شد.');
 }

 public function deletePayment($id)
 {
  $payment = DoctorProfileUpgrade::find($id);

  if (!$payment) {
   return response()->json(['success' => false, 'message' => 'پرداخت یافت نشد!'], 404);
  }

  $payment->delete();

  return response()->json(['success' => true, 'message' => 'پرداخت با موفقیت حذف شد.']);
 }
}