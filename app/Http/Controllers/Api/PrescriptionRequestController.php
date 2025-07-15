<?php

namespace App\Http\Controllers\Api;

use App\Models\Clinic;
use App\Models\Insulin;
use Illuminate\Http\Request;
use App\Models\PrescriptionRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PrescriptionInsurance;
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

    // اطلاعات مورد نیاز برای ساخت فرم درخواست نسخه (برای ابزار مستندساز)
    public function requestPrescription(Request $request)
    {
        if ($request->isMethod('get')) {
            // انواع نسخه
            $types = [
                ['value' => 'renew_lab', 'label' => 'تمدید آزمایش'],
                ['value' => 'renew_drug', 'label' => 'تمدید دارو'],
                ['value' => 'renew_insulin', 'label' => 'تمدید انسولین'],
                ['value' => 'sonography', 'label' => 'سونوگرافی'],
                ['value' => 'mri', 'label' => 'ام آر آی'],
                ['value' => 'other', 'label' => 'سایر'],
            ];
            // لیست انسولین‌ها
            $insulins = Insulin::select('id', 'name')->get();
            // لیست بیمه‌ها (ساختار درختی)
            $insurances = PrescriptionInsurance::with('children')->whereNull('parent_id')->get();
            // لیست کلینیک‌ها
            $clinics = Clinic::select('id', 'name', 'prescription_fee')->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'types' => $types,
                    'insulins' => $insulins,
                    'insurances' => $insurances,
                    'clinics' => $clinics,
                ],
            ]);
        }

        // POST: ثبت درخواست نسخه
        $messages = [
            'type.required' => 'انتخاب نوع نسخه الزامی است.',
            'type.in' => 'نوع نسخه انتخابی معتبر نیست.',
            'doctor_id.required' => 'انتخاب پزشک الزامی است.',
            'doctor_id.exists' => 'پزشک انتخابی معتبر نیست.',
            'clinic_id.exists' => 'کلینیک انتخابی معتبر نیست.',
            'prescription_insurance_id.exists' => 'بیمه انتخابی معتبر نیست.',
            'description.required' => 'توضیحات برای گزینه سایر الزامی است.',
            'description.max' => 'توضیحات نباید بیشتر از ۸۰ کاراکتر باشد.',
            'description.prohibited' => 'توضیحات فقط برای گزینه سایر مجاز است.',
            'insulins.required' => 'انتخاب انسولین برای تمدید انسولین الزامی است.',
            'insulins.array' => 'فرمت انسولین‌ها معتبر نیست.',
            'insulins.*.id.required' => 'شناسه انسولین الزامی است.',
            'insulins.*.id.exists' => 'شناسه انسولین معتبر نیست.',
            'insulins.*.count.required' => 'تعداد انسولین الزامی است.',
            'insulins.*.count.integer' => 'تعداد انسولین باید عدد باشد.',
            'referral_code.required_if' => 'کد ارجاع برای این بیمه الزامی است.',
        ];

        $validated = $request->validate([
            'type' => 'required|in:renew_lab,renew_drug,renew_insulin,sonography,mri,other',
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'nullable|exists:clinics,id',
            'prescription_insurance_id' => 'nullable|exists:prescription_insurances,id',
            'description' => [
                $request->input('type') === 'other' ? 'required' : 'prohibited',
                'max:80'
            ],
            'insulins' => [
                $request->input('type') === 'renew_insulin' ? 'required' : 'nullable',
                'array'
            ],
            'insulins.*.id' => [
                $request->input('type') === 'renew_insulin' ? 'required' : 'nullable',
                'exists:insulins,id'
            ],
            'insulins.*.count' => [
                $request->input('type') === 'renew_insulin' ? 'required' : 'nullable',
                'integer',
                'min:1'
            ],
            'referral_code' => [
                // اگر بیمه انتخاب شده نیاز به کد ارجاع دارد
                function ($attribute, $value, $fail) use ($request) {
                    $insurance = PrescriptionInsurance::find($request->input('prescription_insurance_id'));
                    if ($insurance && $insurance->needs_referral_code && empty($value)) {
                        $fail('کد ارجاع برای این بیمه الزامی است.');
                    }
                }
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
        // do {
        //     $tracking_code = (int)(time() . rand(100, 999));
        // } while (PrescriptionRequest::where('tracking_code', $tracking_code)->exists());

        $clinic = null;
        $prescription_fee = null;
        if (!empty($validated['clinic_id'])) {
            $clinic = Clinic::find($validated['clinic_id']);
            $prescription_fee = $clinic?->prescription_fee;
        }
        $price = $prescription_fee > 0 ? (int)$prescription_fee : null;
        $transaction_id = null;
        $payment_url = null;
        // اگر قیمت دارد، پرداخت را با PaymentService انجام بده
        if ($price) {
            $prescription = $user->prescriptions()->create([
                'type' => $validated['type'],
                'description' => $validated['type'] === 'other' ? $validated['description'] : null,
                'doctor_id' => $validated['doctor_id'],
                'prescription_insurance_id' => $validated['prescription_insurance_id'] ?? null,
                'clinic_id' => $clinic?->id,
                'price' => $price,
                'status' => 'pending',
                'payment_status' => $price ? 'pending' : 'paid',
                'transaction_id' => $transaction_id,
                'referral_code' => $request->input('referral_code'),
            ]);
            // اگر نوع renew_insulin بود، انسولین‌ها را ذخیره کن
            if ($validated['type'] === 'renew_insulin' && !empty($validated['insulins'])) {
                foreach ($validated['insulins'] as $insulin) {
                    $prescription->insulins()->attach($insulin['id'], ['count' => $insulin['count']]);
                }
            }
            // اگر پرداخت نیاز دارد، prescription_id را در meta ذخیره کن و فقط payment_url را برگردان
            $meta = [
                'type' => 'prescription_request',
                'clinic_id' => $clinic?->id,
                'doctor_id' => $validated['doctor_id'],
                'user_id' => $user->id,
                'prescription_id' => $prescription->id,
            ];
            $successRedirect = route('payment.callback');
            $errorRedirect = route('payment.callback');
            try {
                $paymentResponse = $this->paymentService->pay($price, route('api.prescriptions.payment.callback'), $meta, $successRedirect, $errorRedirect);
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
            'prescription_insurance_id' => $validated['prescription_insurance_id'] ?? null,
            'clinic_id' => $clinic?->id,
            'price' => $price,
            'status' => 'pending',
            'payment_status' => $price ? 'pending' : 'paid',
            'transaction_id' => $transaction_id,
            'referral_code' => $request->input('referral_code'),
        ]);
        // اگر نوع renew_insulin بود، انسولین‌ها را ذخیره کن
        if ($validated['type'] === 'renew_insulin' && !empty($validated['insulins'])) {
            foreach ($validated['insulins'] as $insulin) {
                $prescription->insulins()->attach($insulin['id'], ['count' => $insulin['count']]);
            }
        }
        // اگر پرداخت نیاز دارد، prescription_id را در meta ذخیره کن و فقط payment_url را برگردان
        if ($price) {
            $meta = [
                'type' => 'prescription_request',
                'clinic_id' => $clinic?->id,
                'doctor_id' => $validated['doctor_id'],
                'user_id' => $user->id,
                'prescription_id' => $prescription->id,
            ];
            $successRedirect = route('payment.callback');
            $errorRedirect = route('payment.callback');
            try {
                $paymentResponse = $this->paymentService->pay($price, route('api.prescriptions.payment.callback'), $meta, $successRedirect, $errorRedirect);
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
            return response()->json([
                'status' => 'success',
                'payment_url' => $payment_url,
            ], 201);
        }
        $prescription->load(['clinic', 'transaction', 'insulins']);
        return response()->json([
            'status' => 'success',
            'message' => 'درخواست نسخه با موفقیت ثبت شد',
            'data' => [
                'prescription' => $prescription,
            ],
        ], 201);
    }

    /**
     * Callback پرداخت prescription
     */
    public function prescriptionPaymentCallback(Request $request)
    {
        $authority = $request->input('Authority');
        $status = $request->input('Status');
        if ($status !== 'OK' || !$authority) {
            return response()->json([
                'success' => false,
                'message' => 'تراکنش ناموفق بود یا توسط شما لغو شد.',
                'authority' => $authority,
            ], 400);
        }
        $transaction = \App\Models\Transaction::where('transaction_id', $authority)->first();
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'تراکنش یافت نشد.',
                'authority' => $authority,
            ], 404);
        }
        // اگر تراکنش قبلاً paid شده بود
        if ($transaction->status === 'paid') {
            $meta = json_decode($transaction->meta, true);
            if (isset($meta['prescription_id'])) {
                $prescription = \App\Models\PrescriptionRequest::find($meta['prescription_id']);
                if ($prescription && $prescription->payment_status !== 'paid') {
                    $prescription->payment_status = 'paid';
                    $prescription->save();
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'پرداخت نسخه قبلاً با موفقیت انجام شده است.',
                'authority' => $transaction->transaction_id,
            ], 200);
        }
        // اگر تراکنش pending بود، verify را اجرا کن
        $verifiedTransaction = app(\Modules\Payment\Services\PaymentService::class)->verify();
        if (!$verifiedTransaction || $verifiedTransaction->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'تراکنش یافت نشد یا موفقیت آمیز نبود.',
                'authority' => $authority,
            ], 404);
        }
        // بعد از پرداخت موفق، prescription را به paid تغییر بده
        $meta = json_decode($verifiedTransaction->meta, true);
        if (isset($meta['prescription_id'])) {
            $prescription = \App\Models\PrescriptionRequest::find($meta['prescription_id']);
            if ($prescription && $prescription->payment_status !== 'paid') {
                $prescription->payment_status = 'paid';
                $prescription->save();
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'پرداخت نسخه با موفقیت انجام شد.',
            'authority' => $verifiedTransaction->transaction_id,
        ], 200);
    }

    /**
     * لیست بیمه‌های نسخه (درختی)
     */
    public function prescriptionInsurances()
    {
        $insurances = \App\Models\PrescriptionInsurance::with('children')->whereNull('parent_id')->get();
        return response()->json([
            'status' => 'success',
            'data' => $insurances,
        ]);
    }

    /**
     * لیست انسولین‌ها
     */
    public function insulins()
    {
        $insulins = \App\Models\Insulin::select('id', 'name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $insulins,
        ]);
    }
}
 