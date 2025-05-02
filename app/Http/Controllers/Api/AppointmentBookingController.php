<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\InfrastructureFee;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ClinicDepositSetting;
use App\Models\CounselingAppointment;
use Illuminate\Support\Facades\Cache;
use App\Models\DoctorAppointmentConfig;
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
                'service_type.required'        => 'نوع سرویس الزامی است.',
                'service_type.in'              => 'نوع سرویس باید یکی از مقادیر in_person, phone, text, video باشد.',
            ]);

            $appointmentDate = $request->input('appointment_date');
            $appointmentTime = $request->input('appointment_time');
            $serviceType     = $request->input('service_type');

            // پیدا کردن پزشک
            $doctor = Doctor::where('id', $doctorId)
                ->where('status', true)
                ->with([
                    'specialty' => fn ($q) => $q->select('id', 'name'),
                    'province'  => fn ($q) => $q->select('id', 'name'),
                    'clinics'   => fn ($q) => $q->where('is_active', true)
                        ->with(['city' => fn ($q) => $q->select('id', 'name')])
                        ->select('id', 'doctor_id', 'address', 'province_id', 'city_id', 'is_main_clinic'),
                ])
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
            $mainClinic = $doctor->clinics->where('is_main_clinic', true)->first() ?? $doctor->clinics->first();
            $city       = $mainClinic && $mainClinic->city ? $mainClinic->city->name : 'نامشخص';

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
                    'specialty'    => $doctor->specialty?->name ?? 'نامشخص',
                    'avatar'       => $doctor->profile_photo_path ? asset('storage/' . $doctor->profile_photo_path) : '/default-avatar.png',
                    'location'     => [
                        'province' => $doctor->province?->name ?? 'نامشخص',
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
     * ثبت رزرو نوبت و هدایت به درگاه پرداخت
     */

    /**
 * ثبت رزرو نوبت و هدایت به درگاه پرداخت
 */
    public function bookAppointment(Request $request, $doctorId)
    {
        try {
            // اعتبارسنجی ورودی‌ها
            $validated = $request->validate([
                'appointment_date' => 'required|date_format:Y-m-d',
                'appointment_time' => 'required|date_format:H:i',
                'service_type'     => 'required|in:in_person,phone,text,video',
                'patient_type'     => 'required|in:self,relative',
                'user_id'          => 'required_if:patient_type,relative|nullable|integer|exists:users,id',
                'first_name'       => 'required_if:patient_type,relative|string|max:255',
                'last_name'        => 'required_if:patient_type,relative|string|max:255',
                'national_code'    => 'required_if:patient_type,relative|string|size:10|unique:users,national_code',
                'mobile'           => 'required_if:patient_type,relative|string|max:15|unique:users,mobile',
                'email'            => 'nullable|email|unique:users,email',
            ], [
                'appointment_date.required'    => 'تاریخ نوبت الزامی است.',
                'appointment_date.date_format' => 'فرمت تاریخ نوبت باید به شکل YYYY-MM-DD باشد (مثلاً 2025-03-22).',
                'appointment_time.required'    => 'زمان نوبت الزامی است.',
                'appointment_time.date_format' => 'فرمت زمان نوبت باید به شکل HH:MM باشد (مثلاً 14:30).',
                'service_type.required'        => 'نوع سرویس الزامی است.',
                'service_type.in'              => 'نوع سرویس باید یکی از مقادیر in_person, phone, text, video باشد.',
                'patient_type.required'        => 'نوع بیمار الزامی است.',
                'patient_type.in'              => 'نوع بیمار باید یکی از مقادیر self یا relative باشد.',
                'user_id.required_if'          => 'آیدی کاربر الزامی است وقتی نوع بیمار relative باشد.',
                'user_id.exists'               => 'کاربر با این آیدی وجود ندارد.',
                'first_name.required_if'       => 'نام بیمار الزامی است وقتی نوع بیمار relative باشد و کاربر جدیدی ایجاد می‌کنید.',
                'first_name.max'               => 'نام بیمار نمی‌تواند بیشتر از 255 کاراکتر باشد.',
                'last_name.required_if'        => 'نام خانوادگی بیمار الزامی است وقتی نوع بیمار relative باشد و کاربر جدیدی ایجاد می‌کنید.',
                'last_name.max'                => 'نام خانوادگی بیمار نمی‌تواند بیشتر از 255 کاراکتر باشد.',
                'national_code.required_if'    => 'کدملی بیمار الزامی است وقتی نوع بیمار relative باشد و کاربر جدیدی ایجاد می‌کنید.',
                'national_code.size'           => 'کدملی بیمار باید دقیقاً 10 رقم باشد.',
                'national_code.unique'         => 'این کدملی قبلاً ثبت شده است.',
                'mobile.required_if'           => 'شماره موبایل بیمار الزامی است وقتی نوع بیمار relative باشد و کاربر جدیدی ایجاد می‌کنید.',
                'mobile.max'                   => 'شماره موبایل بیمار نمی‌تواند بیشتر از 15 رقم باشد.',
                'mobile.unique'                => 'این شماره موبایل قبلاً ثبت شده است.',
                'email.email'                  => 'ایمیل باید فرمت معتبر داشته باشد.',
                'email.unique'                 => 'این ایمیل قبلاً ثبت شده است.',
            ]);

            $appointmentDate = $request->input('appointment_date');
            $appointmentTime = $request->input('appointment_time');
            $serviceType     = $request->input('service_type');
            $patientType     = $request->input('patient_type');

            // گرفتن کاربر احراز هویت‌شده
            $authenticatedUser = $request->attributes->get('user');
            if (! $authenticatedUser) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'لطفاً ابتدا وارد حساب کاربری خود شوید.',
                    'data'    => null,
                ], 401);
            }

            // پیدا کردن پزشک
            $doctor = Doctor::where('id', $doctorId)
                ->where('status', true)
                ->with(['clinics' => fn ($q) => $q->where('is_active', true)])
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
                // پیدا کردن روز هفته برای پیام خطای دقیق‌تر
                $appointmentDateCarbon = Carbon::parse($appointmentDate, 'Asia/Tehran');
                $dayOfWeek = strtolower($appointmentDateCarbon->format('l'));

                // پیدا کردن کلینیک اصلی
                $mainClinic = $doctor->clinics->where('is_main_clinic', true)->first() ?? $doctor->clinics->first();

                // بررسی ساعات کاری
                $workSchedule = null;
                if ($serviceType === 'in_person') {
                    $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                        ->where('clinic_id', $mainClinic->id)
                        ->where('day', $dayOfWeek)
                        ->first();

                    if (! $workSchedule) {
                        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                            ->whereNull('clinic_id')
                            ->where('day', $dayOfWeek)
                            ->first();
                    }
                } else {
                    $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                        ->where('clinic_id', $mainClinic->id)
                        ->where('day', $dayOfWeek)
                        ->first();

                    if (! $workSchedule) {
                        $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                            ->whereNull('clinic_id')
                            ->where('day', $dayOfWeek)
                            ->first();
                    }
                }

                if (! $workSchedule || ! $workSchedule->is_working) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "پزشک در روز {$this->getPersianDay($dayOfWeek)} کار نمی‌کند.",
                        'data'    => null,
                    ], 400);
                }

                // بررسی زمان باز شدن نوبت‌ها
                $appointmentSettings = $workSchedule->appointment_settings;
                if (is_string($appointmentSettings)) {
                    $appointmentSettings = json_decode($appointmentSettings, true);
                }

                if ($appointmentSettings && is_array($appointmentSettings) && !empty($appointmentSettings)) {
                    $bookingOpening = $appointmentSettings[0];

                    $currentDateTime = Carbon::now('Asia/Tehran');
                    $currentDayOfWeek = strtolower($currentDateTime->format('l'));
                    $currentTime = $currentDateTime->format('H:i');
                    $currentTimeCarbon = Carbon::parse($currentTime, 'Asia/Tehran');

                    if (!in_array($currentDayOfWeek, $bookingOpening['days'])) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => "نوبت‌ها فقط در روزهای " . implode('، ', array_map([$this, 'getPersianDay'], $bookingOpening['days'])) . " قابل رزرو هستند.",
                            'data'    => null,
                        ], 400);
                    }

                    $bookingStart = Carbon::parse($bookingOpening['start_time'], 'Asia/Tehran');
                    $bookingEnd = Carbon::parse($bookingOpening['end_time'], 'Asia/Tehran');

                    if (!$currentTimeCarbon->between($bookingStart, $bookingEnd)) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => "نوبت‌ها فقط در بازه {$bookingOpening['start_time']} تا {$bookingOpening['end_time']} قابل رزرو هستند.",
                            'data'    => null,
                        ], 400);
                    }
                }

                // بررسی ساعات کاری
                $appointmentTimeCarbon = Carbon::parse($appointmentTime, 'Asia/Tehran');
                $workHours = $workSchedule->work_hours;
                if (is_string($workHours)) {
                    $workHours = json_decode($workHours, true);
                }

                $isWithinWorkHours = false;
                if ($workHours && is_array($workHours)) {
                    foreach ($workHours as $slot) {
                        $start = Carbon::parse($slot['start'], 'Asia/Tehran');
                        $end = Carbon::parse($slot['end'], 'Asia/Tehran');

                        if ($appointmentTimeCarbon->between($start, $end)) {
                            $isWithinWorkHours = true;
                            break;
                        }
                    }
                }

                if (!$isWithinWorkHours) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "زمان انتخاب‌شده ({$appointmentTime}) خارج از ساعات کاری پزشک است.",
                        'data'    => null,
                    ], 400);
                }

                // بررسی تعداد حداکثر نوبت‌ها
                foreach ($workHours as $slot) {
                    if ($appointmentTimeCarbon->between(
                        Carbon::parse($slot['start'], 'Asia/Tehran'),
                        Carbon::parse($slot['end'], 'Asia/Tehran')
                    )) {
                        $maxAppointments = $slot['max_appointments'] ?? null;
                        if ($maxAppointments !== null) {
                            $existingAppointments = $serviceType === 'in_person'
                                ? Appointment::where('doctor_id', $doctor->id)
                                    ->where('clinic_id', $mainClinic->id)
                                    ->where('appointment_date', $appointmentDate)
                                    ->where('appointment_time', '>=', $slot['start'])
                                    ->where('appointment_time', '<', $slot['end'])
                                    ->where('status', 'scheduled')
                                    ->count()
                                : CounselingAppointment::where('doctor_id', $doctor->id)
                                    ->where('clinic_id', $mainClinic->id)
                                    ->where('appointment_date', $appointmentDate)
                                    ->where('appointment_time', '>=', $slot['start'])
                                    ->where('appointment_time', '<', $slot['end'])
                                    ->where('status', 'scheduled')
                                    ->count();

                            if ($existingAppointments >= $maxAppointments) {
                                return response()->json([
                                    'status'  => 'error',
                                    'message' => "حداکثر تعداد نوبت‌ها برای این بازه زمانی پر شده است.",
                                    'data'    => null,
                                ], 400);
                            }
                        }
                        break;
                    }
                }

                // اگر به اینجا رسید، مشکل از رزرو قبلی است
                return response()->json([
                    'status'  => 'error',
                    'message' => 'این نوبت قبلاً رزرو شده است.',
                    'data'    => null,
                ], 400);
            }

            // پیدا کردن یا ایجاد بیمار
            $patient = null;
            if ($patientType === 'self') {
                $patient = $authenticatedUser;
            } else {
                if ($request->has('user_id')) {
                    $patient = User::find($request->input('user_id'));
                    if ($patient && $patient->created_by !== $authenticatedUser->id) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'شما اجازه دسترسی به این کاربر را ندارید.',
                            'data'    => null,
                        ], 403);
                    }
                } else {
                    $patient = User::create([
                        'first_name'    => $request->input('first_name'),
                        'last_name'     => $request->input('last_name'),
                        'national_code' => $request->input('national_code'),
                        'mobile'        => $request->input('mobile'),
                        'email'         => $request->input('email'),
                        'created_by'    => $authenticatedUser->id,
                        'user_type'     => 0,
                        'status'        => 1,
                    ]);
                }
            }

            if (! $patient) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'کاربر (بیمار) یافت نشد یا ایجاد نشد.',
                    'data'    => null,
                ], 400);
            }

            ros://www.youtube.com/watch?v=ZgqW0d6y2b8&t=1s

             // اطلاعات کلینیک
             $mainClinic = $doctor->clinics->where('is_main_clinic', true)->first() ?? $doctor->clinics->first();

            // محاسبه هزینه‌ها
            $depositSetting = ClinicDepositSetting::where('doctor_id', $doctor->id)
                ->where('clinic_id', $mainClinic->id)
                ->where('is_active', true)
                ->first();

            $depositAmount = $depositSetting ? $depositSetting->deposit_amount : 0;
            $infrastructureFee = InfrastructureFee::where('appointment_type', $serviceType)
                ->where('is_active', true)
                ->first()->fee ?? 0;
            $totalFee = $depositAmount + $infrastructureFee;

            // تولید کد رهگیری (فقط عدد)
            $trackingCode = null;
            $maxAttempts = 10; // حداکثر تعداد تلاش برای پیدا کردن کد یکتا
            for ($i = 0; $i < $maxAttempts; $i++) {
                $trackingCode = mt_rand(10000000, 99999999); // تولید عدد 8 رقمی
                $exists = $serviceType === 'in_person'
                    ? Appointment::where('tracking_code', $trackingCode)->exists()
                    : CounselingAppointment::where('tracking_code', $trackingCode)->exists();
                if (! $exists) {
                    break; // کد یکتاست، از حلقه خارج شو
                }
                if ($i === $maxAttempts - 1) {
                    throw new \Exception('نمی‌توان کد رهگیری یکتا تولید کرد. لطفاً دوباره تلاش کنید.');
                }
            }

            // ثبت نوبت
            $appointmentData = [
                'doctor_id'        => $doctor->id,
                'patient_id'       => $patient->id,
                'clinic_id'        => $mainClinic->id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'fee'              => $totalFee,
                'tracking_code'    => $trackingCode,
                'reserved_at'      => now(),
                'status'           => 'scheduled',
                'payment_status'   => 'pending',
            ];

            if ($serviceType === 'in_person') {
                $appointment = Appointment::create($appointmentData);
            } else {
                $appointmentData['appointment_type'] = $serviceType;
                $appointment = CounselingAppointment::create($appointmentData);
            }

            // اطلاعات اضافی برای تراکنش
            $meta = [
                'appointment_id'     => $appointment->id,
                'appointment_type'   => $serviceType === 'in_person' ? 'in_person' : 'counseling',
                'infrastructure_fee' => $infrastructureFee, // برای محاسبه در صفحه نتیجه
            ];

            // دریافت URL درگاه پرداخت
            $redirection = $this->paymentService->pay($totalFee, route('payment.callback'), $meta);

            // برگرداندن URL درگاه به صورت JSON
            return response()->json([
                'status'        => 'success',
                'message'       => 'نوبت با موفقیت ثبت شد. لطفاً به درگاه پرداخت هدایت شوید.',
                'payment_url'   => $redirection->getTargetUrl(), // URL درگاه پرداخت
                'tracking_code' => $trackingCode,                // کد رهگیری (فقط عدد)
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای اعتبارسنجی ورودی‌ها',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('BookAppointment - Error: ' . $e->getMessage(), [
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
            // چک کردن وجود Authority
            $authority = $request->input('Authority');
            if (! $authority) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'پارامتر Authority در درخواست وجود ندارد.',
                    'data'    => null,
                ], 400);
            }

            // پیدا کردن تراکنش
            $transaction = Transaction::where('transaction_id', $authority)->first();
            if (! $transaction) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'تراکنش با این Authority یافت نشد.',
                    'data'    => null,
                ], 404);
            }

            // تأیید پرداخت از طریق سرویس پرداخت (فقط اگه وضعیت pending باشه)
            $isPaymentSuccessful = false;
            if ($transaction->status === 'pending') {
                $verifiedTransaction = $this->paymentService->verify();
                $isPaymentSuccessful = (bool) $verifiedTransaction;
                if ($verifiedTransaction) {
                    $transaction = $verifiedTransaction;
                }
            } else {
                $isPaymentSuccessful = $transaction->status === 'success'; // تغییر به success
            }

            // آپدیت وضعیت نوبت
            if ($isPaymentSuccessful) {
                // پرداخت موفق بوده، نوبت رو آپدیت می‌کنیم
                $meta            = $transaction->meta;
                $appointmentId   = $meta['appointment_id'] ?? null;
                $appointmentType = $meta['appointment_type'] ?? null;

                if ($appointmentId && $appointmentType) {
                    if ($appointmentType === 'in_person') {
                        $appointment = Appointment::find($appointmentId);
                    } else {
                        $appointment = CounselingAppointment::find($appointmentId);
                    }

                    if ($appointment) {
                        $validPaymentStatuses = ['pending', 'paid', 'unpaid'];
                        $validStatuses        = [
                            'scheduled', 'cancelled', 'attended', 'missed', 'pending_review',
                            'call_answered', 'call_completed', 'video_started', 'video_completed',
                            'text_completed', 'refunded',
                        ];

                        $paymentStatus = 'paid';
                        $status        = 'pending_review';

                        if (! in_array($paymentStatus, $validPaymentStatuses)) {
                            throw new \Exception("Invalid payment_status: $paymentStatus");
                        }

                        if (! in_array($status, $validStatuses)) {
                            throw new \Exception("Invalid status: $status");
                        }

                        $appointment->update([
                            'payment_status' => $paymentStatus,
                            'status'         => $status,
                        ]);
                    }
                }
            } else {
                // پرداخت ناموفق بوده، می‌تونیم نوبت رو لغو کنیم یا فقط payment_status رو آپدیت کنیم
                $meta            = $transaction->meta;
                $appointmentId   = $meta['appointment_id'] ?? null;
                $appointmentType = $meta['appointment_type'] ?? null;

                if ($appointmentId && $appointmentType) {
                    if ($appointmentType === 'in_person') {
                        $appointment = Appointment::find($appointmentId);
                    } else {
                        $appointment = CounselingAppointment::find($appointmentId);
                    }

                    if ($appointment) {
                        $validPaymentStatuses = ['pending', 'paid', 'unpaid'];
                        $validStatuses        = [
                            'scheduled', 'cancelled', 'attended', 'missed', 'pending_review',
                            'call_answered', 'call_completed', 'video_started', 'video_completed',
                            'text_completed', 'refunded',
                        ];

                        $paymentStatus = 'unpaid';
                        $status        = 'cancelled';

                        if (! in_array($paymentStatus, $validPaymentStatuses)) {
                            throw new \Exception("Invalid payment_status: $paymentStatus");
                        }

                        if (! in_array($status, $validStatuses)) {
                            throw new \Exception("Invalid status: $status");
                        }

                        $appointment->update([
                            'payment_status' => $paymentStatus,
                            'status'         => $status,
                        ]);
                    }
                }
            }

            // آماده‌سازی داده‌ها برای پاسخ
            $data = [
                'status'      => $isPaymentSuccessful ? 'success' : 'failed',
                'message'     => $isPaymentSuccessful ? 'پرداخت با موفقیت انجام شد.' : 'پرداخت ناموفق بود.',
                'transaction' => $transaction ? [
                    'id'             => $transaction->id,
                    'user_id'        => $transaction->user_id,
                    'amount'         => $transaction->amount,
                    'gateway'        => $transaction->gateway,
                    'status'         => $transaction->status,
                    'transaction_id' => $transaction->transaction_id,
                    'meta'           => $transaction->meta,
                    'created_at'     => $transaction->created_at->toDateTimeString(),
                    'updated_at'     => $transaction->updated_at->toDateTimeString(),
                ] : null,
            ];

            // برگرداندن نتیجه به صورت JSON
            return response()->json($data, $isPaymentSuccessful ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('PaymentResult - Error: ' . $e->getMessage(), [
                'request'   => $request->all(),
                'exception' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور در پردازش نتیجه پرداخت',
                'data'    => null,
            ], 500);
        }
    }
    /**
     * بررسی در دسترس بودن نوبت
     */
    /**
     * بررسی در دسترس بودن نوبت
     */
    private function checkSlotAvailability($doctor, $appointmentDate, $appointmentTime, $serviceType)
    {
        // پیدا کردن روز هفته از تاریخ نوبت
        $appointmentDateCarbon = Carbon::parse($appointmentDate, 'Asia/Tehran');
        $dayOfWeek = strtolower($appointmentDateCarbon->format('l')); // مثلاً 'thursday'

        // پیدا کردن کلینیک اصلی پزشک
        $mainClinic = $doctor->clinics->where('is_main_clinic', true)->first() ?? $doctor->clinics->first();
        if (! $mainClinic) {
            Log::warning("CheckSlotAvailability - No clinic found for doctor {$doctor->id}");
            return false;
        }

        // بررسی ساعات کاری پزشک با استفاده از مدل‌ها
        $workSchedule = null;
        if ($serviceType === 'in_person') {
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('clinic_id', $mainClinic->id)
                ->where('day', $dayOfWeek)
                ->first();

            if (! $workSchedule) {
                $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                    ->whereNull('clinic_id')
                    ->where('day', $dayOfWeek)
                    ->first();
            }
        } else {
            $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                ->where('clinic_id', $mainClinic->id)
                ->where('day', $dayOfWeek)
                ->first();

            if (! $workSchedule) {
                $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                    ->whereNull('clinic_id')
                    ->where('day', $dayOfWeek)
                    ->first();
            }
        }

        if (! $workSchedule || ! $workSchedule->is_working) {
            Log::warning("CheckSlotAvailability - Doctor {$doctor->id} is not working on {$dayOfWeek} for service type {$serviceType}");
            return false;
        }

        // بررسی زمان باز شدن نوبت‌ها (appointment_settings)
        $appointmentSettings = $workSchedule->appointment_settings;
        if (is_string($appointmentSettings)) {
            $appointmentSettings = json_decode($appointmentSettings, true);
            Log::debug("CheckSlotAvailability - appointment_settings after json_decode: ", ['appointment_settings' => $appointmentSettings]);
        }

        if ($appointmentSettings && is_array($appointmentSettings) && !empty($appointmentSettings)) {
            $bookingOpening = $appointmentSettings[0];

            $currentDateTime = Carbon::now('Asia/Tehran');
            $currentDayOfWeek = strtolower($currentDateTime->format('l'));
            $currentTime = $currentDateTime->format('H:i');

            // بررسی اینکه روز جاری در لیست روزهای مجاز برای رزرو هست یا نه
            if (!in_array($currentDayOfWeek, $bookingOpening['days'])) {
                Log::warning("CheckSlotAvailability - Booking is not open today for doctor {$doctor->id}. Current day: {$currentDayOfWeek}, Booking days: " . json_encode($bookingOpening['days']));
                return false;
            }

            $currentTimeCarbon = Carbon::parse($currentTime, 'Asia/Tehran');
            $bookingStart = Carbon::parse($bookingOpening['start_time'], 'Asia/Tehran');
            $bookingEnd = Carbon::parse($bookingOpening['end_time'], 'Asia/Tehran');

            if (!$currentTimeCarbon->between($bookingStart, $bookingEnd)) {
                Log::warning("CheckSlotAvailability - Current time {$currentTime} is outside booking opening hours for doctor {$doctor->id}. Booking hours: {$bookingOpening['start_time']} to {$bookingOpening['end_time']}");
                return false;
            }
        }

        // بررسی اینکه زمان نوبت در ساعات کاری پزشک قرار دارد یا خیر
        $workHours = $workSchedule->work_hours;
        Log::debug("CheckSlotAvailability - work_hours for doctor {$doctor->id} on {$dayOfWeek}: ", ['work_hours' => $workHours]);

        if (is_string($workHours)) {
            $workHours = json_decode($workHours, true);
            Log::debug("CheckSlotAvailability - work_hours after json_decode: ", ['work_hours' => $workHours]);
        }

        $isWithinWorkHours = false;



        if ($workHours && is_array($workHours)) {
            foreach ($workHours as $slot) {
                Log::debug("CheckSlotAvailability - Processing slot: ", ['slot' => $slot]);
                $start = Carbon::parse("{$appointmentDate} {$slot['start']}", 'Asia/Tehran');
                $end = Carbon::parse("{$appointmentDate} {$slot['end']}", 'Asia/Tehran');
                $appointmentTimeFull = Carbon::parse("{$appointmentDate} {$appointmentTime}", 'Asia/Tehran');

                Log::debug("CheckSlotAvailability - Start: {$start->toDateTimeString()}, End: {$end->toDateTimeString()}, Appointment Time: {$appointmentTimeFull->toDateTimeString()}");

                // استفاده از greaterThanOrEqualTo و lessThanOrEqualTo
                if ($appointmentTimeFull->greaterThanOrEqualTo($start) && $appointmentTimeFull->lessThanOrEqualTo($end)) {
                    $isWithinWorkHours = true;

                    // بررسی تعداد حداکثر نوبت‌ها در این اسلات
                    $maxAppointments = $slot['max_appointments'] ?? null;
                    if ($maxAppointments !== null) {
                        $existingAppointments = $serviceType === 'in_person'
                            ? Appointment::where('doctor_id', $doctor->id)
                                ->where('clinic_id', $mainClinic->id)
                                ->where('appointment_date', $appointmentDate)
                                ->where('appointment_time', '>=', $slot['start'])
                                ->where('appointment_time', '<=', $slot['end']) // شامل زمان پایان
                                ->where('status', 'scheduled')
                                ->count()
                            : CounselingAppointment::where('doctor_id', $doctor->id)
                                ->where('clinic_id', $mainClinic->id)
                                ->where('appointment_date', $appointmentDate)
                                ->where('appointment_time', '>=', $slot['start'])
                                ->where('appointment_time', '<=', $slot['end']) // شامل زمان پایان
                                ->where('status', 'scheduled')
                                ->count();

                        if ($existingAppointments >= $maxAppointments) {
                            Log::warning("CheckSlotAvailability - Max appointments reached for doctor {$doctor->id} on {$appointmentDate} in slot {$slot['start']}-{$slot['end']}");
                            return false;
                        }
                    }
                    break;
                }
            }
        }



        if (! $isWithinWorkHours) {
            Log::warning("CheckSlotAvailability - Appointment time {$appointmentTime} is outside working hours for doctor {$doctor->id} on {$dayOfWeek}");
            return false;
        }

        // گرفتن تنظیمات نوبت‌دهی
        $config = null;
        if ($serviceType === 'in_person') {
            $config = DoctorAppointmentConfig::where('doctor_id', $doctor->id)
                ->where('clinic_id', $mainClinic->id)
                ->first();
        } elseif (in_array($serviceType, ['phone', 'text', 'video'])) {
            $config = DoctorAppointmentConfig::where('doctor_id', $doctor->id)
                ->where('clinic_id', $mainClinic->id)
                ->first();
        }

        if (! $config) {
            Log::warning("CheckSlotAvailability - Config not found for doctor {$doctor->id} and service type {$serviceType}");
            return false;
        }

        if (in_array($serviceType, ['phone', 'text', 'video'])) {
            $consultationTypes = $config->consultation_types ? json_decode($config->consultation_types, true) : [];
            $isCounselingAllowed = $config->online_consultation &&
                $config->auto_scheduling &&
                in_array($serviceType, $consultationTypes);

            if (! $isCounselingAllowed) {
                Log::warning("CheckSlotAvailability - Counseling not allowed for doctor {$doctor->id} and service type {$serviceType}", [
                    'online_consultation' => $config->online_consultation,
                    'auto_scheduling' => $config->auto_scheduling,
                    'consultation_types' => $consultationTypes,
                ]);
                return false;
            }
        }

        // استفاده از appointment_duration از appointment_settings یا config
        $duration = $appointmentSettings[0]['appointment_duration'] ?? ($config->appointment_duration ?? 15);

        $startTime = Carbon::parse("$appointmentDate $appointmentTime", 'Asia/Tehran');
        $endTime = $startTime->copy()->addMinutes($duration);

        // بررسی رزروهای قبلی
        $existingAppointment = null;
        if ($serviceType === 'in_person') {
            $existingAppointment = Appointment::where('doctor_id', $doctor->id)
                ->where('clinic_id', $mainClinic->id)
                ->where('appointment_date', $appointmentDate)
                ->where('status', 'scheduled')
                ->where(function ($q) use ($startTime, $endTime, $duration) {
                    $q->whereBetween('appointment_time', [
                        $startTime->copy()->subMinutes($duration)->format('H:i'),
                        $endTime->format('H:i'),
                    ]);
                })
                ->exists();
        } else {
            $existingAppointment = CounselingAppointment::where('doctor_id', $doctor->id)
                ->where('clinic_id', $mainClinic->id)
                ->where('appointment_date', $appointmentDate)
                ->where('status', 'scheduled')
                ->where(function ($q) use ($startTime, $endTime, $duration) {
                    $q->whereBetween('appointment_time', [
                        $startTime->copy()->subMinutes($duration)->format('H:i'),
                        $endTime->format('H:i'),
                    ]);
                })
                ->exists();
        }

        if ($existingAppointment) {
            Log::warning("CheckSlotAvailability - Slot already taken for doctor {$doctor->id} on {$appointmentDate} at {$appointmentTime} for service type {$serviceType}");
            return false;
        }

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
