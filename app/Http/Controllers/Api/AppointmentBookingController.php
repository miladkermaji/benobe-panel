<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Models\InfrastructureFee;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ClinicDepositSetting;
use App\Models\SpecialDailySchedule;
use App\Models\CounselingAppointment;
use Illuminate\Support\Facades\Cache;
use App\Models\DoctorCounselingConfig;
use App\Models\CounselingDailySchedule;
use App\Models\DoctorAppointmentConfig;
use App\Models\DoctorCounselingHoliday;
use App\Models\DoctorCounselingWorkSchedule;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\App\Http\Models\Transaction;

class AppointmentBookingController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * دریافت اطلاعات پزشک و نوبت برای صفحه رزرو
     */
    public function getBookingDetails(Request $request, $doctorId)
    {
        try {
            // اعتبارسنجی ورودی‌ها
            $validated = $request->validate([
                'appointment_date' => 'required|date_format:Y-m-d',
                'appointment_time' => 'required|date_format:H:i',
                'service_type'     => 'required|in:in_person,phone,text,video',
            ], [
                'appointment_date.required'    => 'تاریخ نوبت الزامی است.',
                'appointment_date.date_format' => 'فرمت تاریخ نوبت باید به شکل YYYY-MM-DD باشد (مثلاً 2025-03-22).',
                'appointment_time.required'    => 'زمان نوبت الزامی است.',
                'appointment_time.date_format' => 'فرمت زمان نوبت باید به شکل HH:MM باشد (مثلاً 14:30).',
                'service_type.required'        => 'نوع خدمت الزامی است.',
                'service_type.in'              => 'نوع خدمت باید یکی از مقادیر in_person, phone, text, video باشد.',
            ]);

            $appointmentDate = $request->input('appointment_date');
            $appointmentTime = $request->input('appointment_time');
            $serviceType     = $request->input('service_type');

            // پیدا کردن پزشک
            $doctor = Doctor::where('id', $doctorId)
                ->where('status', true)
                ->first();

            if (! $doctor) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'پزشک یافت نشد یا غیرفعال است.',
                    'data'    => null,
                ], 404);
            }

            // بررسی در دسترس بودن نوبت
            $isSlotAvailable = $this->checkSlotAvailability($doctor, $appointmentDate, $appointmentTime, $serviceType);
            if (! $isSlotAvailable) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'این نوبت دیگر در دسترس نیست.',
                    'data'    => null,
                ], 400);
            }

            // اطلاعات کلینیک
            $mainClinic = $doctor->clinics()->where('is_active', true)->where('is_main_clinic', true)->first()
                ?? $doctor->clinics()->where('is_active', true)->first();
            $city = $mainClinic ? $mainClinic->city()->value('name') : 'نامشخص';

            // تبدیل تاریخ به شمسی
            $jalaliDate        = Jalalian::fromCarbon(Carbon::parse("$appointmentDate $appointmentTime", 'Asia/Tehran'));
            $formattedDateTime = $jalaliDate->format('l d F') . ' ساعت ' . $jalaliDate->format('H:i');

            // محاسبه هزینه‌ها
            $depositSetting = ClinicDepositSetting::where('doctor_id', $doctor->id)
                ->where('clinic_id', $mainClinic->id)
                ->where('is_active', true)
                ->first();
            $depositAmount     = $depositSetting ? $depositSetting->deposit_amount : 0;
            $infrastructureFee = InfrastructureFee::where('appointment_type', $serviceType)
                ->where('is_active', true)
                ->first()->fee ?? 0;

            // آماده‌سازی داده‌ها برای نمایش
            $data = [
                'doctor'      => [
                    'id'           => $doctor->id,
                    'name'         => $doctor->display_name ?? ($doctor->first_name . ' ' . $doctor->last_name),
                    'specialty'    => $doctor->specialty()->value('name') ?? 'نامشخص',
                    'avatar'       => $doctor->profile_photo_path ? asset('storage/' . $doctor->profile_photo_path) : '/default-avatar.png',
                    'location'     => [
                        'province' => $doctor->province()->value('name') ?? 'نامشخص',
                        'city'     => $city,
                        'address'  => $mainClinic?->address ?? 'نامشخص',
                    ],
                    'office_phone' => $mainClinic?->phone ?? 'نامشخص',
                ],
                'appointment' => [
                    'date_time'    => $formattedDateTime,
                    'service_type' => $serviceType,
                ],
                'payment'     => [
                    'deposit_amount'     => $depositAmount,
                    'infrastructure_fee' => $infrastructureFee,
                    'total_amount'       => $depositAmount + $infrastructureFee,
                ],
            ];

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای اعتبارسنجی ورودی‌ها',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('GetBookingDetails - Error: ' . $e->getMessage(), [
                'request'   => $request->all(),
                'exception' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * بررسی وضعیت رزرو با کد رهگیری
     */
    public function getReservationStatus(Request $request)
    {
        $trackingCode = $request->input('tracking_code');
        if (!$trackingCode) {
            return response()->json([
                'status' => 'error',
                'message' => 'کد رهگیری ارسال نشده است.',
                'data' => null,
            ], 400);
        }
        $appointment = Appointment::where('tracking_code', $trackingCode)->first();
        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'رزروی با این کد رهگیری یافت نشد.',
                'data' => null,
            ], 404);
        }
        $now = now();
        $reservedAt = $appointment->reserved_at;
        $expiresAt = $reservedAt ? $reservedAt->addMinutes(10) : null;
        $remaining = $expiresAt && $now->lessThan($expiresAt) ? $now->diffInSeconds($expiresAt) : 0;
        $isExpired = $expiresAt && $now->greaterThanOrEqualTo($expiresAt);
        $status = $appointment->payment_status === 'paid' ? 'paid' : ($isExpired ? 'expired' : 'pending');
        return response()->json([
            'status' => 'success',
            'data' => [
                'tracking_code' => $trackingCode,
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'status' => $status,
                'remaining_seconds' => $remaining,
                'reserved_at' => $reservedAt,
                'expires_at' => $expiresAt,
            ],
        ]);
    }

    public function bookAppointment(Request $request, $doctorId)
    {
        try {
            // اعتبارسنجی ورودی‌ها
            $validated = $request->validate([
                'appointment_date' => 'required|date_format:Y-m-d',
                'appointment_time' => 'required|date_format:H:i',
                'service_type'     => 'required|in:in_person,phone,text,video',
                'patient_type'     => 'required|in:self,relative',
                'first_name'       => 'required_if:patient_type,relative|string|max:255',
                'last_name'        => 'required_if:patient_type,relative|string|max:255',
                'national_code'    => 'required_if:patient_type,relative|string|size:10|regex:/^[0-9]{10}$/|unique:users,national_code',
                'mobile'           => 'required_if:patient_type,relative|string|max:15|regex:/^09[0-9]{9}$/|unique:users,mobile',
                'email'            => 'nullable|email|unique:users,email',
                'success_redirect' => 'nullable|url',
                'error_redirect'   => 'nullable|url',
                'clinic_id'        => 'nullable|integer|exists:clinics,id',
            ], [
                'appointment_date.required'    => 'تاریخ نوبت الزامی است.',
                'appointment_date.date_format' => 'فرمت تاریخ نوبت باید به شکل YYYY-MM-DD باشد (مثلاً 2025-03-22).',
                'appointment_time.required'    => 'زمان نوبت الزامی است.',
                'appointment_time.date_format' => 'فرمت زمان نوبت باید به شکل HH:MM باشد (مثلاً 14:30).',
                'service_type.required'        => 'نوع خدمت الزامی است.',
                'service_type.in'              => 'نوع خدمت باید یکی از مقادیر in_person, phone, text, video باشد.',
                'patient_type.required'        => 'نوع بیمار الزامی است.',
                'patient_type.in'              => 'نوع بیمار باید یکی از مقادیر self یا relative باشد.',
                'first_name.required_if'       => 'نام بیمار الزامی است وقتی نوع بیمار relative باشد.',
                'first_name.max'               => 'نام بیمار نمی‌تواند بیشتر از 255 کاراکتر باشد.',
                'last_name.required_if'        => 'نام خانوادگی بیمار الزامی است وقتی نوع بیمار relative باشد.',
                'last_name.max'                => 'نام خانوادگی بیمار نمی‌تواند بیشتر از 255 کاراکتر باشد.',
                'national_code.required_if'    => 'کدملی بیمار الزامی است وقتی نوع بیمار relative باشد.',
                'national_code.size'           => 'کدملی بیمار باید دقیقاً 10 رقم باشد.',
                'national_code.regex'          => 'کدملی باید 10 رقم عددی باشد.',
                'national_code.unique'         => 'این کدملی قبلاً ثبت شده است.',
                'mobile.required_if'           => 'شماره موبایل بیمار الزامی است وقتی نوع بیمار relative باشد.',
                'mobile.max'                   => 'شماره موبایل بیمار نمی‌تواند بیشتر از 15 رقم باشد.',
                'mobile.regex'                 => 'شماره موبایل باید با 09 شروع شود و 11 رقم باشد.',
                'mobile.unique'                => 'این شماره موبایل قبلاً ثبت شده است.',
                'email.email'                  => 'ایمیل باید فرمت معتبر داشته باشد.',
                'email.unique'                 => 'این ایمیل قبلاً ثبت شده است.',
                'success_redirect.url'         => 'آدرس هدایت موفقیت باید یک URL معتبر باشد.',
                'error_redirect.url'           => 'آدرس هدایت خطا باید یک URL معتبر باشد.',
                'clinic_id.integer'            => 'شناسه کلینیک باید یک عدد صحیح باشد.',
                'clinic_id.exists'             => 'کلینیک انتخاب‌شده وجود ندارد.',
            ]);

            // پاک‌سازی رزروهای منقضی‌شده قبل از رزرو جدید
            $this->cleanupExpiredPendingAppointments($doctorId);

            // استفاده از تراکنش برای جلوگیری از رزروهای همزمان
            return DB::transaction(function () use ($request, $doctorId) {
                $appointmentDate = $request->input('appointment_date');
                $appointmentTime = $request->input('appointment_time');
                $serviceType = $request->input('service_type');
                $patientType = $request->input('patient_type');
                $successRedirect = $request->input('success_redirect');
                $errorRedirect = $request->input('error_redirect');
                $clinicId = $request->input('clinic_id');

                // گرفتن کاربر احراز هویت‌شده
                $authenticatedUser = $request->attributes->get('user');

                // پیدا کردن یا ایجاد بیمار
                $patient = null;
                if ($patientType === 'self') {
                    $patient = $authenticatedUser;
                } else {
                    $patient = User::create([
                        'first_name' => $request->input('first_name'),
                        'last_name' => $request->input('last_name'),
                        'national_code' => $request->input('national_code'),
                        'mobile' => $request->input('mobile'),
                        'email' => $request->input('email'),
                        'created_by' => $authenticatedUser->id,
                        'user_type' => 0,
                        'status' => 1,
                    ]);
                }

                if (!$patient) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'کاربر (بیمار) یافت نشد یا ایجاد نشد.',
                        'data' => null,
                    ], 400);
                }

                // پیدا کردن پزشک
                $doctor = Doctor::where('id', $doctorId)
                    ->where('status', true)
                    ->first();

                if (!$doctor) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'پزشک یافت نشد یا غیرفعال است.',
                        'data' => null,
                    ], 404);
                }

                // تعیین کلینیک
                $mainClinic = null;
                if ($clinicId) {
                    // اگر clinic_id در درخواست ارسال شده، کلینیک مربوطه را پیدا کن
                    $mainClinic = $doctor->clinics()->where('id', $clinicId)->where('is_active', true)->first();
                    if (!$mainClinic) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'کلینیک انتخاب‌شده یافت نشد یا غیرفعال است.',
                            'data' => null,
                        ], 400);
                    }
                } else {
                    // اگر clinic_id ارسال نشده، کلینیک اصلی یا اولین کلینیک فعال را انتخاب کن
                    $mainClinic = $doctor->clinics()->where('is_active', true)->where('is_main_clinic', true)->first()
                        ?? $doctor->clinics()->where('is_active', true)->first();
                }

                // اگر کلینیک پیدا نشد، clinic_id را NULL تنظیم کن
                $clinicId = $mainClinic ? $mainClinic->id : null;

                // بررسی در دسترس بودن نوبت
                $isSlotAvailable = $this->checkSlotAvailability($doctor, $appointmentDate, $appointmentTime, $serviceType, $clinicId);
                if (!$isSlotAvailable) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'این نوبت دیگر در دسترس نیست.',
                        'data' => null,
                    ], 400);
                }

                // محاسبه هزینه‌ها
                $depositAmount = 0;
                $infrastructureFee = InfrastructureFee::where('appointment_type', $serviceType)
                    ->where('is_active', true)
                    ->first()->fee ?? 0;

                if ($mainClinic) {
                    $depositSetting = ClinicDepositSetting::where('doctor_id', $doctor->id)
                        ->where('clinic_id', $mainClinic->id)
                        ->where('is_active', true)
                        ->first();
                    $depositAmount = $depositSetting ? $depositSetting->deposit_amount : 0;
                }

                $totalFee = $depositAmount + $infrastructureFee;

                // تولید کد رهگیری (قبل از پرداخت)
                $trackingCode = null;
                $maxAttempts = 10;
                for ($i = 0; $i < $maxAttempts; $i++) {
                    $trackingCode = mt_rand(10000000, 99999999);
                    $exists = Appointment::where('tracking_code', $trackingCode)->exists();
                    if (!$exists) {
                        break;
                    }
                    if ($i === $maxAttempts - 1) {
                        throw new \Exception('نمی‌توان کد رهگیری یکتا تولید کرد. لطفاً دوباره تلاش کنید.');
                    }
                }

                // ثبت نوبت (pending)
                $appointmentData = [
                    'doctor_id' => $doctor->id,
                    'patientable_id' => $patient->id,
                    'patientable_type' => get_class($patient),
                    'clinic_id' => $clinicId,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => Carbon::parse($appointmentTime)->format('H:i:s'),
                    'fee' => $totalFee,
                    'tracking_code' => $trackingCode,
                    'reserved_at' => now(),
                    'status' => 'scheduled',
                    'payment_status' => 'pending',
                    'appointment_type' => $serviceType,
                ];
                $appointment = $serviceType === 'in_person'
                    ? Appointment::create($appointmentData)
                    : CounselingAppointment::create($appointmentData);

                // اطلاعات اضافی برای تراکنش
                $meta = [
                    'appointment_id' => $appointment->id,
                    'appointment_type' => $serviceType === 'in_person' ? 'in_person' : 'counseling',
                    'infrastructure_fee' => $infrastructureFee,
                    'patientable_id' => $patient->id,
                    'patientable_type' => get_class($patient),
                ];

                // دریافت URL درگاه پرداخت
                $redirection = $this->paymentService->pay(
                    $totalFee,
                    route('payment.callback'),
                    $meta,
                    $successRedirect,
                    $errorRedirect
                );

                // پاسخ مینیمال
                return response()->json([
                    'status' => 'success',
                    'message' => 'نوبت با موفقیت رزرو شد. لطفاً به درگاه پرداخت هدایت شوید.',
                    'payment_url' => $redirection->getTargetUrl(),
                    'tracking_code' => $trackingCode,
                    'expires_in_seconds' => 600,
                ], 200);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطای اعتبارسنجی ورودی‌ها',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('BookAppointment - Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور',
                'data' => null,
            ], 500);
        }
    }

    /**
     * پاک‌سازی رزروهای pending منقضی‌شده
     */
    private function cleanupExpiredPendingAppointments($doctorId)
    {
        $expired = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'scheduled')
            ->where('payment_status', 'pending')
            ->where('reserved_at', '<', now()->subMinutes(10))
            ->get();
        foreach ($expired as $appointment) {
            $appointment->update([
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
            ]);
        }
    }

    /**
     * پاک‌سازی نوبت‌های ناموفق
     */
    private function cleanupPendingAppointments($doctorId, $appointmentDate, $appointmentTime, $serviceType, $clinicId = null)
    {
        if ($serviceType === 'in_person') {
            $query = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $appointmentDate)
                ->whereRaw("TIME_FORMAT(appointment_time, '%H:%i') = ?", [$appointmentTime])
                ->where('payment_status', 'pending')
                ->where('status', 'scheduled');

            if ($clinicId) {
                $query->where('clinic_id', $clinicId);
            } else {
                $query->whereNull('clinic_id');
            }

            $query->update([
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
            ]);
        } else {
            $query = CounselingAppointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $appointmentDate)
                ->whereRaw("TIME_FORMAT(appointment_time, '%H:%i') = ?", [$appointmentTime])
                ->where('payment_status', 'pending')
                ->where('status', 'scheduled');

            if ($clinicId) {
                $query->where('clinic_id', $clinicId);
            } else {
                $query->whereNull('clinic_id');
            }

            $query->update([
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
            ]);
        }
    }

    /**
     * تبدیل روز هفته به فارسی برای پیام خطا
     */
    private function getPersianDay($dayOfWeek)
    {
        $days = [
            'saturday'  => 'شنبه',
            'sunday'    => 'یک‌شنبه',
            'monday'    => 'دوشنبه',
            'tuesday'   => 'سه‌شنبه',
            'wednesday' => 'چهارشنبه',
            'thursday'  => 'پنج‌شنبه',
            'friday'    => 'جمعه',
        ];
        return $days[$dayOfWeek] ?? $dayOfWeek;
    }

    /**
     * نمایش نتیجه پرداخت و به‌روزرسانی وضعیت نوبت
     */
    public function paymentResult(Request $request)
    {
        try {
            // چک کردن وجود transaction_id
            $transactionId = $request->input('transaction_id') ?? $request->input('Authority');
            if (!$transactionId) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'پارامتر transaction_id یا Authority در درخواست وجود ندارد.',
                    'data'    => null,
                ], 400);
            }

            // پیدا کردن تراکنش
            $transaction = Transaction::where('transaction_id', $transactionId)->first();
            if (!$transaction) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'تراکنش با این شناسه یافت نشد.',
                    'data'    => null,
                ], 404);
            }

            // تأیید پرداخت از طریق خدمت پرداخت (فقط اگر وضعیت pending باشد)
            $isPaymentSuccessful = false;
            if ($transaction->status === 'pending') {
                $verifiedTransaction = $this->paymentService->verify();
                $isPaymentSuccessful = (bool) $verifiedTransaction;
                if ($verifiedTransaction) {
                    $transaction = $verifiedTransaction;
                }
            } else {
                $isPaymentSuccessful = $transaction->status === 'paid';
            }

            // آپدیت وضعیت نوبت
            if ($isPaymentSuccessful) {
                $meta = json_decode($transaction->meta, true);
                $appointmentId = $meta['appointment_id'] ?? null;
                $appointmentType = $meta['appointment_type'] ?? null;
                $doctorId = null;

                if ($appointmentId && $appointmentType) {
                    $appointment = $appointmentType === 'in_person'
                        ? Appointment::find($appointmentId)
                        : CounselingAppointment::find($appointmentId);

                    if ($appointment) {
                        // اگر patientable_id یا patientable_type نال است، مقداردهی کن
                        if (is_null($appointment->patientable_id) || is_null($appointment->patientable_type)) {
                            if (isset($meta['patientable_id']) && isset($meta['patientable_type'])) {
                                $appointment->patientable_id = $meta['patientable_id'];
                                $appointment->patientable_type = $meta['patientable_type'];
                                $appointment->save();
                            }
                        }
                        $validPaymentStatuses = ['pending', 'paid', 'unpaid'];
                        $validStatuses = [
                            'scheduled', 'cancelled', 'attended', 'missed', 'pending_review',
                            'call_answered', 'call_completed', 'video_started', 'video_completed',
                            'text_completed', 'refunded',
                        ];

                        $paymentStatus = 'paid';
                        $status = 'pending_review';

                        if (!in_array($paymentStatus, $validPaymentStatuses)) {
                            throw new \Exception("Invalid payment_status: $paymentStatus");
                        }

                        if (!in_array($status, $validStatuses)) {
                            throw new \Exception("Invalid status: $status");
                        }

                        $appointment->update([
                            'payment_status' => $paymentStatus,
                            'status' => $status,
                        ]);

                        $doctorId = $appointment->doctor_id;
                    }
                }

                // پاک کردن کش زمان‌های فعال پزشک
                if ($doctorId) {
                    $this->clearDoctorCaches($doctorId, $appointmentType === 'in_person' ? 'in_person' : $meta['appointment_type']);
                }
            } else {
                $meta = json_decode($transaction->meta, true);
                $appointmentId = $meta['appointment_id'] ?? null;
                $appointmentType = $meta['appointment_type'] ?? null;

                if ($appointmentId && $appointmentType) {
                    $appointment = $appointmentType === 'in_person'
                        ? Appointment::find($appointmentId)
                        : CounselingAppointment::find($appointmentId);

                    if ($appointment) {
                        // اگر patientable_id یا patientable_type نال است، مقداردهی کن
                        if (is_null($appointment->patientable_id) || is_null($appointment->patientable_type)) {
                            if (isset($meta['patientable_id']) && isset($meta['patientable_type'])) {
                                $appointment->patientable_id = $meta['patientable_id'];
                                $appointment->patientable_type = $meta['patientable_type'];
                                $appointment->save();
                            }
                        }
                        $validPaymentStatuses = ['pending', 'paid', 'unpaid'];
                        $validStatuses = [
                            'scheduled', 'cancelled', 'attended', 'missed', 'pending_review',
                            'call_answered', 'call_completed', 'video_started', 'video_completed',
                            'text_completed', 'refunded',
                        ];

                        $paymentStatus = 'unpaid';
                        $status = 'cancelled';

                        if (!in_array($paymentStatus, $validPaymentStatuses)) {
                            throw new \Exception("Invalid payment_status: $paymentStatus");
                        }

                        if (!in_array($status, $validStatuses)) {
                            throw new \Exception("Invalid status: $status");
                        }

                        $appointment->update([
                            'payment_status' => $paymentStatus,
                            'status' => $status,
                        ]);

                        // پاک کردن کش در صورت لغو نوبت
                        $this->clearDoctorCaches($appointment->doctor_id, $appointmentType === 'in_person' ? 'in_person' : $meta['appointment_type']);
                    }
                }
            }

            // آماده‌سازی داده‌ها برای پاسخ
            $data = [
                'status' => $isPaymentSuccessful ? 'success' : 'failed',
                'message' => $isPaymentSuccessful ? 'پرداخت با موفقیت انجام شد.' : 'پرداخت ناموفق بود.',
                'transaction' => [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'gateway' => $transaction->gateway,
                    'status' => $transaction->status,
                    'transaction_id' => $transaction->transaction_id,
                    'meta' => json_decode($transaction->meta, true),
                    'created_at' => $transaction->created_at->toDateTimeString(),
                    'updated_at' => $transaction->updated_at->toDateTimeString(),
                ],
            ];

            return response()->json($data, $isPaymentSuccessful ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('PaymentResult - Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور در پردازش نتیجه پرداخت',
                'data' => null,
            ], 500);
        }
    }
    public function getAppointmentOptions(Request $request, $doctorId)
    {
        try {
            $doctor = Doctor::where('id', $doctorId)->where('status', true)->first();
            if (!$doctor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'پزشک یافت نشد یا غیرفعال است.',
                    'data' => null,
                ], 404);
            }

            $serviceType = $request->input('service_type', 'in_person');
            $date = $request->input('date', now()->format('Y-m-d'));

            $mainClinic = $doctor->clinics()->where('is_active', true)->where('is_main_clinic', true)->first()
                ?? $doctor->clinics()->where('is_active', true)->first();
            if (!$mainClinic) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'کلینیک یافت نشد.',
                    'data' => null,
                ], 404);
            }

            $dayOfWeek = strtolower(Carbon::parse($date, 'Asia/Tehran')->format('l'));

            // دریافت برنامه کاری
            $workSchedule = $serviceType === 'in_person'
                ? DoctorWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('clinic_id', $mainClinic->id)
                    ->where('day', $dayOfWeek)
                    ->first()
                : DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('clinic_id', $mainClinic->id)
                    ->where('day', $dayOfWeek)
                    ->first();

            if (!$workSchedule || !$workSchedule->is_working) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'پزشک در این روز کاری نیست.',
                    'data' => null,
                ], 400);
            }

            $workHours = json_decode($workSchedule->work_hours, true);
            $appointmentSettings = json_decode($workSchedule->appointment_settings, true);
            $duration = $appointmentSettings[0]['appointment_duration'] ?? 15;

            // محاسبه duration بر اساس max_appointments
            if (!empty($workHours) && isset($workHours[0]['max_appointments'])) {
                $startTime = Carbon::parse("{$date} {$workHours[0]['start']}", 'Asia/Tehran');
                $endTime = Carbon::parse("{$date} {$workHours[0]['end']}", 'Asia/Tehran');
                $totalMinutes = $startTime->diffInMinutes($endTime);
                $duration = floor($totalMinutes / $workHours[0]['max_appointments']);
                Log::debug("GetAppointmentOptions - Calculated duration", [
                    'doctor_id' => $doctorId,
                    'clinic_id' => $mainClinic->id,
                    'date' => $date,
                    'total_minutes' => $totalMinutes,
                    'max_appointments' => $workHours[0]['max_appointments'],
                    'duration' => $duration,
                ]);
            }

            // دریافت زمان‌های رزروشده
            $reservedTimes = $serviceType === 'in_person'
                ? Appointment::where('doctor_id', $doctor->id)
                    ->where('clinic_id', $mainClinic->id)
                    ->where('appointment_date', $date)
                    ->whereIn('status', ['scheduled', 'pending_review'])
                    ->where('payment_status', 'paid')
                    ->pluck('appointment_time')
                    ->toArray()
                : CounselingAppointment::where('doctor_id', $doctor->id)
                    ->where('clinic_id', $mainClinic->id)
                    ->where('appointment_date', $date)
                    ->whereIn('status', ['scheduled', 'pending_review'])
                    ->where('payment_status', 'paid')
                    ->pluck('appointment_time')
                    ->toArray();

            Log::debug("GetAppointmentOptions - Reserved times for doctor {$doctorId} on {$date} for service type {$serviceType}", [
                'reserved_times' => $reservedTimes,
            ]);

            // محاسبه زمان‌های فعال
            $availableTimes = [];
            foreach ($workHours as $slot) {
                $start = Carbon::parse("{$date} {$slot['start']}", 'Asia/Tehran');
                $end = Carbon::parse("{$date} {$slot['end']}", 'Asia/Tehran');

                while ($start->lessThan($end)) {
                    $time = $start->format('H:i');
                    if (!in_array($time, $reservedTimes)) {
                        $availableTimes[] = $time;
                    }
                    $start->addMinutes($duration);
                }
            }

            Log::debug("GetAppointmentOptions - Available times for doctor {$doctorId} on {$date} for service type {$serviceType}", [
                'available_times' => $availableTimes,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'available_times' => $availableTimes,
                    'duration' => $duration,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('GetAppointmentOptions - Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور',
                'data' => null,
            ], 500);
        }
    }

    /**
     * بررسی در دسترس بودن نوبت
     */
    private function checkSlotAvailability($doctor, $appointmentDate, $appointmentTime, $serviceType, $clinicId = null)
    {
        // تنظیم منطقه زمانی به تهران
        $currentTime = Carbon::now('Asia/Tehran');
        $today = Carbon::today('Asia/Tehran');
        $appointmentDateCarbon = Carbon::parse($appointmentDate, 'Asia/Tehran');
        $dayOfWeek = strtolower($appointmentDateCarbon->format('l'));

        // بررسی تاریخ گذشته
        if ($appointmentDateCarbon->lessThan($today)) {
            Log::warning("CheckSlotAvailability - Appointment date {$appointmentDate} is in the past for doctor {$doctor->id}, current date: {$today->toDateString()}");
            return false;
        }

        // پیدا کردن کلینیک اصلی پزشک اگر clinic_id ارسال نشده باشد
        $mainClinic = $clinicId ? $doctor->clinics->where('id', $clinicId)->first() :
            ($doctor->clinics->where('is_main_clinic', true)->first() ?? $doctor->clinics->first());
        $clinicId = $mainClinic ? $mainClinic->id : $clinicId;

        // دریافت زمان‌های مجاز از getAppointmentOptions
        $request = new Request([
            'service_type' => $serviceType,
            'date' => $appointmentDate,
            'clinic_id' => $clinicId
        ]);

        $appointmentOptions = $this->getAppointmentOptions($request, $doctor->id);
        if ($appointmentOptions->getStatusCode() !== 200) {
            Log::warning("CheckSlotAvailability - Failed to get appointment options for doctor {$doctor->id}");
            return false;
        }

        $responseData = json_decode($appointmentOptions->getContent(), true);
        if (!isset($responseData['data']['available_times'])) {
            Log::warning("CheckSlotAvailability - Invalid response format from getAppointmentOptions for doctor {$doctor->id}");
            return false;
        }

        $availableTimes = $responseData['data']['available_times'];

        // بررسی اینکه زمان درخواستی در لیست زمان‌های مجاز هست یا نه
        if (!in_array($appointmentTime, $availableTimes)) {
            Log::warning("CheckSlotAvailability - Requested time {$appointmentTime} is not in available times list for doctor {$doctor->id}", [
                'available_times' => $availableTimes,
                'requested_time' => $appointmentTime
            ]);
            return false;
        }

        // بررسی تعطیلات
        if ($serviceType === 'in_person') {
            $holidaysQuery = DoctorHoliday::where('doctor_id', $doctor->id)
                ->where('status', 'active');
            if ($clinicId) {
                $holidaysQuery->where('clinic_id', $clinicId);
            }
            $holidays = $holidaysQuery->first();
            if ($holidays && $holidays->holiday_dates) {
                $holidayDates = json_decode($holidays->holiday_dates, true);
                if (in_array($appointmentDate, $holidayDates)) {
                    Log::warning("CheckSlotAvailability - Date {$appointmentDate} is a holiday for doctor {$doctor->id}");
                    return false;
                }
            }
        } else {
            $holidaysQuery = DoctorCounselingHoliday::where('doctor_id', $doctor->id)
                ->where('status', 'active');
            if ($clinicId) {
                $holidaysQuery->where('clinic_id', $clinicId);
            }
            $holidays = $holidaysQuery->first();
            if ($holidays && $holidays->holiday_dates) {
                $holidayDates = json_decode($holidays->holiday_dates, true);
                if (in_array($appointmentDate, $holidayDates)) {
                    Log::warning("CheckSlotAvailability - Date {$appointmentDate} is a counseling holiday for doctor {$doctor->id}");
                    return false;
                }
            }
        }

        // بررسی برنامه روزانه خاص
        $specialSchedule = null;
        if ($serviceType === 'in_person') {
            $specialScheduleQuery = SpecialDailySchedule::where('doctor_id', $doctor->id)
                ->where('date', $appointmentDate);
            if ($clinicId) {
                $specialScheduleQuery->where('clinic_id', $clinicId);
            }
            $specialSchedule = $specialScheduleQuery->first();
        } else {
            $specialScheduleQuery = CounselingDailySchedule::where('doctor_id', $doctor->id)
                ->where('date', $appointmentDate);
            if ($clinicId) {
                $specialScheduleQuery->where('clinic_id', $clinicId);
            }
            $specialSchedule = $specialScheduleQuery->first();
        }

        // اگر برنامه روزانه خاص وجود دارد، از آن استفاده کن
        if ($specialSchedule) {
            $workHours = json_decode($specialSchedule->work_hours, true);
            $appointmentSettings = json_decode($specialSchedule->appointment_settings, true);
            $emergencyTimes = json_decode($specialSchedule->emergency_times, true) ?? [];

            Log::debug("CheckSlotAvailability - Using special schedule for doctor {$doctor->id}", [
                'workHours' => $workHours,
                'appointmentSettings' => $appointmentSettings
            ]);
        } else {
            // استفاده از برنامه کاری عادی
            $workSchedule = null;
            if ($serviceType === 'in_person') {
                $workScheduleQuery = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('day', $dayOfWeek);
                if ($clinicId) {
                    $workScheduleQuery->where('clinic_id', $clinicId);
                } else {
                    $workScheduleQuery->whereNull('clinic_id');
                }
                $workSchedule = $workScheduleQuery->first();
            } else {
                $workScheduleQuery = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('day', $dayOfWeek);
                if ($clinicId) {
                    $workScheduleQuery->where('clinic_id', $clinicId);
                } else {
                    $workScheduleQuery->whereNull('clinic_id');
                }
                $workSchedule = $workScheduleQuery->first();
            }

            if (!$workSchedule || !$workSchedule->is_working) {
                Log::warning("CheckSlotAvailability - Doctor {$doctor->id} is not working on {$dayOfWeek} for service type {$serviceType}", [
                    'workSchedule' => $workSchedule ? $workSchedule->toArray() : null
                ]);
                return false;
            }

            $workHours = json_decode($workSchedule->work_hours, true);
            $appointmentSettings = json_decode($workSchedule->appointment_settings, true);
            $emergencyTimes = json_decode($workSchedule->emergency_times, true) ?? [];

            Log::debug("CheckSlotAvailability - Using regular schedule for doctor {$doctor->id}", [
                'workHours' => $workHours,
                'appointmentSettings' => $appointmentSettings
            ]);
        }

        // Validate work hours structure
        if (!is_array($workHours) || empty($workHours)) {
            Log::error("CheckSlotAvailability - Invalid work hours structure for doctor {$doctor->id}", [
                'workHours' => $workHours,
                'serviceType' => $serviceType,
                'dayOfWeek' => $dayOfWeek
            ]);
            return false;
        }

        // بررسی زمان‌های اورژانسی
        $appointmentTimeCarbon = Carbon::parse($appointmentTime, 'Asia/Tehran');
        foreach ($emergencyTimes as $emergency) {
            if (!isset($emergency['start']) || !isset($emergency['end'])) {
                Log::warning("CheckSlotAvailability - Invalid emergency time structure for doctor {$doctor->id}", [
                    'emergency' => $emergency
                ]);
                continue;
            }

            try {
                $start = Carbon::parse($emergency['start'], 'Asia/Tehran');
                $end = Carbon::parse($emergency['end'], 'Asia/Tehran');
                if ($appointmentTimeCarbon->between($start, $end)) {
                    Log::warning("CheckSlotAvailability - Appointment time {$appointmentTime} is within emergency time for doctor {$doctor->id}");
                    return false;
                }
            } catch (\Exception $e) {
                Log::error("CheckSlotAvailability - Error parsing emergency time for doctor {$doctor->id}", [
                    'emergency' => $emergency,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        // بررسی اینکه زمان نوبت در ساعات کاری پزشک قرار دارد یا خیر
        $isWithinWorkHours = false;
        $latestEndTime = null;

        foreach ($workHours as $slot) {
            if (!isset($slot['start']) || !isset($slot['end'])) {
                Log::warning("CheckSlotAvailability - Invalid work hours slot structure for doctor {$doctor->id}", [
                    'slot' => $slot
                ]);
                continue;
            }

            try {
                $start = Carbon::parse("{$appointmentDate} {$slot['start']}", 'Asia/Tehran');
                $end = Carbon::parse("{$appointmentDate} {$slot['end']}", 'Asia/Tehran');
                $appointmentTimeFull = Carbon::parse("{$appointmentDate} {$appointmentTime}", 'Asia/Tehran');

                // پیدا کردن آخرین زمان پایان ساعات کاری
                if (!$latestEndTime || $end->greaterThan($latestEndTime)) {
                    $latestEndTime = $end;
                }

                if ($appointmentTimeFull->greaterThanOrEqualTo($start) && $appointmentTimeFull->lessThanOrEqualTo($end)) {
                    $isWithinWorkHours = true;

                    // بررسی تعداد حداکثر نوبت‌ها در این اسلات
                    $maxAppointments = $slot['max_appointments'] ?? null;
                    if ($maxAppointments !== null) {
                        $existingAppointmentsQuery = $serviceType === 'in_person'
                            ? Appointment::where('doctor_id', $doctor->id)
                                ->where('appointment_date', $appointmentDate)
                                ->where('appointment_time', '>=', $slot['start'])
                                ->where('appointment_time', '<=', $slot['end'])
                                ->whereIn('status', ['scheduled', 'pending_review'])
                                ->where('payment_status', 'paid')
                            : CounselingAppointment::where('doctor_id', $doctor->id)
                                ->where('appointment_date', $appointmentDate)
                                ->where('appointment_time', '>=', $slot['start'])
                                ->where('appointment_time', '<=', $slot['end'])
                                ->whereIn('status', ['scheduled', 'pending_review'])
                                ->where('payment_status', 'paid');

                        $existingAppointments = $existingAppointmentsQuery->count();

                        if ($existingAppointments >= $maxAppointments) {
                            Log::warning("CheckSlotAvailability - Max appointments reached for doctor {$doctor->id} on {$appointmentDate} in slot {$slot['start']}-{$slot['end']}");
                            return false;
                        }
                    }
                    break;
                }
            } catch (\Exception $e) {
                Log::error("CheckSlotAvailability - Error parsing time for doctor {$doctor->id}", [
                    'slot' => $slot,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        if (!$isWithinWorkHours) {
            Log::warning("CheckSlotAvailability - Appointment time {$appointmentTime} is outside working hours for doctor {$doctor->id} on {$dayOfWeek}", [
                'workHours' => $workHours,
                'appointmentTime' => $appointmentTime
            ]);
            return false;
        }

        // شرط جدید: مقایسه زمان فعلی با زمان پایان ساعات کاری برای نوبت‌های امروز
        if ($latestEndTime && $appointmentDateCarbon->isToday()) {
            if ($currentTime->greaterThan($latestEndTime)) {
                Log::warning("CheckSlotAvailability - Current time {$currentTime->toDateTimeString()} is past the latest working hours {$latestEndTime->toDateTimeString()} for doctor {$doctor->id} on {$appointmentDate}");
                return false;
            }
        }

        // گرفتن تنظیمات نوبت‌دهی
        $config = null;
        if ($serviceType === 'in_person') {
            $configQuery = DoctorAppointmentConfig::where('doctor_id', $doctor->id);
            if ($clinicId) {
                $configQuery->where('clinic_id', $clinicId);
            }
            $config = $configQuery->first();
        } elseif (in_array($serviceType, ['phone', 'text', 'video'])) {
            $configQuery = DoctorCounselingConfig::where('doctor_id', $doctor->id);
            if ($clinicId) {
                $configQuery->where('clinic_id', $clinicId);
            }
            $config = $configQuery->first();
        }

        if (!$config) {
            Log::warning("CheckSlotAvailability - Config not found for doctor {$doctor->id} and service type {$serviceType}");
            return false;
        }

        if (in_array($serviceType, ['phone', 'text', 'video'])) {
            $consultationTypes = $config->consultation_types ? json_decode($config->consultation_types, true) : [];
            $isCounselingAllowed = $config->online_consultation &&
                $config->auto_scheduling &&
                in_array($serviceType, $consultationTypes);

            if (!$isCounselingAllowed) {
                Log::warning("CheckSlotAvailability - Counseling not allowed for doctor {$doctor->id} and service type {$serviceType}", [
                    'online_consultation' => $config->online_consultation,
                    'auto_scheduling' => $config->auto_scheduling,
                    'consultation_types' => $consultationTypes,
                ]);
                return false;
            }
        }

        // بررسی رزروهای قبلی با وضعیت‌های scheduled, pending_review و paid
        $duration = $appointmentSettings[0]['appointment_duration'] ?? ($config->appointment_duration ?? 15);
        $startTime = Carbon::parse("$appointmentDate $appointmentTime", 'Asia/Tehran');
        $endTime = $startTime->copy()->addMinutes($duration);

        // دیباگ: لاگ کردن مقادیر ورودی
        Log::debug("CheckSlotAvailability - Checking slot for doctor {$doctor->id}, clinic " . ($clinicId ?? 'null') . ", date {$appointmentDate}, time {$appointmentTime}, service type {$serviceType}, current time {$currentTime->toDateTimeString()}");

        $existingAppointment = null;
        if ($serviceType === 'in_person') {
            $existingAppointmentQuery = Appointment::where('doctor_id', $doctor->id)
                ->where('appointment_date', $appointmentDate)
                ->whereIn('status', ['scheduled', 'pending_review'])
                ->where('payment_status', 'paid')
                ->whereRaw("TIME_FORMAT(appointment_time, '%H:%i') = ?", [$appointmentTime]);

            $existingAppointment = $existingAppointmentQuery->first();

            if ($existingAppointment) {
                Log::warning("CheckSlotAvailability - Slot already taken for doctor {$doctor->id} on {$appointmentDate} at {$appointmentTime} for service type {$serviceType}", [
                    'existing_appointment' => $existingAppointment->toArray(),
                ]);
                return false;
            }
        } else {
            $existingAppointmentQuery = CounselingAppointment::where('doctor_id', $doctor->id)
                ->where('appointment_date', $appointmentDate)
                ->whereIn('status', ['scheduled', 'pending_review'])
                ->where('payment_status', 'paid')
                ->whereRaw("TIME_FORMAT(appointment_time, '%H:%i') = ?", [$appointmentTime]);

            $existingAppointment = $existingAppointmentQuery->first();

            if ($existingAppointment) {
                Log::warning("CheckSlotAvailability - Slot already taken for doctor {$doctor->id} on {$appointmentDate} at {$appointmentTime} for service type {$serviceType}", [
                    'existing_appointment' => $existingAppointment->toArray(),
                ]);
                return false;
            }
        }

        // دیباگ: لاگ کردن در صورت عدم یافتن رزرو
        Log::debug("CheckSlotAvailability - No existing appointment found for doctor {$doctor->id} on {$appointmentDate} at {$appointmentTime} for service type {$serviceType}");

        return true;
    }

    /**
     * پاک کردن کش‌ها
     */
    private function clearDoctorCaches($doctorId = null, $serviceType = null)
    {
        Cache::forget("doctors_list_*_*_*_*_*_*_*_*");
        if ($doctorId) {
            $types = $serviceType ? [$serviceType] : ['in_person', 'phone', 'text', 'video'];
            foreach ($types as $type) {
                Cache::forget("next_available_slot_doctor_{$doctorId}_{$type}");
            }
        }
    }
}
