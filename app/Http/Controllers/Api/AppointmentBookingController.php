<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Models\MedicalCenter;
use App\Models\InfrastructureFee;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\SendSmsNotificationJob;
use App\Models\ClinicDepositSetting;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Auth;
use App\Models\CounselingAppointment;
use Illuminate\Support\Facades\Cache;
use App\Models\DoctorCounselingConfig;
use App\Models\CounselingDailySchedule;
use App\Models\DoctorAppointmentConfig;
use App\Models\DoctorCounselingHoliday;
use Illuminate\Support\Facades\Storage;
use App\Models\DoctorCounselingWorkSchedule;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\App\Http\Models\Transaction;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class AppointmentBookingController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
 * دریافت اطلاعات پزشک و نوبت برای صفحه رزرو
 * @param string $doctorSlug اسلاگ پزشک
 * @response 200 {
 *   "status": "success",
 *   "data": {
 *     "doctor": {
 *       "id": 1,
 *       "slug": "دکتر-نمونه",
 *       "name": "دکتر نمونه",
 *       "specialty": "متخصص قلب",
 *       "avatar": "/storage/avatars/doctor.jpg",
 *       "location": {
 *         "province": "تهران",
 *         "province_slug": "تهران",
 *         "city": "تهران",
 *         "address": "خیابان نمونه"
 *       },
 *       "office_phone": "02112345678"
 *     },
 *     "appointment": {
 *       "date_time": "شنبه ۲ فروردین ساعت ۱۴:۳۰",
 *       "service_type": "in_person"
 *     },
 *     "payment": {
 *       "deposit_amount": 50000,
 *       "infrastructure_fee": 10000,
 *       "total_amount": 60000
 *     },
 *     "tracking_code": "12345678",
 *     "reserved_at": "2025-08-08 22:55:00"
 *   }
 * }
 * @response 404 {
 *   "status": "error",
 *   "message": "پزشک یافت نشد یا غیرفعال است.",
 *   "data": null
 * }
 * @response 422 {
 *   "status": "error",
 *   "message": "خطای اعتبارسنجی ورودی‌ها",
 *   "errors": {}
 * }
 * @response 500 {
 *   "status": "error",
 *   "message": "خطای سرور",
 *   "data": null
 * }
 */
    public function getBookingDetails(Request $request, $doctorSlug)
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

            // گرفتن کاربر لاگین‌شده و تعیین guard
            $authenticatedUser = null;
            $patientableType = null;
            if (Auth::guard('manager-api')->check()) {
                $authenticatedUser = Auth::guard('manager-api')->user();
                $patientableType = get_class($authenticatedUser);
            } elseif (Auth::guard('secretary-api')->check()) {
                $authenticatedUser = Auth::guard('secretary-api')->user();
                $patientableType = get_class($authenticatedUser);
            } elseif (Auth::guard('doctor-api')->check()) {
                $authenticatedUser = Auth::guard('doctor-api')->user();
                $patientableType = get_class($authenticatedUser);
            } elseif (Auth::guard('api')->check()) {
                $authenticatedUser = Auth::guard('api')->user();
                $patientableType = get_class($authenticatedUser);
            }
            $patientableId = $authenticatedUser ? $authenticatedUser->id : null;

            // پیدا کردن پزشک
            $doctor = Doctor::where('slug', $doctorSlug)
                ->where('status', true)
                ->first();

            if (!$doctor) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'پزشک یافت نشد یا غیرفعال است.',
                    'data'    => null,
                ], 404);
            }

            // اطلاعات کلینیک
            $mainClinic = $doctor->medicalCenters()
                ->where('is_active', true)
                ->where('type', 'policlinic')
                ->where('is_main_center', true)
                ->select('medical_centers.id', 'medical_centers.name', 'medical_centers.slug', 'medical_centers.address', 'medical_centers.phone', 'medical_centers.city_id')
                ->first()
                ?? $doctor->medicalCenters()
                    ->where('is_active', true)
                    ->where('type', 'policlinic')
                    ->select('medical_centers.id', 'medical_centers.name', 'medical_centers.slug', 'medical_centers.address', 'medical_centers.phone', 'medical_centers.city_id')
                    ->first();
            $city = $mainClinic ? $mainClinic->city()->value('name') : 'نامشخص';
            $province = $mainClinic && $mainClinic->city ? $mainClinic->city->province()->select('zones.id', 'zones.name', 'zones.slug')->first() : null;

            // تبدیل تاریخ به شمسی
            $jalaliDate = Jalalian::fromCarbon(Carbon::parse("$appointmentDate $appointmentTime", 'Asia/Tehran'));
            $formattedDateTime = $jalaliDate->format('l d F') . ' ساعت ' . $jalaliDate->format('H:i');

            // محاسبه هزینه‌ها
            $depositSetting = ClinicDepositSetting::where('doctor_id', $doctor->id)
                ->where('medical_center_id', $mainClinic->id)
                ->where('is_active', true)
                ->first();
            $depositAmount = $depositSetting ? $depositSetting->deposit_amount : 0;
            $infrastructureFee = InfrastructureFee::where('appointment_type', $serviceType)
                ->where('is_active', true)
                ->first()->fee ?? 0;

            // بررسی وجود رزرو موقت فعال برای همین اسلات
            $existingAppointment = Appointment::where('doctor_id', $doctor->id)
                ->where('medical_center_id', $mainClinic ? $mainClinic->id : null)
                ->where('appointment_date', $appointmentDate)
                ->whereRaw("TIME_FORMAT(appointment_time, '%H:%i') = ?", [$appointmentTime])
                ->where('status', 'scheduled')
                ->where('payment_status', 'pending')
                ->where('reserved_at', '>=', now()->subMinutes(10))
                ->first();

            if ($existingAppointment) {
                $trackingCode = $existingAppointment->tracking_code;
                $reservedAt = $existingAppointment->reserved_at ? $existingAppointment->reserved_at->copy()->setTimezone('Asia/Tehran') : null;
            } else {
                // تولید کد رهگیری یکتا (قبل از پرداخت)
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
                // ثبت رزرو موقت
                $appointment = Appointment::create([
                    'doctor_id' => $doctor->id,
                    'medical_center_id' => $mainClinic ? $mainClinic->id : null,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => Carbon::parse($appointmentTime)->format('H:i:s'),
                    'fee' => $depositAmount + $infrastructureFee,
                    'tracking_code' => $trackingCode,
                    'reserved_at' => now('Asia/Tehran'),
                    'status' => 'scheduled',
                    'payment_status' => 'pending',
                    'appointment_type' => $serviceType,
                    'patientable_id' => $patientableId,
                    'patientable_type' => $patientableType,
                ]);
                $reservedAt = $appointment->reserved_at ? $appointment->reserved_at->copy()->setTimezone('Asia/Tehran') : null;
            }

            // آماده‌سازی داده‌ها برای نمایش
            $data = [
                'doctor' => [
                    'id' => $doctor->id,
                    'slug' => $doctor->slug,
                    'name' => $doctor->display_name ?? ($doctor->first_name . ' ' . $doctor->last_name),
                    'specialty' => $doctor->specialty()->value('name') ?? 'نامشخص',
                    'avatar' => $doctor->profile_photo_path ? Storage::url($doctor->profile_photo_path) : '/default-avatar.png',
                    'location' => [
                        'province' => $province ? $province->name : 'نامشخص',
                        'province_slug' => $province ? $province->slug : null,
                        'city' => $city,
                        'address' => $mainClinic ? $mainClinic->address : 'نامشخص',
                        'clinic_slug' => $mainClinic ? $mainClinic->slug : null,
                    ],
                    'office_phone' => $mainClinic ? $mainClinic->phone : 'نامشخص',
                ],
                'appointment' => [
                    'date_time' => $formattedDateTime,
                    'service_type' => $serviceType,
                ],
                'payment' => [
                    'deposit_amount' => $depositAmount,
                    'infrastructure_fee' => $infrastructureFee,
                    'total_amount' => $depositAmount + $infrastructureFee,
                ],
                'tracking_code' => $trackingCode,
                'reserved_at' => $reservedAt ? $reservedAt->toDateTimeString() : null,
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطای اعتبارسنجی ورودی‌ها',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور: ' . $e->getMessage(),
                'data' => null,
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
        // جستجو در هر دو جدول
        $appointment = Appointment::where('tracking_code', $trackingCode)->first();
        if (!$appointment) {
            $appointment = CounselingAppointment::where('tracking_code', $trackingCode)->first();
        }
        if (!$appointment) {
            return response()->json([
                'status' => 'error',
                'message' => 'رزروی با این کد رهگیری یافت نشد.',
                'data' => null,
            ], 404);
        }
        $now = now('Asia/Tehran');
        $reservedAt = $appointment->reserved_at ? $appointment->reserved_at->copy()->setTimezone('Asia/Tehran') : null;
        $expiresAt = $reservedAt ? $reservedAt->copy()->addMinutes(10) : null;
        $remaining = $expiresAt && $now->lessThan($expiresAt) ? $now->diffInSeconds($expiresAt) : 0;
        $isExpired = $expiresAt && $now->greaterThanOrEqualTo($expiresAt);
        $status = $appointment->payment_status === 'paid' ? 'paid' : ($isExpired ? 'expired' : 'pending');
        $response = [
            'status' => 'success',
            'data' => [
                'tracking_code' => $trackingCode,
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'status' => $status,
                'remaining_seconds' => $remaining,
                'reserved_at' => $reservedAt ? $reservedAt->toDateTimeString() : null,
                'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : null,
            ],
        ];
        if ($status === 'expired' && $remaining === 0) {
            $response['message'] = 'مهلت پرداخت شما به پایان رسید و این نوبت دیگر قابل پرداخت نیست. لطفاً مجدداً اقدام به رزرو نوبت نمایید.';
            // حذف کامل رکورد نوبت از دیتابیس (force delete)
            $appointment->forceDelete();
        }
        return response()->json($response);
    }

    /**
     * رزرو نوبت برای پزشک
     * @param string $doctorSlug اسلاگ پزشک
     * @response 200 {
     *   "status": "success",
     *   "message": "نوبت با موفقیت رزرو شد. لطفاً به درگاه پرداخت هدایت شوید.",
     *   "payment_url": "http://payment-gateway.com/pay/123",
     *   "tracking_code": "12345678",
     *   "expires_in_seconds": 600,
     *   "data": {
     *     "doctor": {
     *       "id": 1,
     *       "slug": "دکتر-نمونه",
     *       "name": "دکتر نمونه"
     *     },
     *     "clinic": {
     *       "id": 1,
     *       "slug": "کلینیک-نمونه",
     *       "name": "کلینیک نمونه"
     *     }
     *   }
     * }
     * @response 400 {
     *   "status": "error",
     *   "message": "این نوبت دیگر در دسترس نیست.",
     *   "data": null
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "پزشک یا کلینیک یافت نشد یا غیرفعال است.",
     *   "data": null
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "خطای اعتبارسنجی ورودی‌ها",
     *   "errors": {}
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function bookAppointment(Request $request, $doctorSlug)
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
                'clinic_slug'      => 'nullable|exists:medical_centers,slug',
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
                'clinic_slug.exists'           => 'کلینیک انتخاب‌شده وجود ندارد.',
            ]);

            // پیدا کردن پزشک
            $doctor = Doctor::where('slug', $doctorSlug)
                ->where('status', true)
                ->first();

            if (!$doctor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'پزشک یافت نشد یا غیرفعال است.',
                    'data' => null,
                ], 404);
            }

            // پاک‌سازی رزروهای منقضی‌شده قبل از رزرو جدید
            $this->cleanupExpiredPendingAppointments($doctor->id);

            // استفاده از تراکنش برای جلوگیری از رزروهای همزمان
            return DB::transaction(function () use ($request, $doctor, $validated) {
                $appointmentDate = $validated['appointment_date'];
                $appointmentTime = $validated['appointment_time'];
                $serviceType = $validated['service_type'];
                $patientType = $validated['patient_type'];
                $successRedirect = $validated['success_redirect'];
                $errorRedirect = $validated['error_redirect'];
                $clinicSlug = $validated['clinic_slug'];

                // گرفتن کاربر احراز هویت‌شده
                $authenticatedUser = $request->attributes->get('user');

                // پیدا کردن یا ایجاد بیمار
                $patient = null;
                if ($patientType === 'self') {
                    $patient = $authenticatedUser;
                } else {
                    $patient = User::create([
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                        'national_code' => $validated['national_code'],
                        'mobile' => $validated['mobile'],
                        'email' => $validated['email'],
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

                // تعیین کلینیک
                $mainClinic = null;
                if ($clinicSlug) {
                    // اگر clinic_slug در درخواست ارسال شده، کلینیک مربوطه را پیدا کن
                    $mainClinic = $doctor->medicalCenters()
                        ->where('slug', $clinicSlug)
                        ->where('is_active', true)
                        ->where('type', 'policlinic')
                        ->select('medical_centers.id', 'medical_centers.name', 'medical_centers.slug')
                        ->first();
                    if (!$mainClinic) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'کلینیک انتخاب‌شده یافت نشد یا غیرفعال است.',
                            'data' => null,
                        ], 404);
                    }
                } else {
                    // اگر clinic_slug ارسال نشده، کلینیک اصلی یا اولین کلینیک فعال را انتخاب کن
                    $mainClinic = $doctor->medicalCenters()
                        ->where('is_active', true)
                        ->where('type', 'policlinic')
                        ->where('is_main_center', true)
                        ->select('medical_centers.id', 'medical_centers.name', 'medical_centers.slug')
                        ->first()
                        ?? $doctor->medicalCenters()
                            ->where('is_active', true)
                            ->where('type', 'policlinic')
                            ->select('medical_centers.id', 'medical_centers.name', 'medical_centers.slug')
                            ->first();
                }

                // اگر کلینیک پیدا نشد، clinic_id را NULL تنظیم کن
                $clinicId = $mainClinic ? $mainClinic->id : null;

                // بررسی در دسترس بودن نوبت
                $config = null;
                if ($serviceType === 'in_person') {
                    $config = \App\Models\DoctorAppointmentConfig::where('doctor_id', $doctor->id)
                        ->where('medical_center_id', $clinicId)
                        ->first();
                } else {
                    $config = \App\Models\DoctorCounselingConfig::where('doctor_id', $doctor->id)
                        ->where('medical_center_id', $clinicId)
                        ->first();
                }

                $isSlotAvailable = $this->checkSlotAvailability($doctor, $appointmentDate, $appointmentTime, $serviceType, $clinicId);
                if (!$isSlotAvailable) {
                    if ($config && !$config->auto_scheduling) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'امکان رزرو آنلاین برای این پزشک فعال نیست. لطفاً برای دریافت نوبت با مطب تماس بگیرید یا پزشک دیگری را انتخاب کنید.',
                            'data' => null,
                        ], 400);
                    }
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
                        ->where('medical_center_id', $mainClinic->id)
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
                    'medical_center_id' => $clinicId,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => Carbon::parse($appointmentTime)->format('H:i:s'),
                    'fee' => $totalFee,
                    'tracking_code' => $trackingCode,
                    'reserved_at' => now('Asia/Tehran'),
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

                // پاسخ با اطلاعات اضافی
                return response()->json([
                    'status' => 'success',
                    'message' => 'نوبت با موفقیت رزرو شد. لطفاً به درگاه پرداخت هدایت شوید.',
                    'payment_url' => $redirection->getTargetUrl(),
                    'tracking_code' => $trackingCode,
                    'expires_in_seconds' => 600,
                    'data' => [
                        'doctor' => [
                            'id' => $doctor->id,
                            'slug' => $doctor->slug,
                            'name' => $doctor->display_name ?? ($doctor->first_name . ' ' . $doctor->last_name),
                        ],
                        'clinic' => $mainClinic ? [
                            'id' => $mainClinic->id,
                            'slug' => $mainClinic->slug,
                            'name' => $mainClinic->name,
                        ] : null,
                    ],
                ], 200);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطای اعتبارسنجی ورودی‌ها',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور: ' . $e->getMessage(),
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
                $query->where('medical_center_id', $clinicId);
            } else {
                $query->whereNull('medical_center_id');
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
                $query->where('medical_center_id', $clinicId);
            } else {
                $query->whereNull('medical_center_id');
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


    // ... existing code ...
    /**
     * نمایش نتیجه پرداخت و به‌روزرسانی وضعیت نوبت
     *
     * @OA\Post(
     *   path="/appointments/payment/result",
     *   summary="نمایش نتیجه پرداخت و به‌روزرسانی وضعیت نوبت",
     *   description="این متد نتیجه پرداخت را بر اساس transaction_id یا Authority بررسی می‌کند.",
     *   tags={"Appointments"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *           property="transaction_id",
     *           type="string",
     *           description="شناسه تراکنش پرداخت (Transaction ID)",
     *           example="123456"
     *         ),
     *         @OA\Property(
     *           property="Authority",
     *           type="string",
     *           description="کد Authority برگشتی از درگاه پرداخت",
     *           example="A0000000000000000000000000000000000000"
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=200, description="پرداخت موفق یا ناموفق"),
     *   @OA\Response(response=400, description="پارامتر ارسال نشده یا خطای اعتبارسنجی"),
     *   @OA\Response(response=404, description="تراکنش یافت نشد"),
     *   @OA\Response(response=500, description="خطای سرور")
     * )
     */
    public function paymentResult(Request $request)
    {
        try {
            Log::info('paymentResult called', [
                'request_query' => $request->query(),
                'request_input' => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            // چک کردن وجود transaction_id
            $transactionId = $request->input('transaction_id') ?? $request->input('Authority');
            Log::info('paymentResult - transactionId', ['transactionId' => $transactionId]);
            if (!$transactionId) {
                Log::warning('paymentResult - transactionId missing');
                return response()->json([
                    'status'  => 'error',
                    'message' => 'پارامتر transaction_id یا Authority در درخواست وجود ندارد.',
                    'data'    => null,
                ], 400);
            }

            // پیدا کردن تراکنش
            $transaction = \App\Models\Transaction::where('transaction_id', $transactionId)
                ->latest()
                ->first();
            Log::info('paymentResult - transaction', ['transaction' => $transaction]);
            if (!$transaction) {
                Log::warning('paymentResult - transaction not found', ['transactionId' => $transactionId]);
                return response()->json([
                    'status'  => 'error',
                    'message' => 'تراکنش یافت نشد.',
                    'data'    => null,
                ], 404);
            }

            // متا دیتا
            $meta = is_array($transaction->meta) ? $transaction->meta : json_decode($transaction->meta, true);
            Log::info('paymentResult - transaction meta', ['meta' => $meta]);

            // پیدا کردن نوبت
            $appointment = null;
            if (isset($meta['appointment_id']) && isset($meta['appointment_type'])) {
                if ($meta['appointment_type'] === 'in_person') {
                    $appointment = \App\Models\Appointment::find($meta['appointment_id']);
                } else {
                    $appointment = \App\Models\CounselingAppointment::find($meta['appointment_id']);
                }
            }
            Log::info('paymentResult - appointment', ['appointment' => $appointment]);

            // مقداردهی patientable_id و patientable_type اگر نال است
            if ($appointment && (is_null($appointment->patientable_id) || is_null($appointment->patientable_type))) {
                if (isset($meta['patientable_id']) && isset($meta['patientable_type'])) {
                    $appointment->patientable_id = $meta['patientable_id'];
                    $appointment->patientable_type = $meta['patientable_type'];
                    $appointment->save();
                    Log::info('paymentResult - updated patientable fields', [
                        'patientable_id' => $meta['patientable_id'],
                        'patientable_type' => $meta['patientable_type'],
                    ]);
                }
            }

            // وضعیت پرداخت و نوبت
            $validPaymentStatuses = ['pending', 'paid', 'unpaid'];
            $validStatuses = [
                'scheduled', 'cancelled', 'attended', 'missed', 'pending_review',
                'call_answered', 'call_completed', 'video_started', 'video_completed',
                'text_completed', 'refunded',
            ];
            $paymentStatus = 'paid';
            $status = 'pending_review';
            Log::info('paymentResult - status update', [
                'paymentStatus' => $paymentStatus,
                'status' => $status,
            ]);
            if ($appointment) {
                $appointment->update([
                    'payment_status' => $paymentStatus,
                    'status' => $status,
                ]);
                Log::info('paymentResult - appointment updated', [
                    'appointment_id' => $appointment->id,
                    'payment_status' => $paymentStatus,
                    'status' => $status,
                ]);
            }

            // ارسال پیامک پس از پرداخت موفق
            $userMobile = null;
            if ($appointment && method_exists($appointment, 'patientable') && $appointment->patientable) {
                $userMobile = $appointment->patientable->mobile;
            } elseif ($appointment && isset($appointment->mobile)) {
                $userMobile = $appointment->mobile;
            }
            Log::info('paymentResult - userMobile', ['userMobile' => $userMobile]);
            if ($userMobile) {
                $templateId = 100282;
                $params = [
                    $appointment->tracking_code ?? null
                ];
                $message = "به نوبه : نوبت شما ثبت شد جزئیات:\nhttps://emr-benobe.ir/profile/user?section=appointments\nکدرهگیری : {$appointment->tracking_code}";
                $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
                $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
                Log::info('paymentResult - sms params', [
                    'gatewayName' => $gatewayName,
                    'templateId' => $templateId,
                    'params' => $params,
                    'message' => $message,
                ]);
                if ($gatewayName === 'pishgamrayan') {
                    \App\Jobs\SendSmsNotificationJob::dispatch(
                        $message,
                        [$userMobile],
                        $templateId,
                        $params
                    )->delay(now()->addSeconds(5));
                    Log::info('paymentResult - sms job dispatched (pishgamrayan)');
                } else {
                    \App\Jobs\SendSmsNotificationJob::dispatch(
                        $message,
                        [$userMobile]
                    )->delay(now()->addSeconds(5));
                    Log::info('paymentResult - sms job dispatched (other gateway)');
                }
            } else {
                Log::warning('paymentResult - userMobile not found');
            }

            // آماده‌سازی داده‌ها برای پاسخ
            $data = [
                'status' => 'success',
                'message' => 'پرداخت با موفقیت انجام شد و پیامک ارسال شد.',
                'transaction_id' => $transactionId,
                'appointment_id' => $appointment ? $appointment->id : null,
                'user_mobile' => $userMobile,
                'tracking_code' => $appointment ? $appointment->tracking_code : null,
            ];
            Log::info('paymentResult - response', $data);
            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error('paymentResult - exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور در پردازش نتیجه پرداخت',
                'data'    => null,
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

            $mainClinic = $doctor->medicalCenters()
                ->where('is_active', true)
                ->where('type', 'policlinic')
                ->where('is_main_center', true)
                ->first()
                ?? $doctor->medicalCenters()->where('is_active', true)->where('type', 'policlinic')->first();
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
                    ->where('medical_center_id', $mainClinic->id)
                    ->where('day', $dayOfWeek)
                    ->first()
                : DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('medical_center_id', $mainClinic->id)
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

            // Get duration using helper method
            $duration = $this->getAppointmentDuration($appointmentSettings, $dayOfWeek, 15);

            // محاسبه duration بر اساس max_appointments
            if (!empty($workHours) && isset($workHours[0]['max_appointments'])) {
                $startTime = Carbon::parse("{$date} {$workHours[0]['start']}", 'Asia/Tehran');
                $endTime = Carbon::parse("{$date} {$workHours[0]['end']}", 'Asia/Tehran');
                $totalMinutes = $startTime->diffInMinutes($endTime);
                $duration = floor($totalMinutes / $workHours[0]['max_appointments']);

            }

            // دریافت زمان‌های رزروشده (اصلاح شده)
            $reservedTimes = $serviceType === 'in_person'
                ? Appointment::where('doctor_id', $doctor->id)
                    ->where('medical_center_id', $mainClinic->id)
                    ->where('appointment_date', $date)
                    ->whereIn('status', ['scheduled', 'pending_review'])
                    ->where(function ($query) {
                        $query->where('payment_status', 'paid')
                              ->orWhere(function ($q) {
                                  $q->where('payment_status', 'pending')
                                    ->where('reserved_at', '>=', now()->subMinutes(10));
                              });
                    })
                    ->pluck('appointment_time')
                    ->toArray()
                : CounselingAppointment::where('doctor_id', $doctor->id)
                    ->where('medical_center_id', $mainClinic->id)
                    ->where('appointment_date', $date)
                    ->whereIn('status', ['scheduled', 'pending_review'])
                    ->where(function ($query) {
                        $query->where('payment_status', 'paid')
                              ->orWhere(function ($q) {
                                  $q->where('payment_status', 'pending')
                                    ->where('reserved_at', '>=', now()->subMinutes(10));
                              });
                    })
                    ->pluck('appointment_time')
                    ->toArray();


            // محاسبه زمان‌های فعال
            $availableTimes = [];
            $autoScheduling = 1;
            if ($serviceType === 'in_person') {
                $config = \App\Models\DoctorAppointmentConfig::where('doctor_id', $doctor->id)
                    ->where('medical_center_id', $mainClinic->id)
                    ->first();
                if ($config && !$config->auto_scheduling) {
                    $autoScheduling = 0;
                }
            } else {
                $config = \App\Models\DoctorCounselingConfig::where('doctor_id', $doctor->id)
                    ->where('medical_center_id', $mainClinic->id)
                    ->first();
                if ($config && !$config->auto_scheduling) {
                    $autoScheduling = 0;
                }
            }
            if ($autoScheduling) {
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
            }



            return response()->json([
                'status' => 'success',
                'data' => [
                    'available_times' => $availableTimes,
                    'duration' => $duration,
                ],
            ], 200);
        } catch (\Exception $e) {

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

            return false;
        }

        // پیدا کردن کلینیک اصلی پزشک اگر clinic_id ارسال نشده باشد
        $mainClinic = $clinicId ? $doctor->medicalCenters->where('id', $clinicId)->first() :
            ($doctor->medicalCenters->where('is_main_center', true)->first() ?? $doctor->medicalCenters->first());
        $clinicId = $mainClinic ? $mainClinic->id : $clinicId;

        // دریافت زمان‌های مجاز از getAppointmentOptions
        $request = new Request([
            'service_type' => $serviceType,
            'date' => $appointmentDate,
            'clinic_id' => $clinicId
        ]);

        $appointmentOptions = $this->getAppointmentOptions($request, $doctor->id);
        if ($appointmentOptions->getStatusCode() !== 200) {

            return false;
        }

        $responseData = json_decode($appointmentOptions->getContent(), true);
        if (!isset($responseData['data']['available_times'])) {

            return false;
        }

        $availableTimes = $responseData['data']['available_times'];

        // بررسی اینکه زمان درخواستی در لیست زمان‌های مجاز هست یا نه
        if (!in_array($appointmentTime, $availableTimes)) {

            return false;
        }

        // بررسی تعطیلات
        if ($serviceType === 'in_person') {
            $holidaysQuery = DoctorHoliday::where('doctor_id', $doctor->id)
                ->where('status', 'active');
            if ($clinicId) {
                $holidaysQuery->where('medical_center_id', $clinicId);
            }
            $holidays = $holidaysQuery->first();
            if ($holidays && $holidays->holiday_dates) {
                $holidayDates = json_decode($holidays->holiday_dates, true);
                if (in_array($appointmentDate, $holidayDates)) {

                    return false;
                }
            }
        } else {
            $holidaysQuery = DoctorCounselingHoliday::where('doctor_id', $doctor->id)
                ->where('status', 'active');
            if ($clinicId) {
                $holidaysQuery->where('medical_center_id', $clinicId);
            }
            $holidays = $holidaysQuery->first();
            if ($holidays && $holidays->holiday_dates) {
                $holidayDates = json_decode($holidays->holiday_dates, true);
                if (in_array($appointmentDate, $holidayDates)) {

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
                $specialScheduleQuery->where('medical_center_id', $clinicId);
            }
            $specialSchedule = $specialScheduleQuery->first();
        } else {
            $specialScheduleQuery = CounselingDailySchedule::where('doctor_id', $doctor->id)
                ->where('date', $appointmentDate);
            if ($clinicId) {
                $specialScheduleQuery->where('medical_center_id', $clinicId);
            }
            $specialSchedule = $specialScheduleQuery->first();
        }

        // اگر برنامه روزانه خاص وجود دارد، از آن استفاده کن
        if ($specialSchedule) {
            $workHours = json_decode($specialSchedule->work_hours, true);
            $appointmentSettings = json_decode($specialSchedule->appointment_settings, true);
            $emergencyTimes = json_decode($specialSchedule->emergency_times, true) ?? [];


        } else {
            // استفاده از برنامه کاری عادی
            $workSchedule = null;
            if ($serviceType === 'in_person') {
                $workScheduleQuery = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('day', $dayOfWeek);
                if ($clinicId) {
                    $workScheduleQuery->where('medical_center_id', $clinicId);
                } else {
                    $workScheduleQuery->whereNull('medical_center_id');
                }
                $workSchedule = $workScheduleQuery->first();
            } else {
                $workScheduleQuery = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('day', $dayOfWeek);
                if ($clinicId) {
                    $workScheduleQuery->where('medical_center_id', $clinicId);
                } else {
                    $workScheduleQuery->whereNull('medical_center_id');
                }
                $workSchedule = $workScheduleQuery->first();
            }

            if (!$workSchedule || !$workSchedule->is_working) {

                return false;
            }

            $workHours = json_decode($workSchedule->work_hours, true);
            $appointmentSettings = json_decode($workSchedule->appointment_settings, true);
            $emergencyTimes = json_decode($workSchedule->emergency_times, true) ?? [];


        }

        // Validate work hours structure
        if (!is_array($workHours) || empty($workHours)) {

            return false;
        }

        // بررسی زمان‌های اورژانسی
        $appointmentTimeCarbon = Carbon::parse($appointmentTime, 'Asia/Tehran');
        foreach ($emergencyTimes as $emergency) {
            if (!isset($emergency['start']) || !isset($emergency['end'])) {

                continue;
            }

            try {
                $start = Carbon::parse($emergency['start'], 'Asia/Tehran');
                $end = Carbon::parse($emergency['end'], 'Asia/Tehran');
                if ($appointmentTimeCarbon->between($start, $end)) {

                    return false;
                }
            } catch (\Exception $e) {

                continue;
            }
        }

        // بررسی اینکه زمان نوبت در ساعات کاری پزشک قرار دارد یا خیر
        $isWithinWorkHours = false;
        $latestEndTime = null;

        foreach ($workHours as $slot) {
            if (!isset($slot['start']) || !isset($slot['end'])) {

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

                            return false;
                        }
                    }
                    break;
                }
            } catch (\Exception $e) {

                continue;
            }
        }

        if (!$isWithinWorkHours) {

            return false;
        }

        // شرط جدید: مقایسه زمان فعلی با زمان پایان ساعات کاری برای نوبت‌های امروز
        if ($latestEndTime && $appointmentDateCarbon->isToday()) {
            if ($currentTime->greaterThan($latestEndTime)) {

                return false;
            }
        }

        // گرفتن تنظیمات نوبت‌دهی
        $config = null;
        if ($serviceType === 'in_person') {
            $configQuery = DoctorAppointmentConfig::where('doctor_id', $doctor->id);
            if ($clinicId) {
                $configQuery->where('medical_center_id', $clinicId);
            }
            $config = $configQuery->first();
        } elseif (in_array($serviceType, ['phone', 'text', 'video'])) {
            $configQuery = DoctorCounselingConfig::where('doctor_id', $doctor->id);
            if ($clinicId) {
                $configQuery->where('medical_center_id', $clinicId);
            }
            $config = $configQuery->first();
        }

        if (!$config) {

            return false;
        }

        if (in_array($serviceType, ['phone', 'text', 'video'])) {
            $consultationTypes = $config->consultation_types ? json_decode($config->consultation_types, true) : [];
            $isCounselingAllowed = $config->online_consultation &&
                $config->auto_scheduling &&
                in_array($serviceType, $consultationTypes);

            if (!$isCounselingAllowed) {

                return false;
            }
        }

        // بررسی رزروهای قبلی با وضعیت‌های scheduled, pending_review و paid
        $duration = $this->getAppointmentDuration($appointmentSettings, $dayOfWeek, $config->appointment_duration ?? 15);

        $startTime = Carbon::parse("$appointmentDate $appointmentTime", 'Asia/Tehran');
        $endTime = $startTime->copy()->addMinutes($duration);



        $existingAppointment = null;
        if ($serviceType === 'in_person') {
            $existingAppointmentQuery = Appointment::where('doctor_id', $doctor->id)
                ->where('appointment_date', $appointmentDate)
                ->whereIn('status', ['scheduled', 'pending_review'])
                ->where('payment_status', 'paid')
                ->whereRaw("TIME_FORMAT(appointment_time, '%H:%i') = ?", [$appointmentTime]);

            $existingAppointment = $existingAppointmentQuery->first();

            if ($existingAppointment) {

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

                return false;
            }
        }

        // دیباگ: لاگ کردن در صورت عدم یافتن رزرو


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

    /**
     * استخراج مدت زمان نوبت از appointment_settings برای روز مشخص
     * پشتیبانی از فرمت‌های قدیمی و جدید
     */
    private function getAppointmentDuration($appointmentSettings, $dayOfWeek, $defaultDuration = 15)
    {
        if (empty($appointmentSettings)) {
            return $defaultDuration;
        }

        // New format: each item has 'day' field
        if (isset($appointmentSettings[0]['day'])) {
            foreach ($appointmentSettings as $setting) {
                if (isset($setting['day']) && $setting['day'] === $dayOfWeek) {
                    return $setting['appointment_duration'] ?? $defaultDuration;
                }
            }
        }
        // Old format: each item has 'days' array
        elseif (isset($appointmentSettings[0]['days'])) {
            foreach ($appointmentSettings as $setting) {
                if (isset($setting['days']) && is_array($setting['days']) && in_array($dayOfWeek, $setting['days'])) {
                    return $setting['appointment_duration'] ?? $defaultDuration;
                }
            }
        }
        // Fallback: try to get from first item
        elseif (isset($appointmentSettings[0]['appointment_duration'])) {
            return $appointmentSettings[0]['appointment_duration'];
        }

        return $defaultDuration;
    }
}
