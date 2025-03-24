<?php

namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesRateLimiting;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;
use App\Models\DoctorAppointmentConfig;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class ScheduleSettingController extends Controller
{
    use HandlesRateLimiting;
    /**
     * نمایش صفحه ساعات کاری
     */
    public function workhours(Request $request)
    {
        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->query('selectedClinicId', 'default');

        // ایجاد تنظیمات نوبت‌دهی فقط در صورت عدم وجود رکورد
        $appointmentConfig = DoctorAppointmentConfig::firstOrCreate(
            [
                'doctor_id' => $doctorId,
                'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
            ],
            [
                'auto_scheduling'      => true,
                'online_consultation'  => false,
                'holiday_availability' => false,
            ]
        );

        // ایجاد یا به‌روزرسانی ساعات کاری فقط در صورت عدم وجود رکورد
        $workSchedules = DoctorWorkSchedule::firstOrCreate(
            [
                'doctor_id' => $doctorId,
                'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
            ],
            [
                // سایر تنظیمات ساعات کاری به صورت پویا از طریق درخواست ارسال شود
            ]
        );

        return view("dr.panel.turn.schedule.scheduleSetting.workhours", [
            'appointmentConfig' => $appointmentConfig,
            'workSchedules'     => $workSchedules,
            'selectedClinicId'  => $selectedClinicId,
        ]);
    }

    public function copyWorkHours(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', 'default');
        $override         = filter_var($request->input('override', false), FILTER_VALIDATE_BOOLEAN);
        $validated        = $request->validate([
            'source_day'  => 'required|string',
            'target_days' => 'required|array|min:1',
            'override'    => 'nullable|in:0,1,true,false',
        ]);
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        DB::beginTransaction();
        try {
            // دریافت ساعات کاری روز مبدأ
            $sourceWorkSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $validated['source_day'])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();
            if (! $sourceWorkSchedule || empty($sourceWorkSchedule->work_hours)) {
                return response()->json([
                    'message' => 'روز مبدأ یافت نشد یا فاقد ساعات کاری است.',
                    'status'  => false,
                ], 404);
            }
            // تبدیل ساعات کاری روز مبدأ به آرایه
            $sourceWorkHours = json_decode($sourceWorkSchedule->work_hours, true) ?? [];
            foreach ($validated['target_days'] as $targetDay) {
                $targetWorkSchedule = DoctorWorkSchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day'       => $targetDay,
                        'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                    ],
                    [
                        'is_working' => true,
                        'work_hours' => json_encode([])
                    ]
                );
                // اگر حالت override فعال باشد، ساعات قبلی حذف می‌شوند
                if ($override) {
                    $targetWorkSchedule->work_hours = json_encode($sourceWorkHours);
                } else {
                    // بررسی تداخل زمانی با ساعات کاری فعلی روز مقصد
                    $existingWorkHours = json_decode($targetWorkSchedule->work_hours, true) ?? [];
                    foreach ($sourceWorkHours as $sourceSlot) {
                        foreach ($existingWorkHours as $existingSlot) {
                            $sourceStart   = Carbon::createFromFormat('H:i', $sourceSlot['start']);
                            $sourceEnd     = Carbon::createFromFormat('H:i', $sourceSlot['end']);
                            $existingStart = Carbon::createFromFormat('H:i', $existingSlot['start']);
                            $existingEnd   = Carbon::createFromFormat('H:i', $existingSlot['end']);
                            if (
                                ($sourceStart >= $existingStart && $sourceStart < $existingEnd) ||
                                ($sourceEnd > $existingStart && $sourceEnd <= $existingEnd) ||
                                ($sourceStart <= $existingStart && $sourceEnd >= $existingEnd)
                            ) {
                                return response()->json([
                                    'message' => 'بازه زمانی ' . $sourceStart->format('H:i') . ' تا ' . $sourceEnd->format('H:i') . ' با بازه‌های موجود تداخل دارد.',
                                    'status'  => false,
                                    'day'     => $targetDay,
                                ], 400);
                            }
                        }
                    }
                    // اضافه کردن بازه‌های جدید بدون حذف قبلی‌ها
                    $mergedWorkHours                = array_merge($existingWorkHours, $sourceWorkHours);
                    $targetWorkSchedule->work_hours = json_encode($mergedWorkHours);
                }
                $targetWorkSchedule->save();
            }
            DB::commit();
            return response()->json([
                'message'       => 'ساعات کاری با موفقیت کپی شد',
                'status'        => true,
                'target_days'   => $validated['target_days'],
                'workSchedules' => DoctorWorkSchedule::where('doctor_id', $doctor->id)
                    ->whereIn('day', $validated['target_days'])
                    ->where(function ($query) use ($selectedClinicId) {
                        if ($selectedClinicId !== 'default') {
                            $query->where('clinic_id', $selectedClinicId);
                        } else {
                            $query->whereNull('clinic_id');
                        }
                    })
                    ->get(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'خطا در کپی ساعات کاری. لطفاً مجدداً تلاش کنید.',
                'status'  => false,
            ], 500);
        }
    }

    public function copySingleSlot(Request $request)
    {
        $validated = $request->validate([
            'source_day' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'target_days' => 'required|array',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'max_appointments' => 'required|integer|min:1',
            'override' => 'boolean',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $selectedClinicId = $request->input('selectedClinicId', 'default');
        $sourceDay = $validated['source_day'];
        $targetDays = array_diff($validated['target_days'], [$sourceDay]); // حذف روز مبدأ

        if (empty($targetDays)) {
            return response()->json(['message' => 'هیچ روز مقصدی انتخاب نشده است'], 400);
        }

        $newSlot = [
            'start' => $validated['start_time'],
            'end' => $validated['end_time'],
            'max_appointments' => $validated['max_appointments'],
        ];

        $conflictingSlots = [];
        foreach ($targetDays as $day) {
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $day)
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if ($workSchedule && $workSchedule->work_hours) {
                $workHours = json_decode($workSchedule->work_hours, true);
                foreach ($workHours as $slot) {
                    if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                        $conflictingSlots[] = [
                            'day' => $day,
                            'start' => $slot['start'],
                            'end' => $slot['end'],
                        ];
                    }
                }
            }
        }

        if (!empty($conflictingSlots) && !$validated['override']) {
            return response()->json(['conflicting_slots' => $conflictingSlots], 400);
        }

        foreach ($targetDays as $day) {
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $day)
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if (!$workSchedule) {
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $day,
                    'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                    'is_working' => true,
                    'work_hours' => json_encode([$newSlot]),
                ]);
            } else {
                $workHours = json_decode($workSchedule->work_hours, true) ?? [];
                if ($validated['override']) {
                    $workHours = array_filter($workHours, function ($slot) use ($newSlot) {
                        return !$this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end']);
                    });
                }
                $workHours[] = $newSlot;
                $workSchedule->update(['work_hours' => json_encode(array_values($workHours)), 'is_working' => true]);
            }
        }

        $updatedSchedules = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('clinic_id', $selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->get();

        return response()->json([
            'message' => $validated['override'] ? 'بازه‌ها جایگزین شدند' : 'بازه‌ها کپی شدند',
            'target_days' => $targetDays,
            'workSchedules' => $updatedSchedules,
        ]);
    }

    private function isTimeConflict($newStart, $newEnd, $existingStart, $existingEnd)
    {
        $newStartMinutes = $this->timeToMinutes($newStart);
        $newEndMinutes = $this->timeToMinutes($newEnd);
        $existingStartMinutes = $this->timeToMinutes($existingStart);
        $existingEndMinutes = $this->timeToMinutes($existingEnd);

        return ($newStartMinutes < $existingEndMinutes && $newEndMinutes > $existingStartMinutes);
    }

    private function timeToMinutes($time)
    {
        [$hours, $minutes] = explode(':', $time);
        return ($hours * 60) + $minutes;
    }

    // تابع کمکی برای تبدیل روز به فارسی
    private function getDayNameInPersian($day)
    {
        $days = [
            'saturday'  => 'شنبه',
            'sunday'    => 'یکشنبه',
            'monday'    => 'دوشنبه',
            'tuesday'   => 'سه‌شنبه',
            'wednesday' => 'چهارشنبه',
            'thursday'  => 'پنج‌شنبه',
            'friday'    => 'جمعه',
        ];
        return $days[$day] ?? $day;
    }
    public function checkDaySlots(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', 'default');
        $validated        = $request->validate([
            'day' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        ]);
        $doctor       = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $validated['day'])
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('clinic_id', $selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->first();
        // بررسی اینکه آیا ساعات کاری به صورت JSON ذخیره شده است و مقدار دارد
        $hasSlots = $workSchedule && ! empty(json_decode($workSchedule->work_hours, true));
        return response()->json(['hasSlots' => $hasSlots]);
    }

    public function saveTimeSlot(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));
        $validated        = $request->validate([
            'day'              => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'max_appointments' => 'required|integer|min:1',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        try {
            // بررسی وجود رکورد با داده‌های انتخاب‌شده
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $validated['day'])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if (! $workSchedule) {
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id'  => $doctor->id,
                    'day'        => $validated['day'],
                    'clinic_id'  => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                    'is_working' => true,
                    'work_hours' => json_encode([])
                ]);
            }

            $existingWorkHours = json_decode($workSchedule->work_hours, true) ?? [];

            // اگر بازه زمانی جدید تداخلی با بازه‌های موجود نداشته باشد، اضافه کردن به `work_hours`
            $newSlot = [
                'start'            => $validated['start_time'],
                'end'              => $validated['end_time'],
                'max_appointments' => $validated['max_appointments'],
            ];

            if (
                ! array_filter($existingWorkHours, function ($hour) use ($newSlot) {
                    return Carbon::createFromFormat('H:i', $newSlot['start'])->equalTo(Carbon::createFromFormat('H:i', $hour['start'])) &&
                    Carbon::createFromFormat('H:i', $newSlot['end'])->equalTo(Carbon::createFromFormat('H:i', $hour['end']));
                })
            ) {
                $existingWorkHours[] = $newSlot;

                // بروزرسانی `work_hours`
                $workSchedule->update(['work_hours' => json_encode($existingWorkHours)]);
            }

            return response()->json([
                'message'      => 'ساعت کاری با موفقیت ذخیره شد',
                'status'       => true,
                'work_hours'   => $existingWorkHours,
                'workSchedule' => $workSchedule,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در ذخیره‌سازی نوبت',
                'status'  => false,
            ], 500);
        }
    }

    public function deleteTimeSlot(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', 'default');
        $validated        = $request->validate([
            'day'        => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        try {
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $validated['day'])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if (! $workSchedule) {
                return response()->json(['message' => 'ساعات کاری یافت نشد', 'status' => false], 404);
            }

            $existingWorkHours = json_decode($workSchedule->work_hours, true) ?? [];

            // فیلتر کردن و حذف ساعت انتخاب‌شده
            $updatedWorkHours = array_filter($existingWorkHours, function ($slot) use ($validated) {
                return ! ($slot['start'] === $validated['start_time'] && $slot['end'] === $validated['end_time']);
            });

            if (count($existingWorkHours) === count($updatedWorkHours)) {
                return response()->json(['message' => 'ساعت انتخاب‌شده یافت نشد', 'status' => false], 404);
            }

            // بروزرسانی `work_hours`
            $workSchedule->update(['work_hours' => json_encode(array_values($updatedWorkHours))]);

            return response()->json([
                'message' => 'ساعات کاری با موفقیت حذف شد',
                'status'  => true,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'خطا در حذف ساعت کاری', 'status' => false], 500);
        }
    }

    public function updateWorkDayStatus(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));
        $validated        = $request->validate([
            'day'        => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'is_working' => 'required|in:0,1,true,false',
        ]);

        try {
            $doctor       = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $isWorking    = filter_var($validated['is_working'], FILTER_VALIDATE_BOOLEAN);
            $workSchedule = DoctorWorkSchedule::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'day'       => $validated['day'],
                    'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                ],
                [
                    'is_working' => $isWorking,
                ]
            );

            // اگر روز کاری غیرفعال باشد، `work_hours` را خالی کنیم
            if (! $isWorking) {
                $workSchedule->update(['work_hours' => json_encode([])]);
            }

            return response()->json([
                'message' => $isWorking ? 'روز کاری با موفقیت فعال شد' : 'روز کاری با موفقیت غیرفعال شد',
                'status'  => true,
                'data'    => $workSchedule,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در بروزرسانی وضعیت روز کاری',
                'status'  => false,
            ], 500);
        }
    }

    public function updateAutoScheduling(Request $request)
    {
        $selectedClinicId = $request->input('selectedClinicId', 'default');
        $validated        = $request->validate([
            'auto_scheduling' => [
                'required',
                'in:0,1,true,false', // Explicitly allow these values
            ],
        ]);

        // Convert to strict boolean
        $autoScheduling = filter_var($validated['auto_scheduling'], FILTER_VALIDATE_BOOLEAN);

        try {
            $doctor            = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $appointmentConfig = DoctorAppointmentConfig::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                ],
                [
                    'auto_scheduling' => $autoScheduling,
                    'doctor_id'       => $doctor->id,
                    'clinic_id'       => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                ]
            );

            return response()->json([
                'message' => $autoScheduling
                ? 'نوبت‌دهی خودکار فعال شد'
                : 'نوبت‌دهی خودکار غیرفعال شد',
                'status'  => true,
                'data'    => [
                    'auto_scheduling' => $appointmentConfig->auto_scheduling,
                ],
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'خطا در به‌روزرسانی تنظیمات',
                'status'  => false,
            ], 500);
        }
    }

    public function saveAppointmentSettings(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));
        $validated        = $request->validate([
            'day'              => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'max_appointments' => 'nullable|integer|min:1',
            'selected_days'    => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        ]);
        try {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            // تبدیل selected_days به آرایه
            $selectedDays = is_array($request->input('selected_days'))
            ? $request->input('selected_days')
            : explode(',', $request->input('selected_days'));
            $results = [];
            foreach ($selectedDays as $day) {
                // تنظیمات موجود برای روز
                $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('day', $validated['day'])
                    ->where(function ($query) use ($selectedClinicId) {
                        if ($selectedClinicId !== 'default') {
                            $query->where('clinic_id', $selectedClinicId);
                        } else {
                            $query->whereNull('clinic_id');
                        }
                    })
                    ->first();
                // بازیابی تنظیمات قبلی به صورت آرایه
                $existingSettings = [];
                if ($workSchedule && $workSchedule->appointment_settings) {
                    $existingSettings = json_decode($workSchedule->appointment_settings, true);
                    if (! is_array($existingSettings)) {
                        $existingSettings = [];
                    }
                }
                // بررسی اینکه آیا تنظیمی برای این ساعات کاری موجود است
                foreach ($existingSettings as $setting) {
                    if (
                        ($validated['start_time'] >= $setting['start_time'] && $validated['start_time'] < $setting['end_time']) ||
                        ($validated['end_time'] > $setting['start_time'] && $validated['end_time'] <= $setting['end_time']) ||
                        ($validated['start_time'] <= $setting['start_time'] && $validated['end_time'] >= $setting['end_time'])
                    ) {
                        return response()->json([
                            'message' => "برای بازه زمانی {$validated['start_time']} تا {$validated['end_time']} در روز " . $this->getDayNameInPersian($validated['day']) . " تنظیماتی وجود دارد.",
                            'status'  => false,
                        ], 400);
                    }
                }
                $workhours_identifier = $request['workhours_identifier'];

                // افزودن تنظیم جدید به آرایه تنظیمات موجود
                $newSetting = [
                    'id'               => $workhours_identifier,
                    'start_time'       => $validated['start_time'],
                    'end_time'         => $validated['end_time'],
                    'max_appointments' => $validated['max_appointments'],
                    'selected_day'     => $validated['selected_days'],
                ];
                $existingSettings[] = $newSetting;
                // ذخیره تنظیمات جدید به صورت JSON
                DoctorWorkSchedule::updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day'       => $validated['day'],
                        'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                    ],
                    [
                        'is_working'           => true,
                        'appointment_settings' => json_encode($existingSettings),
                    ]
                );
                $results[] = $newSetting;
            }
            return response()->json([
                'message' => 'تنظیمات نوبت‌دهی با موفقیت ذخیره شد.',
                'results' => $results,
                'status'  => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در ذخیره‌سازی تنظیمات.',
                'status'  => false,
            ], 500);
        }
    }

    private function calculateMaxAppointments($startTime, $endTime)
    {
        try {
            // تبدیل زمان‌ها به فرمت Carbon
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end   = Carbon::createFromFormat('H:i', $endTime);
            // محاسبه تفاوت زمانی به دقیقه
            $diffInMinutes = $start->diffInMinutes($end);
            // تعیین طول هر نوبت (به دقیقه)
            $appointmentDuration = config('settings.default_appointment_duration', 20); // 20 دقیقه پیش‌فرض
            // محاسبه تعداد نوبت‌ها
            return floor($diffInMinutes / $appointmentDuration);
        } catch (\Exception $e) {
            return 0; // بازگرداندن مقدار صفر در صورت بروز خطا
        }
    }
    public function getAppointmentSettings(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', 'default');
        $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        // دریافت `id` از درخواست
        $id = $request->id;

        // بازیابی تنظیمات نوبت‌دهی برای پزشک
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $request->day)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('clinic_id', $selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->first();

        if ($workSchedule && $workSchedule->appointment_settings) {
            $settings = json_decode($workSchedule->appointment_settings, true);

            // فیلتر تنظیمات بر اساس `id`
            $filteredSettings = array_filter($settings, function ($setting) use ($id) {
                return $setting['id'] == $id;
            });

            return response()->json([
                'settings' => array_values($filteredSettings), // بازگرداندن تنظیمات فیلتر شده
                'day'      => $workSchedule->day,
                'status'   => true,
            ]);
        }

        return response()->json([
            'message' => 'تنظیماتی یافت نشد',
            'status'  => false,
        ]);
    }

  public function saveWorkSchedule(Request $request)
{
    $selectedClinicId = $request->input('selectedClinicId') ?? 'default';

    $validatedData = $request->validate([
        'auto_scheduling'      => 'required|boolean',
        'calendar_days'        => 'nullable|integer|min:1|max:365',
        'online_consultation'  => 'required|boolean',
        'holiday_availability' => 'required|boolean',
        'days'                 => 'array',
    ]);

    DB::beginTransaction();
    try {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        // ذخیره تنظیمات کلی نوبت‌دهی
        $appointmentConfig = DoctorAppointmentConfig::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
            ],
            [
                'auto_scheduling'      => $validatedData['auto_scheduling'],
                'calendar_days'        => $validatedData['calendar_days'] ?? null,
                'online_consultation'  => $validatedData['online_consultation'],
                'holiday_availability' => $validatedData['holiday_availability'],
            ]
        );

        // به‌روزرسانی یا ایجاد ساعات کاری برای هر روز
        foreach ($validatedData['days'] as $day => $dayConfig) {
            $workHours = isset($dayConfig['slots']) ? array_map(function ($slot) {
                return [
                    'start'            => $slot['start_time'],
                    'end'              => $slot['end_time'],
                    'max_appointments' => $slot['max_appointments'] ?? 1,
                ];
            }, $dayConfig['slots']) : [];

            DoctorWorkSchedule::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'day'       => $day,
                    'clinic_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                ],
                [
                    'is_working' => $dayConfig['is_working'] ?? false,
                    'work_hours' => !empty($workHours) ? json_encode($workHours) : null,
                ]
            );
        }

        DB::commit();
        return response()->json([
            'message' => 'تنظیمات ساعات کاری با موفقیت ذخیره شد.',
            'status'  => true,
            'data'    => [
                'calendar_days' => $appointmentConfig->calendar_days,
            ],
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'خطا در ذخیره‌سازی تنظیمات ساعات کاری: ' . $e->getMessage(),
            'status'  => false,
        ], 500);
    }
}

    public function getAllDaysSettings(Request $request)
    {
        try {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            // دریافت داده‌های ارسال‌شده از درخواست
            $inputDay             = $request->input('day');
            $inputStartTime       = $request->input('start_time');
            $inputEndTime         = $request->input('end_time');
            $inputMaxAppointments = $request->input('max_appointments');
            $selectedClinicId     = $request->query('selectedClinicId', 'default');

            // فیلتر کردن بر اساس داده‌های ارسال‌شده و `selectedClinicId`
            $workSchedules = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->when($inputDay, function ($query) use ($inputDay) {
                    $query->where('day', $inputDay);
                })
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->get();

            $filteredSettings = $workSchedules->map(function ($schedule) use ($inputStartTime, $inputEndTime, $inputMaxAppointments) {
                // تبدیل appointment_settings به آرایه
                $appointmentSettings = [];
                if ($schedule->appointment_settings) {
                    if (is_string($schedule->appointment_settings)) {
                        $appointmentSettings = json_decode($schedule->appointment_settings, true);
                    } elseif (is_array($schedule->appointment_settings)) {
                        $appointmentSettings = $schedule->appointment_settings;
                    }
                }
                // اگر appointment_settings یک آرایه نباشد، آن را به آرایه خالی تبدیل کنید
                if (! is_array($appointmentSettings)) {
                    $appointmentSettings = [];
                }
                // مقایسه با مقادیر ورودی
                if (
                    (! $inputStartTime || ($appointmentSettings['start_time'] ?? '') == $inputStartTime) &&
                    (! $inputEndTime || ($appointmentSettings['end_time'] ?? '') == $inputEndTime) &&
                    (! $inputMaxAppointments || ($appointmentSettings['max_appointments'] ?? '') == $inputMaxAppointments)
                ) {
                    return [
                        'day'              => $schedule->day,
                        'start_time'       => $appointmentSettings['start_time'] ?? '',
                        'end_time'         => $appointmentSettings['end_time'] ?? '',
                        'max_appointments' => $appointmentSettings['max_appointments'] ?? '',
                        'selected_day'     => $appointmentSettings['selected_day'] ?? '',
                    ];
                }
                return null;
            })->filter(); // حذف مقادیر `null`

            return response()->json([
                'status'   => true,
                'settings' => $filteredSettings->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'خطا در دریافت تنظیمات.',
            ], 500);
        }
    }

    public function deleteScheduleSetting(Request $request)
    {
        $validated = $request->validate([
            'day'          => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'selected_day' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i',
        ]);
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        try {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            // دریافت رکورد مربوط به ساعات کاری پزشک در روز انتخاب‌شده
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $validated['day'])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();
            if (! $workSchedule) {
                return response()->json([
                    'message' => 'ساعات کاری یافت نشد',
                    'status'  => false,
                ], 404);
            }
            // دیکد کردن تنظیمات نوبت‌دهی (appointment_settings)
            $settings = json_decode($workSchedule->appointment_settings, true) ?? [];
            if (empty($settings)) {
                return response()->json([
                    'message' => 'هیچ تنظیماتی برای این روز یافت نشد',
                    'status'  => false,
                ], 404);
            }
            // فیلتر تنظیمات برای حذف آیتم موردنظر
            $updatedSettings = array_filter($settings, function ($setting) use ($validated) {
                return ! (
                    trim($setting['start_time']) === trim($validated['start_time']) &&  //  استفاده از نام درست فیلد
                    trim($setting['end_time']) === trim($validated['end_time']) &&      //  استفاده از نام درست فیلد
                    trim($setting['selected_day']) === trim($validated['selected_day']) //  حذف بر اساس `selected_day`
                );
            });
            // بررسی اینکه آیا هیچ تنظیمی حذف شده است یا نه
            if (count($settings) === count($updatedSettings)) {
                return response()->json([
                    'message' => 'هیچ تنظیمی حذف نشد. مقدار ارسالی با مقدار ذخیره شده تطابق ندارد.',
                    'status'  => false,
                ], 400);
            }
            // بروزرسانی فیلد `appointment_settings`
            $workSchedule->update(['appointment_settings' => json_encode(array_values($updatedSettings))]);
            return response()->json([
                'message' => 'تنظیم نوبت با موفقیت حذف شد',
                'status'  => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در حذف تنظیم نوبت: ' . $e->getMessage(),
                'status'  => false,
            ], 500);
        }
    }

    /**
     * تعیین نوع ساعات کاری بر اساس زمان
     */
    private function determineSlotType($startTime)
    {
        try {
            $hour = intval(substr($startTime, 0, 2));
            if ($hour >= 5 && $hour < 12) {
                return 'morning'; // ساعات کاری صبح
            } elseif ($hour >= 12 && $hour < 17) {
                return 'afternoon'; // ساعات کاری بعد از ظهر
            } else {
                return 'evening'; // ساعات کاری عصر
            }
        } catch (\Exception $e) {
            return 'unknown'; // بازگرداندن مقدار پیش‌فرض در صورت بروز خطا
        }
    }
    /**
     * بازیابی تنظیمات ساعات کاری
     */
    public function getWorkSchedule(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', 'default');
        $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        $workSchedules = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('clinic_id', $selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->get();

        return response()->json([
            'workSchedules' => $workSchedules,
        ]);
    }

    // متدهای موجود در کنترلر اصلی
    public function index()
    {
        return view("dr.panel.turn.schedule.scheduleSetting.index");
    }
    public function turnContract()
    {
        return view("dr.panel.turn.schedule.turnContract.index");
    }
    public function mySpecialDays()
    {
        return view("dr.panel.turn.schedule.scheduleSetting.my-special-days");
    }
    public function getAppointmentsCountPerDay(Request $request)
    {
        try {
            // دریافت شناسه پزشک یا منشی
            $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
            $selectedClinicId = $request->input('selectedClinicId'); // کلینیک انتخابی

            // استخراج تعداد نوبت‌های هر روز با شرط خاص برای 'default'
            $appointments = DB::table('appointments')
                ->select(DB::raw('appointment_date, COUNT(*) as appointment_count'))
                ->where('doctor_id', $doctorId)
                ->where('status', 'scheduled')
                ->whereNull('deleted_at') // فیلتر برای نوبت‌های فعال
                ->when($selectedClinicId === 'default', function ($query) use ($doctorId) {
                    // در صورت 'default' فقط نوبت‌های بدون کلینیک (clinic_id = NULL) مرتبط با پزشک
                    $query->whereNull('clinic_id')->where('doctor_id', $doctorId);
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    // در صورت ارسال کلینیک خاص
                    $query->where('clinic_id', $selectedClinicId);
                })
                ->groupBy('appointment_date')
                ->get();

            // قالب‌بندی داده‌ها
            $data = $appointments->map(function ($item) {
                return [
                    'appointment_date'  => $item->appointment_date,
                    'appointment_count' => $item->appointment_count,
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'خطا در دریافت داده‌ها',
                'error'   => $e->getMessage(), // نمایش پیام خطا برای دیباگ بهتر
            ], 500);
        }
    }

    public function toggleHolidayStatus(Request $request)
    {
        $validated = $request->validate([
            'date'             => 'required|date',
            'selectedClinicId' => 'nullable|string',
        ]);

        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId');

        // بازیابی یا ایجاد رکورد تعطیلات با شرط کلینیک
        $holidayRecordQuery = DoctorHoliday::where('doctor_id', $doctorId);

        if ($selectedClinicId === 'default') {
            $holidayRecordQuery->whereNull('clinic_id');
        } elseif ($selectedClinicId && $selectedClinicId !== 'default') {
            $holidayRecordQuery->where('clinic_id', $selectedClinicId);
        }

        $holidayRecord = $holidayRecordQuery->firstOrCreate([
            'doctor_id' => $doctorId,
            'clinic_id' => ($selectedClinicId !== 'default' ? $selectedClinicId : null),
        ], [
            'holiday_dates' => json_encode([])
        ]);

        // بررسی و تبدیل JSON به آرایه
        $holidayDates = json_decode($holidayRecord->holiday_dates, true);
        if (! is_array($holidayDates)) {
            $holidayDates = [];
        }

        // بررسی وجود تاریخ و تغییر وضعیت
        if (in_array($validated['date'], $holidayDates)) {
            // حذف تاریخ از لیست تعطیلات
            $holidayDates = array_diff($holidayDates, [$validated['date']]);
            $message      = 'این تاریخ از حالت تعطیلی خارج شد.';
            $isHoliday    = false;
        } else {
            // اضافه کردن تاریخ به لیست تعطیلات
            $holidayDates[] = $validated['date'];
            $message        = 'این تاریخ تعطیل شد.';
            $isHoliday      = true;

            // حذف SpecialDailySchedule مرتبط با کلینیک
            $specialDayQuery = SpecialDailySchedule::where('date', $validated['date']);

            if ($selectedClinicId === 'default') {
                $specialDayQuery->whereNull('clinic_id');
            } elseif ($selectedClinicId && $selectedClinicId !== 'default') {
                $specialDayQuery->where('clinic_id', $selectedClinicId);
            }

            $specialDayQuery->delete();
        }

        // به‌روزرسانی رکورد تعطیلات
        $holidayRecord->update([
            'holiday_dates' => json_encode(array_values($holidayDates)),
        ]);

        return response()->json([
            'status'        => true,
            'is_holiday'    => $isHoliday,
            'message'       => $message,
            'holiday_dates' => $holidayDates,
        ]);
    }

    public function getHolidayDates(Request $request)
    {
        // دریافت شناسه پزشک یا منشی
        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId'); // کلینیک انتخابی

        // جستجوی تعطیلی‌های پزشک با شرط‌های لازم
        $holidayQuery = DoctorHoliday::where('doctor_id', $doctorId)
            ->when($selectedClinicId === 'default', function ($query) use ($doctorId) {
                // در صورت 'default' فقط تعطیلی‌های بدون کلینیک (clinic_id = NULL) بازگردانده شود
                $query->whereNull('clinic_id')->where('doctor_id', $doctorId);
            })
            ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                // در صورت ارسال کلینیک خاص
                $query->where('clinic_id', $selectedClinicId);
            });

        $holidayRecord = $holidayQuery->first();
        $holidays      = [];

        // اگر رکورد تعطیلی وجود داشت و تاریخ‌های تعطیلی خالی نبودند
        if ($holidayRecord && ! empty($holidayRecord->holiday_dates)) {
            $decodedHolidays = json_decode($holidayRecord->holiday_dates, true);
            $holidays        = is_array($decodedHolidays) ? $decodedHolidays : [];
        }

        return response()->json([
            'status'   => true,
            'holidays' => $holidays,
        ]);
    }

    public function getHolidayStatus(Request $request)
    {
        // اعتبارسنجی ورودی
        $validated = $request->validate([
            'date'             => 'required|date',
            'selectedClinicId' => 'nullable|string', // فیلتر selectedClinicId
        ]);

        // گرفتن شناسه دکتر
        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId');

        /**
         * 🟡 بخش ۱: بررسی تعطیلی پزشک با شرط کلینیک
         */
        $holidayRecord = DoctorHoliday::where('doctor_id', $doctorId)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId === 'default') {
                    $query->whereNull('clinic_id');
                } elseif ($selectedClinicId) {
                    $query->where('clinic_id', $selectedClinicId);
                }
            })
            ->first();

        $holidayDates = json_decode($holidayRecord->holiday_dates ?? '[]', true);
        $isHoliday    = in_array($validated['date'], $holidayDates);

        /**
         * 🟡 بخش ۲: بررسی نوبت‌های پزشک با شرط کلینیک
         */
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $validated['date'])
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId === 'default') {
                    $query->whereNull('clinic_id');
                } elseif ($selectedClinicId) {
                    $query->where('clinic_id', $selectedClinicId);
                }
            })
            ->get();

        /**
         * 🟡 بخش ۳: ارسال پاسخ به‌صورت JSON
         */
        return response()->json([
            'status'     => true,
            'is_holiday' => $isHoliday,
            'data'       => $appointments,
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
        $query = Appointment::withTrashed()
            ->whereIn('id', $appointmentIds)
            ->where('appointment_date', $gregorianDate);

        if ($selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } else {
            $query->where('clinic_id', $selectedClinicId);
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
            $templateId = ($gatewayName === 'pishgamrayan') ? 100253 : null;

            SendSmsNotificationJob::dispatch(
                $message,
                $recipients,
                $templateId,
                [$jalaliDate]
            )->delay(now()->addSeconds(5));
        }

        return response()->json([
            'status' => true,
            'message' => $newlyCancelled ? 'نوبت‌ها با موفقیت لغو شدند و پیامک در صف قرار گرفت.' : 'برخی نوبت‌ها قبلاً لغو یا ویزیت شده بودند و تغییری اعمال نشد.',
            'total_recipients' => count($recipients),
        ]);
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

            $appointments = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $oldDateGregorian)
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('clinic_id', $selectedClinicId);
                }, function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->get();

            if ($appointments->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'هیچ نوبتی برای این تاریخ یافت نشد.'], 404);
            }

            $workHours = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', strtolower(Carbon::parse($validated['new_date'])->format('l')))
                ->when($selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('clinic_id', $selectedClinicId);
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
                'message'          => 'نوبت‌ها با موفقیت جابجا شدند و پیامک در صف قرار گرفت.',
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
            $appointmentsQuery = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $validated['old_date'])
                ->when($selectedClinicId === 'default', function ($query) {
                    // اگر selectedClinicId برابر با 'default' باشد، فقط نوبت‌های بدون کلینیک را در نظر بگیرد
                    $query->whereNull('clinic_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    // در غیر این صورت، نوبت‌های مربوط به کلینیک مشخص‌شده را بررسی کند
                    $query->where('clinic_id', $selectedClinicId);
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
            $workHours = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayOfWeek)
                ->when($selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('clinic_id', $selectedClinicId);
                })
                ->first();

            // دیباگ برای بررسی کوئری ساعات کاری
            if (! $workHours) {
                return response()->json([
                    'status'  => false,
                    'message' => 'ساعات کاری پزشک برای تاریخ جدید یافت نشد.',
                    'debug'   => [
                        'doctor_id' => $doctorId,
                        'clinic_id' => $selectedClinicId,
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

    public function getNextAvailableDate(Request $request)
    {
        // دریافت شناسه پزشک یا منشی
        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId'); // کلینیک انتخابی

        // دریافت تعطیلی‌های پزشک با توجه به کلینیک
        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)
            ->when($selectedClinicId === 'default', function ($query) use ($doctorId) {
                // در صورت 'default' فقط تعطیلی‌های بدون کلینیک (clinic_id = NULL)
                $query->whereNull('clinic_id');
            })
            ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                // اگر کلینیک خاص ارسال شود
                $query->where('clinic_id', $selectedClinicId);
            });

        $holidays     = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);

        // تعداد روزهای قابل بررسی برای نوبت خالی
        $today       = Carbon::now()->startOfDay();
        $daysToCheck = DoctorAppointmentConfig::where('doctor_id', $doctorId)->value('calendar_days') ?? 30;

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
            $appointmentQuery = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->when($selectedClinicId === 'default', function ($query) {
                    // فقط نوبت‌های بدون کلینیک (clinic_id = NULL) بازگردانده شود
                    $query->whereNull('clinic_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    // نوبت‌های کلینیک مشخص‌شده بازگردانده شود
                    $query->where('clinic_id', $selectedClinicId);
                });

            return ! $appointmentQuery->exists();
        });

        return response()->json([
            'status' => $nextAvailableDate ? true : false,
            'date'   => $nextAvailableDate ?? 'هیچ نوبت خالی یافت نشد.',
        ]);
    }

    public function getAppointmentsByDate(Request $request)
    {
        $date             = $request->input('date'); // تاریخ به فرمت میلادی
        $selectedClinicId = $request->selectedClinicId;

        // بررسی وجود نوبت برای تاریخ مورد نظر
        $appointments = Appointment::where('appointment_date', $date)
            ->where('status', 'scheduled')
            ->get();
        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            // اگر selectedClinicId برابر با 'default' باشد، clinic_id باید NULL یا خالی باشد
            $appointments->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            // اگر selectedClinicId مقدار داشت، clinic_id باید با آن مطابقت داشته باشد
            $appointments->where('clinic_id', $selectedClinicId);
        }

        // بررسی اگر هیچ نوبتی وجود ندارد
        $isHoliday = $appointments->isEmpty();
        return response()->json([
            'status'     => true,
            'is_holiday' => $isHoliday,
            'data'       => $appointments, // اگر نوبت وجود داشته باشد، ارسال می‌شود
        ]);
    }
    public function addHoliday(Request $request)
    {
        $validated = $request->validate([
            'date'             => 'required|date',
            'selectedClinicId' => 'nullable|string',
        ]);

        try {
            $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
            $selectedClinicId = $request->input('selectedClinicId');

            // بررسی وجود تعطیلی برای همان تاریخ و کلینیک
            $existingHolidayQuery = DoctorHoliday::where('doctor_id', $doctorId)
                ->whereJsonContains('holiday_dates', $validated['date']);

            if ($selectedClinicId === 'default') {
                $existingHolidayQuery->whereNull('clinic_id');
            } elseif ($selectedClinicId && $selectedClinicId !== 'default') {
                $existingHolidayQuery->where('clinic_id', $selectedClinicId);
            }

            $existingHoliday = $existingHolidayQuery->first();

            if ($existingHoliday) {
                return response()->json([
                    'status'  => false,
                    'message' => 'این تاریخ قبلاً به عنوان تعطیل ثبت شده است.',
                ]);
            }

            // ذخیره تعطیلی در جدول با کلینیک
            DoctorHoliday::create([
                'doctor_id'     => $doctorId,
                'clinic_id'     => ($selectedClinicId !== 'default' ? $selectedClinicId : null),
                'holiday_dates' => json_encode([$validated['date']]),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'روز موردنظر به‌عنوان تعطیل ثبت شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'خطا در ثبت تعطیلی.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleHoliday(Request $request)
    {
        $validated = $request->validate([
            'date'             => 'required|date',
            'selectedClinicId' => 'nullable|string',
        ]);

        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId');

        // بازیابی یا ایجاد رکورد تعطیلات با کلینیک
        $holidayRecordQuery = DoctorHoliday::where('doctor_id', $doctorId);

        if ($selectedClinicId === 'default') {
            $holidayRecordQuery->whereNull('clinic_id');
        } elseif ($selectedClinicId && $selectedClinicId !== 'default') {
            $holidayRecordQuery->where('clinic_id', $selectedClinicId);
        }

        $holidayRecord = $holidayRecordQuery->firstOrCreate([
            'doctor_id' => $doctorId,
            'clinic_id' => ($selectedClinicId !== 'default' ? $selectedClinicId : null),
        ], [
            'holiday_dates' => json_encode([])
        ]);

        // تبدیل JSON به آرایه
        $holidayDates = json_decode($holidayRecord->holiday_dates, true) ?? [];

        if (in_array($validated['date'], $holidayDates)) {
            // حذف تاریخ از تعطیلات
            $holidayDates = array_diff($holidayDates, [$validated['date']]);
            $message      = 'این تاریخ از حالت تعطیلی خارج شد.';
            $isHoliday    = false;
        } else {
            // اضافه کردن تاریخ به تعطیلات
            $holidayDates[] = $validated['date'];
            $message        = 'این تاریخ تعطیل شد.';
            $isHoliday      = true;
        }

        // به‌روزرسانی رکورد
        $holidayRecord->update([
            'holiday_dates' => json_encode(array_values($holidayDates)),
        ]);

        return response()->json([
            'status'        => true,
            'is_holiday'    => $isHoliday,
            'message'       => $message,
            'holiday_dates' => $holidayDates,
        ]);
    }

    public function getHolidays(Request $request)
    {
        try {
            $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
            $selectedClinicId = $request->input('selectedClinicId');

            // دریافت تعطیلات با شرط کلینیک
            $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId);

            if ($selectedClinicId === 'default') {
                $holidaysQuery->whereNull('clinic_id');
            } elseif ($selectedClinicId && $selectedClinicId !== 'default') {
                $holidaysQuery->where('clinic_id', $selectedClinicId);
            }

            $holidays = $holidaysQuery->get()->pluck('holiday_dates')->flatten()->toArray();

            return response()->json([
                'status'   => true,
                'holidays' => $holidays,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'خطا در دریافت داده‌ها.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

            // اعتبارسنجی داده‌های ورودی
            $validated = $request->validate([
                'day'        => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
                'start_time' => 'required|date_format:H:i',
                'end_time'   => 'required|date_format:H:i',
            ]);

            // دریافت رکورد ساعات کاری برای پزشک و روز مورد نظر
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $validated['day'])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if (! $workSchedule) {
                return response()->json([
                    'message' => 'ساعات کاری یافت نشد',
                    'status'  => false,
                ], 404);
            }

            // بررسی مقدار `work_hours` قبل از حذف
            $workHours = json_decode($workSchedule->work_hours, true);

            if (! is_array($workHours)) {
                return response()->json([
                    'message' => 'خطا در پردازش ساعات کاری',
                    'status'  => false,
                ], 500);
            }

            // 🟢 لاگ مقدار اولیه قبل از حذف

            // فیلتر بازه زمانی مشخص از `work_hours`
            $filteredWorkHours = array_filter($workHours, function ($slot) use ($validated) {
                return ! (
                    trim((string) $slot['start']) === trim((string) $validated['start_time']) &&
                    trim((string) $slot['end']) === trim((string) $validated['end_time'])
                );
            });

            // 🟢 لاگ مقدار بعد از حذف بازه

            // بررسی اینکه آیا تغییری رخ داده است
            if (count($filteredWorkHours) === count($workHours)) {
                return response()->json([
                    'message' => 'بازه زمانی یافت نشد یا قبلاً حذف شده است',
                    'status'  => false,
                ], 404);
            }

            // ذخیره تغییرات در `doctor_work_schedules`
            $workSchedule->work_hours = empty($filteredWorkHours) ? null : json_encode(array_values($filteredWorkHours));

            if (! $workSchedule->save()) {
                return response()->json([
                    'message' => 'خطا در ذخیره تغییرات',
                    'status'  => false,
                ], 500);
            }

            return response()->json([
                'message' => 'بازه زمانی با موفقیت حذف شد',
                'status'  => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در حذف بازه زمانی',
                'status'  => false,
            ], 500);
        }
    }

    public function getDefaultSchedule(Request $request)
    {
        $doctorId         = Auth::guard('doctor')->user()->id;
        $date             = $request->date;
        $selectedDate     = Carbon::parse($request->date); // تاریخ دریافتی در فرمت میلادی
        $selectedClinicId = $request->input('selectedClinicId');
        $dayOfWeek        = strtolower($selectedDate->format('l')); // دریافت نام روز (مثلاً saturday, sunday, ...)

        // Check for special schedule
        $specialScheduleQuery = SpecialDailySchedule::where('date', $date);
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $specialScheduleQuery->where('clinic_id', $selectedClinicId);
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
        $workScheduleQuery = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayOfWeek);
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $workScheduleQuery->where('clinic_id', $selectedClinicId);
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
    public function getWorkHours(Request $request)
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $date     = $request->input('date');

        // بررسی جدول جدید (special_daily_schedules)
        $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->first();

        if ($specialSchedule) {
            return response()->json([
                'status'     => true,
                'source'     => 'special_daily_schedules',
                'work_hours' => $specialSchedule->work_hours,
            ]);
        }

        // بررسی جدول قدیمی (doctor_work_schedules)
        $defaultSchedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', date('w', strtotime($date)))
            ->first();

        if ($defaultSchedule) {
            return response()->json([
                'status'     => true,
                'source'     => 'doctor_work_schedules',
                'work_hours' => json_decode($defaultSchedule->work_hours, true),
            ]);
        }

        return response()->json(['status' => false, 'message' => 'هیچ ساعت کاری برای این روز ثبت نشده است.']);
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
        $specialWorkHoursQuery = SpecialDailySchedule::where('date', $date);

        // اگر selectedClinicId وجود دارد و برابر 'default' نیست، فیلتر را اعمال کنید
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $specialWorkHoursQuery->where('clinic_id', $selectedClinicId);
        }

        $specialWorkHours = $specialWorkHoursQuery->first();

        if ($specialWorkHours) {
            // اگر وجود داشت، بروزرسانی شود
            $specialWorkHours->update(['work_hours' => json_encode($workHours)]);
        } else {
            // در غیر این صورت، رکورد جدید اضافه شود
            SpecialDailySchedule::create([
                'doctor_id'  => auth()->guard('doctor')->user()->id,
                'date'       => $date,
                'work_hours' => json_encode($workHours),
                'clinic_id'  => $selectedClinicId, // اضافه کردن clinic_id به رکورد جدید
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'ساعات کاری با موفقیت بروزرسانی شد.',
        ]);
    }

}
