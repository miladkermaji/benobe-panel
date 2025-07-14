<?php

namespace App\Http\Controllers\Api;

use App\Models\PrescriptionRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Payment\Services\PaymentService;

class PrescriptionRequestController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // لیست نسخه‌های من (کاربر لاگین شده)
    public function myPrescriptions(Request $request)
    {
        $user = Auth::user();
        if (! $user || !method_exists($user, 'prescriptions')) {
            return response()->json([
                'status' => 'error',
                'message' => 'کاربر احراز هویت نشده است یا دسترسی ندارد',
                'data' => null,
            ], 401);
        }
        $prescriptions = $user->prescriptions()->with(['clinic', 'transaction'])->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $prescriptions,
        ]);
    }

    // ثبت درخواست نسخه جدید
    public function requestPrescription(Request $request)
    {
        $messages = [
            'type.required' => 'انتخاب نوع نسخه الزامی است.',
            'type.in' => 'نوع نسخه انتخابی معتبر نیست.',
            'doctor_id.required' => 'انتخاب پزشک الزامی است.',
            'doctor_id.exists' => 'پزشک انتخابی معتبر نیست.',
            'clinic_id.exists' => 'کلینیک انتخابی معتبر نیست.',
            'insurance_id.exists' => 'بیمه انتخابی معتبر نیست.',
            'description.required' => 'توضیحات برای گزینه سایر الزامی است.',
            'description.max' => 'توضیحات نباید بیشتر از ۸۰ کاراکتر باشد.',
            'description.prohibited' => 'توضیحات فقط برای گزینه سایر مجاز است.',
        ];

        $validated = $request->validate([
            'type' => 'required|in:renew_lab,renew_drug,renew_insulin,other',
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'nullable|exists:clinics,id',
            'insurance_id' => 'nullable|exists:insurances,id',
            'description' => [
                $request->input('type') === 'other' ? 'required' : 'prohibited',
                'max:80'
            ],
        ], $messages);

        $user = Auth::user();
        $doctorId = $validated['doctor_id'];

        // شرط نوبت موفق
        $hasAppointment = \App\Models\Appointment::where('doctor_id', $doctorId)
            ->where(function ($q) use ($user) {
                $q->where('patientable_id', $user->id)
                  ->where('patientable_type', get_class($user));
            })
            ->where('status', 'attended')
            ->where('payment_status', 'paid')
            ->exists();

        $hasCounseling = \App\Models\CounselingAppointment::where('doctor_id', $doctorId)
            ->where('patient_id', $user->id)
            ->where('status', 'attended')
            ->where('payment_status', 'paid')
            ->exists();

        if (!($hasAppointment || $hasCounseling)) {
            return response()->json([
                'status' => 'error',
                'message' => 'شما مجاز به ثبت درخواست نسخه برای این پزشک نیستید. ابتدا باید یک ویزیت موفق با این پزشک داشته باشید.',
                'data' => null,
            ], 403);
        }

        // تولید کد رهگیری عددی یونیک
        do {
            $tracking_code = (int)(time() . rand(100, 999));
        } while (\App\Models\PrescriptionRequest::where('tracking_code', $tracking_code)->exists());

        $clinic = null;
        $prescription_fee = null;
        if (!empty($validated['clinic_id'])) {
            $clinic = \App\Models\Clinic::find($validated['clinic_id']);
            $prescription_fee = $clinic?->prescription_fee;
        }
        $price = $prescription_fee > 0 ? (int)$prescription_fee : null;
        $transaction_id = null;
        $payment_url = null;
        // اگر قیمت دارد، پرداخت را با PaymentService انجام بده
        if ($price) {
            $meta = [
                'type' => 'prescription_request',
                'clinic_id' => $clinic?->id,
                'doctor_id' => $validated['doctor_id'],
                'user_id' => $user->id,
            ];
            $successRedirect = route('payment.callback'); // یا route مناسب prescription
            $errorRedirect = route('payment.callback');
            try {
                $paymentResponse = $this->paymentService->pay($price, null, $meta, $successRedirect, $errorRedirect);
                if ($paymentResponse instanceof \Shetabit\Multipay\RedirectionForm) {
                    $payment_url = $paymentResponse->getAction();
                } elseif ($paymentResponse instanceof \Illuminate\Http\RedirectResponse) {
                    $payment_url = $paymentResponse->getTargetUrl();
                } elseif (is_array($paymentResponse) && isset($paymentResponse['payment_url'])) {
                    $payment_url = $paymentResponse['payment_url'];
                } else {
                    return response()->json(['message' => 'خطا در ایجاد لینک پرداخت.'], 500);
                }
            } catch (\Exception $e) {
                Log::error($e);
                return response()->json(['message' => 'خطای سرور: ' . $e->getMessage()], 500);
            }
            // خروجی فقط payment_url
            return response()->json([
                'status' => 'success',
                'payment_url' => $payment_url,
            ], 201);
        }
        // اگر پرداخت نیاز نیست، prescription را ثبت کن
        if (!method_exists($user, 'prescriptions')) {
            return response()->json([
                'status' => 'error',
                'message' => 'امکان ثبت نسخه برای این نقش وجود ندارد.',
            ], 403);
        }
        $prescription = $user->prescriptions()->create([
            'type' => $validated['type'],
            'description' => $validated['type'] === 'other' ? $validated['description'] : null,
            'doctor_id' => $validated['doctor_id'],
            'insurance_id' => $validated['insurance_id'] ?? null,
            'clinic_id' => $clinic?->id,
            'price' => $price,
            'tracking_code' => $tracking_code,
            'status' => 'pending',
            'payment_status' => 'paid',
            'transaction_id' => $transaction_id,
        ]);
        $prescription->load(['clinic', 'transaction']);
        return response()->json([
            'status' => 'success',
            'message' => 'درخواست نسخه با موفقیت ثبت شد',
            'data' => [
                'prescription' => $prescription,
            ],
        ], 201);
    }
}
