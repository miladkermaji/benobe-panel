<?php

namespace App\Http\Controllers\Mc\Panel\Turn\Schedule\MoshavereWaiting;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use App\Models\CounselingAppointment;
use App\Models\DoctorCounselingConfig;
use App\Http\Controllers\Mc\Controller;
use App\Models\CounselingDailySchedule;
use App\Models\DoctorCounselingWorkSchedule;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class MoshavereWaitingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $today    = Carbon::today();
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id; // گرفتن ID پزشک لاگین‌شده
        // تعداد بیماران امروز فقط برای این پزشک
        $totalPatientsToday = CounselingAppointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->count();
        // بیماران ویزیت شده فقط برای این پزشک
        $visitedPatients = CounselingAppointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('attendance_status', 'attended')
            ->count();
        // بیماران باقی‌مانده فقط برای این پزشک
        $remainingPatients = $totalPatientsToday - $visitedPatients;



        return view("mc.panel.turn.schedule.moshavere_waiting.index", compact('totalPatientsToday', 'visitedPatients', 'remainingPatients'));
    }
    public function searchAppointments(Request $request)
    {
        $request->validate([
            'date'             => 'nullable|string',
            'user_mobile'      => 'nullable|string',
            'user_name'        => 'nullable|string',
            'user_national_id' => 'nullable|string',
            'selectedClinicId' => 'nullable|string',
        ]);

        $date             = $request->input('date');
        $userMobile       = $request->input('user_mobile');
        $userName         = $request->input('user_name');
        $userNationalId   = $request->input('user_national_id');
        $selectedClinicId = $request->input('selectedClinicId');
        $doctorId         = auth('doctor')->user()->id;

        $gregorianDate = null;
        if ($date) {
            $dateParts = explode(' ', str_replace('  ', ' ', $date));
            if (count($dateParts) === 3) {
                list($day, $monthName, $year) = $dateParts;
                $monthNames                   = [
                    'فروردین'  => 1,
                    'اردیبهشت' => 2,
                    'خرداد'    => 3,
                    'تیر'      => 4,
                    'مرداد'    => 5,
                    'شهریور'   => 6,
                    'مهر'      => 7,
                    'آبان'     => 8,
                    'آذر'      => 9,
                    'دی'       => 10,
                    'بهمن'     => 11,
                    'اسفند'    => 12,
                ];
                if (isset($monthNames[$monthName])) {
                    $month = $monthNames[$monthName];
                    // استفاده از Jalalian به جای Verta
                    $gregorianDate = Jalalian::fromFormat('Y/m/d', "$year/$month/$day")->toCarbon()->format('Y-m-d');
                }
            }
        }

        $appointments = CounselingAppointment::with('patient')
            ->where('doctor_id', $doctorId)
            ->when($selectedClinicId === 'default', function ($query) {
                return $query->whereNull('medical_center_id');
            })
            ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                return $query->where('medical_center_id', $selectedClinicId);
            })
            ->when($gregorianDate, function ($query) use ($gregorianDate) {
                return $query->where('appointment_date', $gregorianDate);
            })
            ->when($userMobile, function ($query, $userMobile) {
                return $query->whereHas('patient', function ($query) use ($userMobile) {
                    $query->where('mobile', 'like', '%' . $userMobile . '%');
                });
            })
            ->when($userName, function ($query, $userName) {
                return $query->whereHas('patient', function ($query) use ($userName) {
                    $query->where(function ($query) use ($userName) {
                        $query->where('first_name', 'like', '%' . $userName . '%')
                            ->orWhere('last_name', 'like', '%' . $userName . '%');
                    });
                });
            })
            ->when($userNationalId, function ($query, $userNationalId) {
                return $query->whereHas('patient', function ($query) use ($userNationalId) {
                    $query->where('national_code', 'like', '%' . $userNationalId . '%');
                });
            })
            ->paginate(10);

        return response()->json([
            'data'         => $appointments->items(),
            'current_page' => $appointments->currentPage(),
            'last_page'    => $appointments->lastPage(),
            'per_page'     => $appointments->perPage(),
            'total'        => $appointments->total(),
        ]);
    }
    public function getAppointmentsByDateSpecial(Request $request)
    {
        $date = $request->input('date');
        $selectedClinicId = $request->input('selectedClinicId');
        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();

        $appointments = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled') // فقط نوبت‌های فعال
            ->whereNull('deleted_at')
            ->when($selectedClinicId === 'default', function ($query) {
                $query->whereNull('medical_center_id');
            })
            ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                $query->where('medical_center_id', $selectedClinicId);
            })
            ->get();

        return response()->json([
            'status' => true,
            'data' => $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'appointment_date' => $appointment->appointment_date,
                    'status' => $appointment->status,
                ];
            }),
        ]);
    }
    public function cancelAppointments(Request $request)
    {
        $appointmentIds = $request->input('appointment_ids');
        $date = $request->input('date');
        $selectedClinicId = $request->input('selectedClinicId');

        if (empty($appointmentIds)) {
            return response()->json([
                'status' => false,
                'message' => 'هیچ نوبتی انتخاب نشده است'
            ], 400);
        }

        $gregorianDate = $date;
        $jalaliDate = $date;
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date)) {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->toDateString();
            $jalaliDate = $date;
        }

        // کوئری با در نظر گرفتن نوبت‌های حذف‌شده
        $query = CounselingAppointment::withTrashed()
            ->whereIn('id', $appointmentIds)
            ->where('appointment_date', $gregorianDate);

        if ($selectedClinicId === 'default') {
            $query->whereNull('medical_center_id');
        } else {
            $query->where('medical_center_id', $selectedClinicId);
        }

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'هیچ نوبتی با این مشخصات یافت نشد'
            ], 404);
        }

        // چک کردن وضعیت نوبت‌ها
        $allCancelledOrAttended = $appointments->every(function ($appointment) {
            return $appointment->status === 'cancelled' || $appointment->status === 'attended';
        });

        if ($allCancelledOrAttended) {
            return response()->json([
                'status' => false,
                'message' => 'نوبت‌ها یا قبلاً لغو شده‌اند یا ویزیت شده‌اند و قابل لغو نیستند'
            ], 400);
        }

        $recipients = [];
        $newlyCancelled = false;

        foreach ($appointments as $appointment) {
            if ($appointment->status !== 'cancelled' && $appointment->status !== 'attended') { // فقط اگه لغو یا ویزیت نشده باشه
                if ($appointment->patient && $appointment->patient->mobile) {
                    $recipients[] = $appointment->patient->mobile;
                }
                $appointment->status = 'cancelled';
                $appointment->save();
                $appointment->delete();
                $newlyCancelled = true;
            }
        }

        if ($newlyCancelled && !empty($recipients)) {
            $message = "کاربر گرامی، نوبت شما برای تاریخ {$jalaliDate} لغو شد.";

            $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
            $templateId = ($gatewayName === 'pishgamrayan') ? 100286 : null;

            SendSmsNotificationJob::dispatch(
                $message,
                $recipients,
                $templateId,
                [$jalaliDate]
            )->delay(now()->addSeconds(5));
        }

        return response()->json([
            'status' => true,
            'message' => $newlyCancelled ? 'نوبت‌ها با موفقیت لغو شدند .' : 'برخی نوبت‌ها قبلاً لغو یا ویزیت شده بودند و تغییری اعمال نشد.',
            'total_recipients' => count($recipients),
        ]);
    }
    public function getDefaultSchedule(Request $request)
    {
        $doctorId         = Auth::guard('doctor')->user()->id;
        $date             = $request->date;
        $selectedDate     = Carbon::parse($request->date); // تاریخ دریافتی در فرمت میلادی
        $selectedClinicId = $request->input('selectedClinicId');
        $dayOfWeek        = strtolower($selectedDate->format('l')); // دریافت نام روز (مثلاً saturday, sunday, ...)

        // Check for special schedule
        $specialScheduleQuery = CounselingDailySchedule::where('date', $date);
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $specialScheduleQuery->where('medical_center_id', $selectedClinicId);
        }
        $specialSchedule = $specialScheduleQuery->first();

        // بررسی وجود ساعات کاری برای تاریخ مشخص در جدول ویژه
        if ($specialSchedule) {
            return response()->json([
                'status'     => true,
                'work_hours' => json_decode($specialSchedule->work_hours, true),
            ]);
        }

        // دریافت ساعات کاری دکتر برای این روز خاص
        $workScheduleQuery = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayOfWeek);
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $workScheduleQuery->where('medical_center_id', $selectedClinicId);
        }
        $workSchedule = $workScheduleQuery->first();

        if ($workSchedule) {
            return response()->json([
                'status'     => true,
                'work_hours' => json_decode($workSchedule->work_hours, true) ?? [],
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'هیچ ساعات کاری برای این روز یافت نشد.',
        ]);
    }
    public function getNextAvailableDate(Request $request)
    {
        // دریافت شناسه پزشک یا منشی
        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId'); // کلینیک انتخابی

        // دریافت تعطیلی‌های پزشک با توجه به کلینیک
        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)
            ->when($selectedClinicId === 'default', function ($query) use ($doctorId) {
                // در صورت 'default' فقط تعطیلی‌های بدون کلینیک (medical_center_id = NULL)
                $query->whereNull('medical_center_id');
            })
            ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                // اگر کلینیک خاص ارسال شود
                $query->where('medical_center_id', $selectedClinicId);
            });

        $holidays     = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);

        // تعداد روزهای قابل بررسی برای نوبت خالی
        $today       = Carbon::now()->startOfDay();
        $daysToCheck = DoctorCounselingConfig::where('doctor_id', $doctorId)->value('calendar_days') ?? 30;

        // تولید لیست تاریخ‌ها برای بررسی
        $datesToCheck = collect();
        for ($i = 1; $i <= $daysToCheck; $i++) {
            $date = $today->copy()->addDays($i)->format('Y-m-d');
            $datesToCheck->push($date);
        }

        // پیدا کردن اولین تاریخ خالی
        $nextAvailableDate = $datesToCheck->first(function ($date) use ($doctorId, $holidayDates, $selectedClinicId) {
            // بررسی عدم وجود در لیست تعطیلی‌ها
            if (in_array($date, $holidayDates)) {
                return false;
            }

            // بررسی عدم وجود نوبت در تاریخ مورد نظر
            $appointmentQuery = CounselingAppointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->when($selectedClinicId === 'default', function ($query) {
                    // فقط نوبت‌های بدون کلینیک (medical_center_id = NULL) بازگردانده شود
                    $query->whereNull('medical_center_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    // نوبت‌های کلینیک مشخص‌شده بازگردانده شود
                    $query->where('medical_center_id', $selectedClinicId);
                });

            return ! $appointmentQuery->exists();
        });

        return response()->json([
            'status' => $nextAvailableDate ? true : false,
            'date'   => $nextAvailableDate ?? 'هیچ نوبت خالی یافت نشد.',
        ]);
    }
    public function updateFirstAvailableAppointment(Request $request)
    {
        // اعتبارسنجی ورودی
        $validated = $request->validate([
            'old_date'         => 'required|date',   // تاریخ قبلی نوبت
            'new_date'         => 'required|date',   // تاریخ جدید که باید جایگزین شود
            'selectedClinicId' => 'nullable|string', // اضافه کردن فیلتر selectedClinicId
        ]);

        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId'); // دریافت selectedClinicId از درخواست

        try {
            // پیدا کردن تمام نوبت‌های اولین تاریخ ثبت‌شده با فیلتر کلینیک
            $appointmentsQuery = CounselingAppointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $validated['old_date'])
                ->when($selectedClinicId === 'default', function ($query) {
                    // اگر selectedClinicId برابر با 'default' باشد، فقط نوبت‌های بدون کلینیک را در نظر بگیرد
                    $query->whereNull('medical_center_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    // در غیر این صورت، نوبت‌های مربوط به کلینیک مشخص‌شده را بررسی کند
                    $query->where('medical_center_id', $selectedClinicId);
                });

            $appointments = $appointmentsQuery->get();

            if ($appointments->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'هیچ نوبتی برای بروزرسانی یافت نشد.',
                ], 404);
            }

            // بررسی ساعات کاری پزشک برای تاریخ جدید
            $selectedDate = Carbon::parse($validated['new_date']); // تبدیل تاریخ جدید به میلادی
            $dayOfWeek    = strtolower($selectedDate->format('l'));
            // بررسی ساعات کاری پزشک برای تاریخ جدید
            $workHours = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayOfWeek)
                ->when($selectedClinicId === 'default', function ($query) {
                    $query->whereNull('medical_center_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('medical_center_id', $selectedClinicId);
                })
                ->first();

            // دیباگ برای بررسی کوئری ساعات کاری
            if (! $workHours) {
                return response()->json([
                    'status'  => false,
                    'message' => 'ساعات کاری پزشک برای تاریخ جدید یافت نشد.',
                    'debug'   => [
                        'doctor_id' => $doctorId,
                        'medical_center_id' => $selectedClinicId,
                        'day'       => $dayOfWeek,
                    ],
                ], 400);
            }

            // لیست شماره‌های موبایل کاربران
            $recipients = [];

            foreach ($appointments as $appointment) {
                // ذخیره تاریخ قبلی برای پیامک
                $oldDate = $appointment->appointment_date;

                // به‌روزرسانی تاریخ نوبت
                $appointment->appointment_date = $validated['new_date'];
                $appointment->save();

                // اضافه کردن شماره موبایل به لیست دریافت‌کنندگان پیامک
                if ($appointment->patient && $appointment->patient->mobile) {
                    $recipients[] = $appointment->patient->mobile;
                }
            }

            // تبدیل تاریخ‌ها به فرمت شمسی
            $oldDateJalali = \Morilog\Jalali\Jalalian::fromDateTime($validated['old_date'])->format('Y/m/d');
            $newDateJalali = \Morilog\Jalali\Jalalian::fromDateTime($validated['new_date'])->format('Y/m/d');

            // ارسال پیامک به همه کاربران
            if (! empty($recipients)) {
                $messageContent = "کاربر گرامی، نوبت شما از تاریخ {$oldDateJalali} به تاریخ {$newDateJalali} تغییر یافت.";

                foreach ($recipients as $recipient) {
                    $user         = User::where('mobile', $recipient)->first();
                    $userFullName = $user ? $user->first_name . " " . $user->last_name : 'کاربر گرامی';

                    $messagesService = new MessageService(
                        SmsService::create(100252, $recipient, [$userFullName, $oldDateJalali, $newDateJalali, 'به نوبه'])
                    );
                    $messagesService->send();
                }
            }

            return response()->json([
                'status'           => true,
                'message'          => 'نوبت‌ها با موفقیت بروزرسانی شدند و پیامک ارسال گردید.',
                'total_recipients' => count($recipients),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'خطا در بروزرسانی نوبت‌ها.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function rescheduleAppointment(Request $request)
    {
        $validated = $request->validate([
            'old_date'         => 'required', // تاریخ می‌تونه شمسی یا میلادی باشه
            'new_date'         => 'required|date',
            'selectedClinicId' => 'nullable|string',
        ]);

        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId');

        try {
            // تبدیل تاریخ old_date از شمسی به میلادی اگه شمسی باشه
            $oldDateGregorian = $validated['old_date'];
            if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $validated['old_date'])) {
                $oldDateGregorian = Jalalian::fromFormat('Y/m/d', $validated['old_date'])->toCarbon()->toDateString();
            }

            // مقایسه تاریخ قدیم و جدید
            $newDateGregorian = Carbon::parse($validated['new_date'])->toDateString();
            if ($oldDateGregorian === $newDateGregorian) {
                return response()->json([
                    'status'  => false,
                    'message' => 'تاریخ جدید نمی‌تواند با تاریخ فعلی نوبت یکسان باشد.'
                ], 400);
            }

            $appointments = CounselingAppointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $oldDateGregorian)
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('medical_center_id', $selectedClinicId);
                }, function ($query) {
                    $query->whereNull('medical_center_id');
                })
                ->get();

            if ($appointments->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'هیچ نوبتی برای این تاریخ یافت نشد.'], 404);
            }

            $workHours = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', strtolower(Carbon::parse($validated['new_date'])->format('l')))
                ->when($selectedClinicId === 'default', function ($query) {
                    $query->whereNull('medical_center_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('medical_center_id', $selectedClinicId);
                })
                ->first();

            if (!$workHours) {
                return response()->json(['status' => false, 'message' => 'ساعات کاری یافت نشد.'], 400);
            }

            $recipients = [];
            $oldDateJalali = \Morilog\Jalali\Jalalian::fromDateTime($oldDateGregorian)->format('Y/m/d');
            $newDateJalali = \Morilog\Jalali\Jalalian::fromDateTime($validated['new_date'])->format('Y/m/d');

            foreach ($appointments as $appointment) {
                $appointment->appointment_date = $validated['new_date'];
                $appointment->save();

                if ($appointment->patient && $appointment->patient->mobile) {
                    $recipients[] = $appointment->patient->mobile;
                }
            }

            if (!empty($recipients)) {
                $message = "کاربر گرامی، نوبت شما از تاریخ {$oldDateJalali} به تاریخ {$newDateJalali} تغییر یافت.";

                $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
                $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
                $templateId = ($gatewayName === 'pishgamrayan') ? 100252 : null;

                SendSmsNotificationJob::dispatch(
                    $message,
                    $recipients,
                    $templateId,
                    [$oldDateJalali, $newDateJalali, 'به نوبه']
                )->delay(now()->addSeconds(5));
            }

            return response()->json([
                'status'           => true,
                'message'          => 'نوبت‌ها با موفقیت جابجا شدند .',
                'total_recipients' => count($recipients),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'خطا در جابجایی نوبت‌ها.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function getHolidayStatus(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'selectedClinicId' => 'nullable|string',
        ]);

        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId');

        // بخش ۱: بررسی تعطیلی
        $holidayRecord = DoctorHoliday::where('doctor_id', $doctorId)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId === 'default') {
                    $query->whereNull('medical_center_id');
                } elseif ($selectedClinicId) {
                    $query->where('medical_center_id', $selectedClinicId);
                }
            })
            ->first();

        $holidayDates = json_decode($holidayRecord->holiday_dates ?? '[]', true);
        $isHoliday = in_array($validated['date'], $holidayDates);

        // بخش ۲: بررسی نوبت‌ها (فقط نوبت‌های فعال)
        $appointments = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $validated['date'])
            ->where('status', '!=', 'cancelled') // نوبت‌های لغو شده رو حذف کن
            ->whereNull('deleted_at') // فقط نوبت‌های فعال
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId === 'default') {
                    $query->whereNull('medical_center_id');
                } elseif ($selectedClinicId) {
                    $query->where('medical_center_id', $selectedClinicId);
                }
            })
            ->get();

        // بخش ۳: بررسی ساعات کاری
        $dayOfWeek = strtolower(Carbon::parse($validated['date'])->englishDayOfWeek);
        $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayOfWeek)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId === 'default') {
                    $query->whereNull('medical_center_id');
                } elseif ($selectedClinicId) {
                    $query->where('medical_center_id', $selectedClinicId);
                }
            })
            ->first();

        $hasWorkHours = $workSchedule && !empty(json_decode($workSchedule->work_hours, true));

        // پاسخ نهایی
        return response()->json([
            'status' => true,
            'is_holiday' => $isHoliday,
            'has_appointments' => !$appointments->isEmpty(),
            'has_work_hours' => $hasWorkHours,
            'data' => $appointments,
        ]);
    }
    public function getAppointmentsByDate(Request $request)
    {

        $selectedClinicId = $request->selectedClinicId;
        $jalaliDate       = $request->input('date'); // دریافت تاریخ جلالی از فرانت‌اند
        // **اصلاح فرمت تاریخ ورودی**
        if (strpos($jalaliDate, '-') !== false) {
            // اگر تاریخ با `-` جدا شده بود، آن را به `/` تبدیل کنیم
            $jalaliDate = str_replace('-', '/', $jalaliDate);
        }
        // بررسی صحت فرمت تاریخ ورودی (1403/11/24)
        if (! preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $jalaliDate)) {
            return response()->json(['error' => 'فرمت تاریخ جلالی نادرست است.'], 400);
        }
        // **تبدیل تاریخ جلالی به میلادی**
        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $jalaliDate)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطا در تبدیل تاریخ جلالی به میلادی.'], 500);
        }
        // لاگ‌گیری برای بررسی تبدیل صحیح
        $doctorId = Auth::guard('doctor')->user()->id; // دریافت ID پزشک لاگین‌شده
        // گرفتن نوبت‌های پزشک جاری در تاریخ تبدیل‌شده به میلادی
        // Fetch appointments
        $query = CounselingAppointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $gregorianDate)
            ->with(['patient', 'insurance']);
        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            // اگر selectedClinicId برابر با 'default' باشد، medical_center_id باید NULL یا خالی باشد
            $query->whereNull('medical_center_id');
        } elseif ($selectedClinicId) {
            // اگر selectedClinicId مقدار داشت، medical_center_id باید با آن مطابقت داشته باشد
            $query->where('medical_center_id', $selectedClinicId);
        }

        $appointments = $query->get();
        return response()->json([
            'appointments' => $appointments,
        ]);
    }
    public function searchPatients(Request $request)
    {
        $query            = $request->query('query'); // مقدار جستجو شده
        $date             = $request->query('date');  // تاریخ انتخاب شده
        $selectedClinicId = $request->query('selectedClinicId');
        // **اصلاح فرمت تاریخ ورودی**
        if (strpos($date, '-') !== false) {
            // اگر تاریخ با `-` جدا شده بود، آن را به `/` تبدیل کنیم
            $date = str_replace('-', '/', $date);
        }

        // **تبدیل تاریخ جلالی به میلادی**
        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطا در تبدیل تاریخ جلالی به میلادی.'], 500);
        }
        // ایجاد کوئری پایه
        $appointmentsQuery = CounselingAppointment::with('patient', 'insurance')
            ->whereDate('appointment_date', $gregorianDate)
            ->whereHas('patient', function ($q) use ($query) {
                $q->where('first_name', 'like', "%$query%")
                    ->orWhere('last_name', 'like', "%$query%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$query%"])
                    ->orWhere('mobile', 'like', "%$query%")
                    ->orWhere('national_code', 'like', "%$query%");
            });

        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            // اگر selectedClinicId برابر با 'default' باشد، medical_center_id باید NULL یا خالی باشد
            $appointmentsQuery->whereNull('medical_center_id');
        } elseif ($selectedClinicId) {
            // اگر selectedClinicId مقدار داشت، medical_center_id باید با آن مطابقت داشته باشد
            $appointmentsQuery->where('medical_center_id', $selectedClinicId);
        }

        // اجرای کوئری و دریافت نتایج
        $patients = $appointmentsQuery->get();
        return response()->json(['patients' => $patients]);
    }

    public function updateAppointmentDate(Request $request, $id)
    {
        $request->validate([
            'new_date' => 'required|date_format:Y-m-d',
        ]);

        $appointment = CounselingAppointment::findOrFail($id);

        // چک کردن وضعیت نوبت
        if ($appointment->status === 'attended' || $appointment->status === 'cancelled') {
            return response()->json(['error' => 'نمی‌توانید نوبت ویزیت‌شده یا لغو شده را جابجا کنید.'], 400);
        }

        $newDate = Carbon::parse($request->new_date);
        if ($newDate->lt(Carbon::today())) {
            return response()->json(['error' => 'امکان جابجایی به تاریخ گذشته وجود ندارد.'], 400);
        }

        $oldDate = $appointment->appointment_date; // تاریخ قبلی
        $appointment->appointment_date = $newDate;
        $appointment->save();

        // تبدیل تاریخ‌ها به فرمت شمسی
        $oldDateJalali = Jalalian::fromDateTime($oldDate)->format('Y/m/d');
        $newDateJalali = Jalalian::fromDateTime($newDate)->format('Y/m/d');

        // ارسال پیامک جابجایی نوبت
        if ($appointment->patient && $appointment->patient->mobile) {
            $message = "کاربر گرامی، نوبت شما از تاریخ {$oldDateJalali} به {$newDateJalali} تغییر یافت.";
            SendSmsNotificationJob::dispatch(
                $message,
                [$appointment->patient->mobile],
                null, // بدون قالب
                [] // بدون پارامتر اضافی
            )->delay(now()->addSeconds(5));
        }

        return response()->json(['message' => 'نوبت با موفقیت جابجا شد .']);
    }
    public function filterAppointments(Request $request)
    {
        $status           = $request->query('status');
        $attendanceStatus = $request->query('attendance_status');
        $selectedClinicId = $request->input('selectedClinicId');
        $query            = CounselingAppointment::withTrashed(); // اضافه کردن withTrashed برای نمایش نوبت‌های لغو شده
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $query->where('medical_center_id', $selectedClinicId);
        }
        // فیلتر بر اساس `status`
        if (! empty($status)) {
            $query->where('status', $status);
        }
        // فیلتر بر اساس `attendance_status`
        if (! empty($attendanceStatus)) {
            $query->where('attendance_status', $attendanceStatus);
        }
        // دریافت نوبت‌ها به همراه اطلاعات بیمار، پزشک و کلینیک
        $appointments = $query->with(['patient', 'doctor', 'clinic', 'insurance'])->get();
        return response()->json([
            'success'      => true,
            'appointments' => $appointments,
        ]);
    }
    public function endVisit(Request $request, $id)
    {
        try {
            // پیدا کردن نوبت با شناسه
            $appointment = CounselingAppointment::findOrFail($id);

            // اعتبارسنجی درخواست
            $request->validate([
                'description' => 'nullable|string|max:1000', // توضیحات اختیاری
            ]);

            // به‌روزرسانی توضیحات و وضعیت نوبت
            $appointment->description = $request->input('description');
            $appointment->status = 'attended'; // تغییر وضعیت به attended
            $appointment->attendance_status = 'attended'; // هماهنگ کردن attendance_status
            $appointment->save();

            // برگرداندن پاسخ موفقیت‌آمیز
            return response()->json([
                'success' => true,
                'message' => 'ویزیت با موفقیت ثبت شد.',
                'appointment' => $appointment // اطلاعات نوبت به‌روز شده
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت ویزیت: ' . $e->getMessage()
            ], 500);
        }
    }
    public function updateWorkSchedule(Request $request)
    {
        // اعتبارسنجی ورودی
        $request->validate([
            'date'             => 'required|date',
            'work_hours'       => 'required|json',
            'selectedClinicId' => 'nullable|string', // اضافه کردن فیلتر selectedClinicId
        ]);

        $date             = $request->date;
        $workHours        = json_decode($request->work_hours, true);
        $selectedClinicId = $request->input('selectedClinicId');

        // بررسی وجود ساعات کاری برای تاریخ مورد نظر در جدول جدید
        $specialWorkHoursQuery = CounselingDailySchedule::where('date', $date);

        // اگر selectedClinicId وجود دارد و برابر 'default' نیست، فیلتر را اعمال کنید
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $specialWorkHoursQuery->where('medical_center_id', $selectedClinicId);
        }

        $specialWorkHours = $specialWorkHoursQuery->first();

        if ($specialWorkHours) {
            // اگر وجود داشت، بروزرسانی شود
            $specialWorkHours->update(['work_hours' => json_encode($workHours)]);
        } else {
            // در غیر این صورت، رکورد جدید اضافه شود
            CounselingDailySchedule::create([
                'doctor_id'  => auth()->guard('doctor')->user()->id,
                'date'       => $date,
                'work_hours' => json_encode($workHours),
                'medical_center_id'  => $selectedClinicId, // اضافه کردن medical_center_id به رکورد جدید
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'ساعات کاری با موفقیت بروزرسانی شد.',
        ]);
    }
}
