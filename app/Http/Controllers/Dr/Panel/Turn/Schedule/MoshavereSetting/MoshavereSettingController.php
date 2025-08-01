<?php

namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\MoshavereSetting;

use App\Http\Controllers\Dr\Controller;
use App\Models\DoctorCounselingConfig;
use App\Models\DoctorCounselingWorkSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoshavereSettingController extends Controller
{
    /**
 * Display a listing of the resource.
 */
    public function index(Request $request)
    {
        try {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            // دریافت داده‌های ارسال‌شده از درخواست
            $inputDay = $request->input('day');
            $inputStartTime = $request->input('start_time');
            $inputEndTime = $request->input('end_time');
            $inputMaxAppointments = $request->input('max_appointments');

            // فیلتر کردن بر اساس داده‌های ارسال‌شده
            $workSchedules = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
                ->when($inputDay, function ($query) use ($inputDay) {
                    $query->where('day', $inputDay);
                })
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('medical_center_id', $selectedClinicId);
                    } else {
                        $query->whereNull('medical_center_id');
                    }
                })
                ->get();

            // بررسی یا ایجاد تنظیمات مشاوره آنلاین
            $appointmentConfig = DoctorCounselingConfig::firstOrCreate(
                ['doctor_id' => $doctorId, 'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null],
                [
                    'auto_scheduling'      => true,
                    'calendar_days'        => 30,
                    'online_consultation'  => false,
                    'holiday_availability' => false,
                    'has_phone_counseling' => false, // مقدار پیش‌فرض برای مشاوره تلفنی
                    'has_text_counseling'  => false, // مقدار پیش‌فرض برای مشاوره متنی
                    'has_video_counseling' => false, // مقدار پیش‌فرض برای مشاوره ویدیویی
                ]
            );

            return view('dr.panel.turn.schedule.moshavere_setting.index', [
                'appointmentConfig' => $appointmentConfig,
                'workSchedules'     => $workSchedules,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در دریافت تنظیمات',
                'status'  => false,
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */

    public function workhours(Request $request)
    {
        $doctorId         = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        $appointmentConfig = DoctorCounselingConfig::firstOrCreate(
            ['doctor_id' => $doctorId, 'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null],
            [
                'auto_scheduling'      => true,
                'online_consultation'  => false,
                'holiday_availability' => false,
            ]
        );

        $workSchedules = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('medical_center_id', $selectedClinicId);
                } else {
                    $query->whereNull('medical_center_id');
                }
            })
            ->get();

        return view("dr.panel.turn.schedule.scheduleSetting.workhours", [
            'appointmentConfig' => $appointmentConfig,
            'workSchedules'     => $workSchedules,
        ]);
    }

    public function copyWorkHours(Request $request)
    {
        $override  = filter_var($request->input('override', false), FILTER_VALIDATE_BOOLEAN);
        $validated = $request->validate([
            'source_day'  => 'required|string',
            'target_days' => 'required|array|min:1',
            'override'    => 'nullable|in:0,1,true,false',
        ]);
        $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        DB::beginTransaction();
        try {
            // دریافت ساعات کاری روز مبدأ
            $sourceWorkSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $validated['source_day'])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('medical_center_id', $selectedClinicId);
                    } else {
                        $query->whereNull('medical_center_id');
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
                $targetWorkSchedule = DoctorCounselingWorkSchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day'       => $targetDay,
                        'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
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
                'workSchedules' => DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                    ->whereIn('day', $validated['target_days']) // ارتباط با ساعات کاری‌ها
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
            $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $day)
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('medical_center_id', $selectedClinicId);
                    } else {
                        $query->whereNull('medical_center_id');
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
            $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $day)
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('medical_center_id', $selectedClinicId);
                    } else {
                        $query->whereNull('medical_center_id');
                    }
                })
                ->first();

            if (!$workSchedule) {
                $workSchedule = DoctorCounselingWorkSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $day,
                    'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
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

        $updatedSchedules = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('medical_center_id', $selectedClinicId);
                } else {
                    $query->whereNull('medical_center_id');
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
        $validated = $request->validate([
            'day' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        ]);
        $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $validated['day'])
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('medical_center_id', $selectedClinicId);
                } else {
                    $query->whereNull('medical_center_id');
                }
            })
            ->first();
        // بررسی اینکه آیا ساعات کاری به صورت JSON ذخیره شده است و مقدار دارد
        $hasSlots = $workSchedule && ! empty(json_decode($workSchedule->work_hours, true));
        return response()->json(['hasSlots' => $hasSlots]);
    }

    public function saveTimeSlot(Request $request)
    {
        $validated = $request->validate([
            'day'              => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'max_appointments' => 'required|integer|min:1',
        ]);

        $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        try {
            $workSchedule = DoctorCounselingWorkSchedule::firstOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'day'       => $validated['day'],
                    'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                ],
                ['is_working' => true, 'work_hours' => "[]"]// مقداردهی با رشته‌ی `[]`
            );

            // 🛠 اصلاح مشکل json_decode
            $existingWorkHours = is_string($workSchedule->work_hours) && ! empty($workSchedule->work_hours)
            ? json_decode($workSchedule->work_hours, true)
            : [];

            foreach ($existingWorkHours as $hour) {
                $existingStart = Carbon::createFromFormat('H:i', $hour['start']);
                $existingEnd   = Carbon::createFromFormat('H:i', $hour['end']);
                $newStart      = Carbon::createFromFormat('H:i', $validated['start_time']);
                $newEnd        = Carbon::createFromFormat('H:i', $validated['end_time']);

                if ($newStart->equalTo($existingStart) && $newEnd->equalTo($existingEnd)) {
                    return response()->json([
                        'message' => 'این بازه زمانی از قبل ثبت شده است.',
                        'status'  => false,
                    ], 400);
                }

                if (
                    $newStart->between($existingStart, $existingEnd, false) ||
                    $newEnd->between($existingStart, $existingEnd, false) ||
                    ($newStart->lte($existingStart) && $newEnd->gte($existingEnd))
                ) {
                    return response()->json([
                        'message' => 'این بازه زمانی با بازه‌های موجود تداخل دارد.',
                        'status'  => false,
                    ], 400);
                }
            }

            // اضافه کردن ساعت جدید به JSON
            $newSlot = [
                'start'            => $validated['start_time'],
                'end'              => $validated['end_time'],
                'max_appointments' => $validated['max_appointments'],
            ];
            $existingWorkHours[] = $newSlot;

            // بروزرسانی `work_hours`
            $workSchedule->update(['work_hours' => json_encode($existingWorkHours)]);

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

    public function updateWorkDayStatus(Request $request)
    {
        $validated = $request->validate([
            'day'        => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'is_working' => 'required|in:0,1,true,false',
        ]);
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        try {
            $doctor       = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $isWorking    = filter_var($validated['is_working'], FILTER_VALIDATE_BOOLEAN);
            $workSchedule = DoctorCounselingWorkSchedule::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'day'       => $validated['day'],
                    'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                ],
                [
                    'is_working' => $isWorking,
                ]
            );
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
        $validated = $request->validate([
            'auto_scheduling' => [
                'required',
                'in:0,1,true,false', // Explicitly allow these values
            ],
        ]);
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));
        // Convert to strict boolean
        $autoScheduling = filter_var($validated['auto_scheduling'], FILTER_VALIDATE_BOOLEAN);
        try {
            $doctor            = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $appointmentConfig = DoctorCounselingConfig::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                ],
                ['auto_scheduling' => $autoScheduling]
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

        $messages = [
                'day.required' => 'لطفاً روز را انتخاب کنید.',
                'day.in' => 'روز انتخاب شده معتبر نیست.',
                'start_time.required' => 'لطفاً ساعت شروع را وارد کنید.',
                'start_time.date_format' => 'فرمت ساعت شروع باید به صورت HH:MM باشد.',
                'end_time.required' => 'لطفاً ساعت پایان را وارد کنید.',
                'end_time.date_format' => 'فرمت ساعت پایان باید به صورت HH:MM باشد.',
                'end_time.after' => 'ساعت پایان باید بعد از ساعت شروع باشد.',
                'max_appointments.integer' => 'حداکثر تعداد نوبت‌ها باید عدد باشد.',
                'max_appointments.min' => 'حداکثر تعداد نوبت‌ها باید حداقل ۱ باشد.',
                'selected_days.required' => 'لطفاً روزهای انتخاب شده را مشخص کنید.',
                'selected_days.in' => 'روز انتخاب شده معتبر نیست.',
            ];

        $validated = $request->validate([
            'day'              => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'max_appointments' => 'nullable|integer|min:1',
            'selected_days'    => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        ], $messages);

        try {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            // تبدیل selected_days به آرایه
            $selectedDays = is_array($request->input('selected_days'))
            ? $request->input('selected_days')
            : explode(',', $request->input('selected_days'));
            $results = [];
            foreach ($selectedDays as $day) {
                // تنظیمات موجود برای روز
                $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                    ->where('day', $validated['day'])
                    ->where(function ($query) use ($selectedClinicId) {
                        if ($selectedClinicId !== 'default') {
                            $query->where('medical_center_id', $selectedClinicId);
                        } else {
                            $query->whereNull('medical_center_id');
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
                DoctorCounselingWorkSchedule::updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day'       => $validated['day'],
                        'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
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
        $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        // دریافت `id` از درخواست
        $id = $request->id;

        // بازیابی تنظیمات نوبت‌دهی برای پزشک
        $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $request->day)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('medical_center_id', $selectedClinicId);
                } else {
                    $query->whereNull('medical_center_id');
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

    /**
 * ذخیره تنظیمات برنامه کاری
 */
    public function saveWorkSchedule(Request $request)
    {
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));
        $validatedData = $request->validate([
            'auto_scheduling'      => 'boolean',
            'calendar_days'        => 'nullable|integer|min:1|max:365',
            'online_consultation'  => 'boolean',
            'holiday_availability' => 'boolean',
            'appointment_duration' => 'nullable|integer|min:5|max:120',
            'days'                 => 'array',
            'price_15min'          => 'nullable|integer|min:0',
            'price_30min'          => 'nullable|integer|min:0',
            'price_45min'          => 'nullable|integer|min:0',
            'price_60min'          => 'nullable|integer|min:0',
            'has_phone_counseling' => 'boolean',
            'has_text_counseling'  => 'boolean',
            'has_video_counseling' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $doctor = Auth::guard('doctor')->user();

            // ذخیره تنظیمات کلی
            $counselingConfig = DoctorCounselingConfig::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                ],
                [
                    'auto_scheduling'      => $validatedData['auto_scheduling'] ?? false,
                    'calendar_days'        => $request->input('calendar_days'),
                    'online_consultation'  => $validatedData['online_consultation'] ?? false,
                    'holiday_availability' => $validatedData['holiday_availability'] ?? false,
                    'appointment_duration' => $validatedData['appointment_duration'] ?? 15,
                    'price_15min'          => $validatedData['price_15min'],
                    'price_30min'          => $validatedData['price_30min'],
                    'price_45min'          => $validatedData['price_45min'],
                    'price_60min'          => $validatedData['price_60min'],
                    'has_phone_counseling' => $validatedData['has_phone_counseling'] ?? false,
                    'has_text_counseling'  => $validatedData['has_text_counseling'] ?? false,
                    'has_video_counseling' => $validatedData['has_video_counseling'] ?? false,
                ]
            );

            // به‌روزرسانی یا ایجاد برنامه کاری روزها
            foreach ($validatedData['days'] as $day => $dayConfig) {
                $slots = isset($dayConfig['slots']) ? array_map(function ($slot) {
                    return [
                        'start'            => $slot['start'],
                        'end'              => $slot['end'],
                        'max_appointments' => $slot['max_appointments'] ?? 1,
                    ];
                }, $dayConfig['slots']) : [];

                DoctorCounselingWorkSchedule::updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day'       => $day,
                        'medical_center_id' => $selectedClinicId !== 'default' ? $selectedClinicId : null,
                    ],
                    [
                        'is_working'           => $dayConfig['is_working'] ?? false,
                        'work_hours'           => !empty($slots) ? json_encode($slots) : null,
                        'appointment_settings' => json_encode($dayConfig['appointment_settings'] ?? []),
                    ]
                );
            }

            DB::commit();
            return response()->json([
                'message' => 'تنظیمات با موفقیت ذخیره شد.',
                'status'  => true,
                'data'    => [
                    'calendar_days'        => $counselingConfig->calendar_days,
                    'price_15min'          => $counselingConfig->price_15min,
                    'price_30min'          => $counselingConfig->price_30min,
                    'price_45min'          => $counselingConfig->price_45min,
                    'price_60min'          => $counselingConfig->price_60min,
                    'has_phone_counseling' => $counselingConfig->has_phone_counseling,
                    'has_text_counseling'  => $counselingConfig->has_text_counseling,
                    'has_video_counseling' => $counselingConfig->has_video_counseling,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطا در ذخیره‌سازی تنظیمات: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'خطا در ذخیره‌سازی تنظیمات: ' . $e->getMessage(),
                'status'  => false,
            ], 500);
        }
    }

    public function getAllDaysSettings(Request $request)
    {
        try {
            $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));
            // دریافت داده‌های ارسال‌شده از درخواست
            $inputDay             = $request->input('day');
            $inputStartTime       = $request->input('start_time');
            $inputEndTime         = $request->input('end_time');
            $inputMaxAppointments = $request->input('max_appointments');
            // فیلتر کردن بر اساس داده‌های ارسال‌شده
            $workSchedules = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                ->when($inputDay, function ($query) use ($inputDay) {
                    $query->where('day', $inputDay);
                })
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('medical_center_id', $selectedClinicId);
                    } else {
                        $query->whereNull('medical_center_id');
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
            $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $validated['day'])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('medical_center_id', $selectedClinicId);
                    } else {
                        $query->whereNull('medical_center_id');
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
        $doctor           = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        $workSchedules = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('medical_center_id', $selectedClinicId);
                } else {
                    $query->whereNull('medical_center_id');
                }
            })
            ->get();
        return response()->json([
            'workSchedules' => $workSchedules,
        ]);
    }

    // متدهای موجود در کنترلر اصلی

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
            $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $validated['day'])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('medical_center_id', $selectedClinicId);
                    } else {
                        $query->whereNull('medical_center_id');
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
                Log::error('❌ مقدار `work_hours` نامعتبر است:', ['work_hours' => $workSchedule->work_hours]);
                return response()->json([
                    'message' => 'خطا در پردازش ساعات کاری',
                    'status'  => false,
                ], 500);
            }

            // 🟢 لاگ مقدار اولیه قبل از حذف
            Log::info('🔍 مقدار اولیه `work_hours`:', ['work_hours' => $workHours]);

            // فیلتر بازه زمانی مشخص از `work_hours`
            $filteredWorkHours = array_filter($workHours, function ($slot) use ($validated) {
                return ! (
                    trim((string) $slot['start']) === trim((string) $validated['start_time']) &&
                    trim((string) $slot['end']) === trim((string) $validated['end_time'])
                );
            });

            // 🟢 لاگ مقدار بعد از حذف بازه
            Log::info('📌 مقدار `work_hours` بعد از حذف:', ['filtered_work_hours' => $filteredWorkHours]);

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
                Log::error('❌ خطا در ذخیره تغییرات در پایگاه داده');
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
            Log::error('❌ خطای حذف بازه زمانی:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'خطا در حذف بازه زمانی',
                'status'  => false,
            ], 500);
        }
    }

    public function getDefaultSchedule(Request $request)
    {
        $doctorId         = Auth::guard('doctor')->id();    // دریافت شناسه پزشک لاگین شده
        $dayOfWeek        = $request->input('day_of_week'); // دریافت شماره روز هفته
        $selectedClinicId = $request->query('selectedClinicId', $request->input('selectedClinicId', 'default'));

        // بررسی وجود برنامه کاری برای این روز
        $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayOfWeek) // بررسی روز هفته
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('medical_center_id', $selectedClinicId);
                } else {
                    $query->whereNull('medical_center_id');
                }
            })
            ->first();
        if ($workSchedule && ! empty($workSchedule->work_hours)) {
            return response()->json([
                'status'     => true,
                'work_hours' => json_decode($workSchedule->work_hours, true), // تبدیل JSON به آرایه
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => 'هیچ برنامه کاری‌ای برای این روز تنظیم نشده است.',
            ]);
        }
    }

}
