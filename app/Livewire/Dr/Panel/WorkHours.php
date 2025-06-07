<?php

namespace App\Livewire\Dr\Panel;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Clinic;
use Livewire\Component;
use App\Models\Appointment;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorAppointmentConfig;
use Illuminate\Support\Facades\Validator;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Models\Doctor;

class Workhours extends Component
{
    public $calculationMode = 'count'; // حالت پیش‌فرض: تعداد نوبت‌ها
    public $selectedClinicId; // برای صفحه قبلی (مثل schedule/setting)
    public $clinicId; // برای صفحه جدید (مثل activation/workhours/{clinic})
    public $activeClinicId; // پراپرتی مشترک برای کوئری‌ها
    public $appointmentConfig;
    public $workSchedules;
    public $selectedDay;
    public $startTime;
    public $endTime;
    public $slots;
    public $maxAppointments;
    public $isWorking = [];
    public $autoScheduling = false;
    public $calendarDays;
    public $onlineConsultation = false;
    public $holidayAvailability = false;
    public $sourceDay;
    public $targetDays = [];
    public $override = false;
    public $holidayDate;
    public $oldDate;
    public $newDate;
    public $appointmentIds = [];
    public $modalOpen = false;
    public $selectedDays = [];
    public $modalType = '';
    public $modalMessage = '';
    public $holidays = [];
    public $nextAvailableDate;
    public $scheduleModalDay;
    public $scheduleModalIndex;
    public $scheduleSettings = [];
    public $calculator = [
        'day' => null,
        'index' => null,
        'appointment_count' => null,
        'time_per_appointment' => null,
    ];
    public $copySource = [
        'day' => null,
        'index' => null,
    ];
    public $selectedCopyDays = [
        'saturday' => false,
        'sunday' => false,
        'monday' => false,
        'tuesday' => false,
        'wednesday' => false,
        'thursday' => false,
        'friday' => false,
    ];
    public $selectedScheduleDays = [];
    public $selectAllCopyModal = false;
    public $emergencyTimes = [];
    public $emergencyModalDay;
    public $emergencyModalIndex;
    public $isEmergencyModalOpen = false;
    public $selectAllScheduleModal = false;
    public $doctorId;
    public $doctor;

    protected $rules = [
        'selectedDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        'startTime' => 'required|date_format:H:i',
        'endTime' => 'required|date_format:H:i|after:startTime',
        'maxAppointments' => 'required|integer|min:1',
        'sourceDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        'targetDays' => 'required|array|min:1',
        'override' => 'boolean',
        'holidayDate' => 'required|date',
        'oldDate' => 'required',
        'newDate' => 'required|date_format:Y-m-d',
        'appointmentIds' => 'array',
        'scheduleModalDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        'scheduleModalIndex' => 'required|integer',
        'selectedScheduleDays' => 'required|array|min:1',
    ];

    protected $messages = [
        'selectedDay.required' => 'لطفاً یک روز را انتخاب کنید.',
        'startTime.required' => 'لطفاً زمان شروع را وارد کنید.',
        'startTime.date_format' => 'فرمت زمان شروع نامعتبر است.',
        'endTime.required' => 'لطفاً زمان پایان را وارد کنید.',
        'endTime.date_format' => 'فرمت زمان پایان نامعتبر است.',
        'endTime.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
        'maxAppointments.required' => 'لطفاً تعداد حداکثر نوبت‌ها را وارد کنید.',
        'maxAppointments.integer' => 'تعداد نوبت‌ها باید عدد باشد.',
        'maxAppointments.min' => 'تعداد نوبت‌ها باید حداقل ۱ باشد.',
        'sourceDay.required' => 'لطفاً روز مبدا را انتخاب کنید.',
        'targetDays.required' => 'لطفاً حداقل یک روز مقصد را انتخاب کنید.',
        'targetDays.min' => 'لطفاً حداقل یک روز مقصد را انتخاب کنید.',
        'holidayDate.required' => 'لطفاً تاریخ تعطیلی را وارد کنید.',
        'oldDate.required' => 'لطفاً تاریخ قدیمی را وارد کنید.',
        'newDate.required' => 'لطفاً تاریخ جدید را وارد کنید.',
        'newDate.date_format' => 'فرمت تاریخ جدید نامعتبر است.',
        'scheduleModalDay.required' => 'لطفاً یک روز را برای زمان‌بندی انتخاب کنید.',
        'scheduleModalIndex.required' => 'اندیس زمان‌بندی نامعتبر است.',
        'selectedScheduleDays.required' => 'لطفاً حداقل یک روز را انتخاب کنید.',
        'selectedScheduleDays.min' => 'لطفاً حداقل یک روز را انتخاب کنید.',
    ];

    public function mount($clinicId = null)
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }

        $this->doctorId = $doctor instanceof \App\Models\Secretary ? $doctor->doctor_id : $doctor->id;
        $this->doctor = Doctor::with(['clinics', 'workSchedules'])->find($this->doctorId);

        if (!$this->doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'اطلاعات پزشک یافت نشد.');
        }

        $this->clinicId = $clinicId;
        $this->selectedClinicId = request()->query('selectedClinicId', session('selectedClinicId', 'default'));
        $this->activeClinicId = $this->clinicId ?? $this->selectedClinicId;
        session(['selectedClinicId' => $this->selectedClinicId]);

        // یک درخواست برای دریافت همه تنظیمات
        $this->appointmentConfig = DoctorAppointmentConfig::withoutGlobalScopes()
            ->firstOrCreate(
                [
                    'doctor_id' => $this->doctorId,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                ],
                [
                    'auto_scheduling' => true,
                    'online_consultation' => false,
                    'holiday_availability' => false,
                ]
            );

        // یک درخواست برای ایجاد/به‌روزرسانی همه روزها
        $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $existingSchedules = DoctorWorkSchedule::withoutGlobalScopes()
            ->where('doctor_id', $this->doctorId)
            ->where(function ($query) {
                if ($this->activeClinicId !== 'default') {
                    $query->where('clinic_id', $this->activeClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->get()
            ->keyBy('day');

        $missingDays = array_diff($daysOfWeek, $existingSchedules->keys()->toArray());

        if (!empty($missingDays)) {
            $newSchedules = collect($missingDays)->map(function ($day) {
                return [
                    'doctor_id' => $this->doctorId,
                    'day' => $day,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                    'is_working' => false,
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ];
            })->toArray();

            DoctorWorkSchedule::insert($newSchedules);
        }

        $this->refreshWorkSchedules();

        $this->isWorking = array_fill_keys($daysOfWeek, false);
        $this->slots = array_fill_keys($daysOfWeek, []);

        // Lazy load slots
        foreach ($daysOfWeek as $day) {
            $schedule = collect($this->workSchedules)->firstWhere('day', $day);
            if ($schedule) {
                $this->isWorking[$day] = (bool) $schedule['is_working'];
                $workHours = !empty($schedule['work_hours']) ? $schedule['work_hours'] : [];
                if (!empty($workHours)) {
                    foreach ($workHours as $index => $slot) {
                        $this->slots[$day][$index] = [
                            'id' => $schedule['id'] . '-' . $index,
                            'start_time' => $slot['start'] ?? null,
                            'end_time' => $slot['end'] ?? null,
                            'max_appointments' => $slot['max_appointments'] ?? null,
                        ];
                    }
                }
            }
            if (empty($this->slots[$day])) {
                $this->slots[$day][] = [
                    'id' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'max_appointments' => null,
                ];
            }
        }

        $this->autoScheduling = $this->appointmentConfig->auto_scheduling;
        $this->calendarDays = $this->appointmentConfig->calendar_days ?? 30;
        $this->onlineConsultation = $this->appointmentConfig->online_consultation;
        $this->holidayAvailability = $this->appointmentConfig->holiday_availability;

        $this->selectedScheduleDays = array_fill_keys($daysOfWeek, false);

        $this->dispatch('refresh-clinic-data');
    }

    public function setSelectedClinicId($clinicId)
    {
        $this->selectedClinicId = $clinicId;
        $this->activeClinicId = $this->clinicId ?? $clinicId;
        session(['selectedClinicId' => $clinicId]);
        $this->reset(['workSchedules', 'isWorking', 'slots']);
        $this->mount($this->clinicId);
        $this->dispatch('refresh-clinic-data');
    }

    public function forceRefreshSettings()
    {
        $this->dispatch('refresh-schedule-settings');
    }

    public function openScheduleModal($day, $index)
    {
        $this->scheduleModalDay = $day;
        $this->scheduleModalIndex = $index;
        $this->selectedScheduleDays = array_fill_keys(
            ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            false
        );

        $schedule = DoctorWorkSchedule::where('doctor_id', $this->doctorId)
            ->where('day', $day)
            ->where(function ($query) {
                if ($this->activeClinicId !== 'default') {
                    $query->where('clinic_id', $this->activeClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->first();

        if ($schedule && $schedule->appointment_settings) {
            $settings = is_array($schedule->appointment_settings)
                ? $schedule->appointment_settings
                : json_decode($schedule->appointment_settings, true) ?? [];

            foreach ($settings as $setting) {
                if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$index) {
                    foreach ($setting['days'] as $d) {
                        $this->selectedScheduleDays[$d] = true;
                    }
                }
            }
        }

        $this->refreshWorkSchedules();
        $this->dispatch('refresh-schedule-settings');
    }

    public function updatedSelectAllScheduleModal($value)
    {
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            $this->selectedScheduleDays[$day] = $value;
        }
    }

    public function saveSchedule($startTime, $endTime)
    {
        try {
            $validator = Validator::make([
                'startTime' => $startTime,
                'endTime' => $endTime,
                'selectedScheduleDays' => $this->selectedScheduleDays,
                'scheduleModalDay' => $this->scheduleModalDay,
                'scheduleModalIndex' => $this->scheduleModalIndex,
            ], [
                'scheduleModalDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
                'scheduleModalIndex' => 'required|integer',
                'selectedScheduleDays' => 'required|array|min:1',
                'startTime' => 'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
            ], [
                'scheduleModalDay.required' => 'لطفاً یک روز را برای زمان‌بندی انتخاب کنید.',
                'scheduleModalIndex.required' => 'اندیس زمان‌بندی نامعتبر است.',
                'selectedScheduleDays.required' => 'لطفاً حداقل یک روز انتخاب کنید.',
                'selectedScheduleDays.min' => 'لطفاً حداقل یک روز انتخاب کنید.',
                'startTime.required' => 'لطفاً زمان شروع را وارد کنید.',
                'startTime.date_format' => 'فرمت زمان شروع نامعتبر است.',
                'endTime.required' => 'لطفاً زمان پایان را وارد کنید.',
                'endTime.date_format' => 'فرمت زمان پایان نامعتبر است.',
                'endTime.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $this->modalMessage = implode(' ', $errors);
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'error',
                ]);
                return;
            }

            $days = array_keys(array_filter($this->selectedScheduleDays));
            $timeToMinutes = function ($time) {
                [$hours, $minutes] = explode(':', $time);
                return (int)$hours * 60 + (int)$minutes;
            };

            $newStartMinutes = $timeToMinutes($startTime);
            $newEndMinutes = $timeToMinutes($endTime);

            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            $dayTranslations = [
                'saturday' => 'شنبه',
                'sunday' => 'یکشنبه',
                'monday' => 'دوشنبه',
                'tuesday' => 'سه‌شنبه',
                'wednesday' => 'چهارشنبه',
                'thursday' => 'پنج‌شنبه',
                'friday' => 'جمعه',
            ];

            foreach ($days as $day) {
                $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                    ->where('day', $day)
                    ->where(function ($query) {
                        if ($this->activeClinicId !== 'default') {
                            $query->where('clinic_id', $this->activeClinicId);
                        } else {
                            $query->whereNull('clinic_id');
                        }
                    })
                    ->first();

                if ($schedule && $schedule->appointment_settings) {
                    $settings = json_decode($schedule->appointment_settings, true) ?? [];
                    foreach ($settings as $setting) {
                        if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->scheduleModalIndex) {
                            continue;
                        }
                        $existingStartMinutes = $timeToMinutes($setting['start_time']);
                        $existingEndMinutes = $timeToMinutes($setting['end_time']);

                        if (
                            ($newStartMinutes >= $existingStartMinutes && $newStartMinutes < $existingEndMinutes) ||
                            ($newEndMinutes > $existingStartMinutes && $newEndMinutes <= $existingEndMinutes) ||
                            ($newStartMinutes <= $existingStartMinutes && $newEndMinutes >= $existingEndMinutes)
                        ) {
                            throw new \Exception(
                                "تداخل زمانی در روز {$dayTranslations[$day]} با بازه {$setting['start_time']} تا {$setting['end_time']} وجود دارد. لطفاً زمان دیگری انتخاب کنید."
                            );
                        }
                    }
                }
            }

            $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $this->scheduleModalDay)
                ->where(function ($query) {
                    if ($this->activeClinicId !== 'default') {
                        $query->where('clinic_id', $this->activeClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if (!$schedule) {
                $schedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctorId,
                    'day' => $this->scheduleModalDay,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                    'appointment_settings' => json_encode([]),
                ]);
            }

            $appointmentSettings = json_decode($schedule->appointment_settings, true) ?? [];
            $newSetting = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'days' => $days,
                'work_hour_key' => (int)$this->scheduleModalIndex,
            ];

            $existingIndex = array_search(
                (int)$this->scheduleModalIndex,
                array_column($appointmentSettings, 'work_hour_key')
            );

            if ($existingIndex !== false) {
                $appointmentSettings[$existingIndex] = $newSetting;
            } else {
                $appointmentSettings[] = $newSetting;
            }

            $schedule->update(['appointment_settings' => json_encode(array_values($appointmentSettings))]);

            $this->refreshWorkSchedules();

            $this->modalMessage = 'تنظیم زمان‌بندی با موفقیت ذخیره شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);
            $this->dispatch('close-schedule-modal');
        } catch (\Exception $e) {
            $this->modalMessage = $e->getMessage();
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    public function deleteScheduleSetting($day, $index)
    {
        try {
            $this->validate([
                'scheduleModalDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            ]);

            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $day)
                ->where(function ($query) {
                    if ($this->activeClinicId !== 'default') {
                        $query->where('clinic_id', $this->activeClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if (!$schedule) {
                throw new \Exception('برنامه کاری برای این روز یافت نشد');
            }

            $appointmentSettings = json_decode($schedule->appointment_settings, true) ?? [];
            $filteredKeys = array_keys(array_filter(
                $appointmentSettings,
                fn ($setting) => isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->scheduleModalIndex
            ));

            if (!isset($filteredKeys[$index])) {
                throw new \Exception('تنظیم انتخاب‌شده یافت نشد');
            }

            $actualIndex = $filteredKeys[$index];
            unset($appointmentSettings[$actualIndex]);
            $appointmentSettings = array_values($appointmentSettings);

            $schedule->update(['appointment_settings' => json_encode($appointmentSettings)]);

            $this->refreshWorkSchedules();

            $this->modalMessage = 'تنظیم زمان‌بندی با موفقیت حذف شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);
            $this->dispatch('refresh-schedule-settings');
        } catch (\Exception $e) {
            $this->modalMessage = $e->getMessage();
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    public function refreshWorkSchedules()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        // Optimize query by selecting only needed fields and using eager loading
        $this->workSchedules = DoctorWorkSchedule::withoutGlobalScopes()
            ->select(['id', 'day', 'is_working', 'work_hours', 'appointment_settings', 'emergency_times'])
            ->where('doctor_id', $doctorId)
            ->where(function ($query) {
                if ($this->activeClinicId !== 'default') {
                    $query->where('clinic_id', $this->activeClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'is_working' => (bool) $schedule->is_working,
                    'work_hours' => is_array($schedule->work_hours) ? $schedule->work_hours : json_decode($schedule->work_hours, true) ?? [],
                    'appointment_settings' => is_array($schedule->appointment_settings) ? $schedule->appointment_settings : json_decode($schedule->appointment_settings, true) ?? [],
                    'emergency_times' => is_array($schedule->emergency_times) ? $schedule->emergency_times : json_decode($schedule->emergency_times, true) ?? [],
                ];
            })
            ->toArray();
    }

    public function closeScheduleModal()
    {
        $this->reset(['scheduleModalDay', 'scheduleModalIndex', 'scheduleSettings']);
        $this->dispatch('close-schedule-modal');
    }

    public function updatedSelectAllCopyModal($value)
    {
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            if ($day !== $this->sourceDay) {
                $this->selectedDays[$day] = $value;
            }
        }
    }

    public function saveEmergencyTimes()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $this->emergencyModalDay)
            ->where(function ($query) {
                if ($this->activeClinicId !== 'default') {
                    $query->where('clinic_id', $this->activeClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->first();

        if ($schedule) {
            $schedule->emergency_times = json_encode($this->emergencyTimes);
            $schedule->save();
            $this->refreshWorkSchedules();

            $this->modalMessage = 'زمان‌های اورژانسی با موفقیت ذخیره شدند';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => 'زمان‌های اورژانسی با موفقیت ذخیره شدند',
                'type' => 'success',
            ]);
        } else {
            $this->modalMessage = 'برنامه کاری برای این روز یافت نشد';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => 'برنامه کاری برای این روز یافت نشد',
                'type' => 'error',
            ]);
        }

        $this->isEmergencyModalOpen = false;
        $this->dispatch('close-emergency-modal');
    }

    public function saveWorkSchedule()
    {
        try {
            $this->validate([
                'calendarDays' => 'required|integer|min:1',
                'holidayAvailability' => 'boolean',
                'autoScheduling' => 'boolean',
                'onlineConsultation' => 'boolean',
            ], [
                'calendarDays.required' => 'تعداد روزهای تقویم الزامی است',
                'calendarDays.integer' => 'تعداد روزهای تقویم باید عدد باشد',
                'calendarDays.min' => 'تعداد روزهای تقویم باید حداقل ۱ باشد',
                'holidayAvailability.boolean' => 'مقدار در دسترس بودن در تعطیلات نامعتبر است',
                'autoScheduling.boolean' => 'مقدار نوبت‌دهی خودکار نامعتبر است',
                'onlineConsultation.boolean' => 'مقدار مشاوره آنلاین نامعتبر است',
            ]);

            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            DB::beginTransaction();
            try {
                $config = DoctorAppointmentConfig::withoutGlobalScopes()
                    ->updateOrCreate(
                        [
                            'doctor_id' => $doctorId,
                            'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                        ],
                        [
                            'calendar_days' => (int) $this->calendarDays,
                            'holiday_availability' => (bool) $this->holidayAvailability,
                            'auto_scheduling' => (bool) $this->autoScheduling,
                            'online_consultation' => (bool) $this->onlineConsultation,
                        ]
                    );

                // به‌روزرسانی تنظیمات در حافظه
                $this->appointmentConfig = $config;
                $this->calendarDays = (int) $this->calendarDays;
                $this->holidayAvailability = (bool) $this->holidayAvailability;
                $this->autoScheduling = (bool) $this->autoScheduling;
                $this->onlineConsultation = (bool) $this->onlineConsultation;

                DB::commit();

                $this->modalMessage = 'تنظیمات با موفقیت ذخیره شد';
                $this->modalType = 'success';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'success',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();
            $errorMessage = implode('، ', $errorMessages);
            $this->modalMessage = $errorMessage ?: 'لطفاً تمام فیلدهای مورد نیاز را پر کنید';
            $this->modalType = 'error';
            $this->modalOpen = true;
            Log::error('Validation error in saveWorkSchedule: ' . $errorMessage);
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in saveWorkSchedule: ' . $e->getMessage(), [
                'doctor_id' => $doctorId ?? null,
                'calendar_days' => $this->calendarDays ?? null,
                'holiday_availability' => $this->holidayAvailability ?? null,
                'auto_scheduling' => $this->autoScheduling ?? null,
                'online_consultation' => $this->onlineConsultation ?? null
            ]);

            $this->modalMessage = $e->getMessage() ?: 'خطا در ذخیره تنظیمات';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    public $storedCopySource = [];
    public $storedSelectedDays = [];

    public function copySchedule($replace = false)
    {
        try {
            if (!$replace && !empty($this->copySource['day'])) {
                $this->storedCopySource = $this->copySource;
                $this->storedSelectedDays = $this->selectedDays;
            }

            $copySource = $replace || empty($this->copySource['day']) ? $this->storedCopySource : $this->copySource;
            $selectedDays = $replace || empty(array_filter($this->selectedDays)) ? $this->storedSelectedDays : $this->selectedDays;

            if (!isset($copySource['day']) || !in_array($copySource['day'], ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday']) || !isset($copySource['index']) || !is_numeric($copySource['index']) || $copySource['index'] < 0) {
                throw new \Exception('داده‌های منبع کپی نامعتبر است');
            }

            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
            $sourceDay = $copySource['day'];
            $sourceIndex = (int) $copySource['index'];

            if (empty(array_filter($selectedDays))) {
                throw new \Exception('هیچ روزی برای کپی انتخاب نشده است');
            }

            $sourceSchedule = collect($this->workSchedules)->firstWhere('day', $sourceDay);
            if (!$sourceSchedule) {
                throw new \Exception('برنامه کاری برای روز مبدا یافت نشد');
            }

            $sourceWorkHours = !empty($sourceSchedule['work_hours']) ? $sourceSchedule['work_hours'] : [];
            $sourceAppointmentSettings = !empty($sourceSchedule['appointment_settings']) ? $sourceSchedule['appointment_settings'] : [];
            $sourceEmergencyTimes = !empty($sourceSchedule['emergency_times']) ? $sourceSchedule['emergency_times'] : [];

            if (empty($sourceWorkHours[$sourceIndex])) {
                throw new \Exception('اسلات انتخاب‌شده برای کپی یافت نشد');
            }

            $sourceSlot = $sourceWorkHours[$sourceIndex];
            $conflicts = [];
            $selectedTargetDays = array_keys(array_filter($selectedDays));

            DB::beginTransaction();
            try {
                foreach ($selectedTargetDays as $targetDay) {
                    if ($targetDay === $sourceDay) {
                        continue;
                    }

                    $targetSchedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                        ->where('day', $targetDay)
                        ->where(function ($query) {
                            if ($this->activeClinicId !== 'default') {
                                $query->where('clinic_id', $this->activeClinicId);
                            } else {
                                $query->whereNull('clinic_id');
                            }
                        })
                        ->first();

                    if ($targetSchedule) {
                        $targetWorkHours = $targetSchedule->work_hours ? json_decode($targetSchedule->work_hours, true) : [];
                        $targetEmergencyTimes = $targetSchedule->emergency_times ? json_decode($targetSchedule->emergency_times, true) : [];

                        if (!$replace && (!empty($targetWorkHours) || !empty($targetEmergencyTimes))) {
                            $conflictDetails = [];
                            foreach ($targetWorkHours as $slot) {
                                if ($this->isTimeConflict($sourceSlot['start'], $sourceSlot['end'], $slot['start'], $slot['end'])) {
                                    $conflictDetails['work_hours'][] = [
                                        'start' => $slot['start'],
                                        'end' => $slot['end'],
                                        'max_appointments' => $slot['max_appointments'] ?? null,
                                    ];
                                }
                            }
                            if (!empty($targetEmergencyTimes)) {
                                $conflictDetails['emergency_times'] = $targetEmergencyTimes;
                            }
                            if (!empty($conflictDetails)) {
                                $conflicts[$targetDay] = $conflictDetails;
                            }
                        }
                    }
                }

                if (!empty($conflicts) && !$replace) {
                    $this->dispatch('show-conflict-alert', ['conflicts' => $conflicts]);
                    $this->dispatch('close-checkbox-modal');
                    return;
                }

                $daysToCopy = $replace ? array_diff($selectedTargetDays, [$sourceDay]) : array_diff($selectedTargetDays, array_keys($conflicts), [$sourceDay]);

                if (empty($daysToCopy)) {
                    throw new \Exception('هیچ روزی برای کپی بدون تداخل یافت نشد');
                }

                foreach ($daysToCopy as $targetDay) {
                    $targetSchedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                        ->where('day', $targetDay)
                        ->where(function ($query) {
                            if ($this->activeClinicId !== 'default') {
                                $query->where('clinic_id', $this->activeClinicId);
                            } else {
                                $query->whereNull('clinic_id');
                            }
                        })
                        ->first();

                    if (!$targetSchedule) {
                        $targetSchedule = DoctorWorkSchedule::create([
                            'doctor_id' => $doctorId,
                            'day' => $targetDay,
                            'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                            'is_working' => true,
                            'work_hours' => json_encode([$sourceSlot]),
                            'appointment_settings' => json_encode($sourceAppointmentSettings),
                            'emergency_times' => json_encode($sourceEmergencyTimes),
                        ]);
                    } else {
                        $targetWorkHours = json_decode($targetSchedule->work_hours, true) ?? [];
                        if ($replace) {
                            $targetWorkHours = array_filter($targetWorkHours, fn ($slot) => !$this->isTimeConflict($sourceSlot['start'], $sourceSlot['end'], $slot['start'], $slot['end']));
                        }
                        $targetWorkHours[] = $sourceSlot;
                        $targetSchedule->update([
                            'work_hours' => json_encode($targetWorkHours),
                            'appointment_settings' => json_encode($sourceAppointmentSettings),
                            'emergency_times' => json_encode($sourceEmergencyTimes),
                            'is_working' => true,
                        ]);
                    }
                }

                $this->refreshWorkSchedules();

                $this->slots = [];
                $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                foreach ($daysOfWeek as $day) {
                    $schedule = collect($this->workSchedules)->firstWhere('day', $day);
                    if ($schedule) {
                        $this->isWorking[$day] = (bool) $schedule['is_working'];
                        $workHours = !empty($schedule['work_hours']) ? $schedule['work_hours'] : [];
                        if (!empty($workHours)) {
                            foreach ($workHours as $index => $slot) {
                                $this->slots[$day][$index] = [
                                    'id' => $schedule['id'] . '-' . $index,
                                    'start_time' => $slot['start'] ?? null,
                                    'end_time' => $slot['end'] ?? null,
                                    'max_appointments' => $slot['max_appointments'] ?? null,
                                ];
                            }
                        }
                    }
                    if (empty($this->slots[$day])) {
                        $this->slots[$day][] = [
                            'id' => null,
                            'start_time' => null,
                            'end_time' => null,
                            'max_appointments' => null,
                        ];
                    }
                }

                DB::commit();

                $this->selectedDays = [];
                $this->selectAllCopyModal = false;
                $this->modalMessage = $replace ? 'برنامه کاری با موفقیت جایگزین شد' : 'برنامه کاری با موفقیت کپی شد';
                $this->modalType = 'success';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'success',
                ]);
                $this->dispatch('close-checkbox-modal');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error in copySchedule: ' . $e->getMessage(), [
                'copy_source' => $copySource ?? null,
                'selected_days' => $selectedDays ?? null,
                'doctor_id' => $doctorId ?? null
            ]);

            $this->modalMessage = $e->getMessage() ?: 'خطا در کپی برنامه کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    private function isTimeConflict($newStart, $newEnd, $existingStart, $existingEnd)
    {
        try {
            if (empty($newStart) || empty($newEnd) || empty($existingStart) || empty($existingEnd)) {
                return false;
            }

            $newStartTime = Carbon::createFromFormat('H:i', $newStart);
            $newEndTime = Carbon::createFromFormat('H:i', $newEnd);
            $existingStartTime = Carbon::createFromFormat('H:i', $existingStart);
            $existingEndTime = Carbon::createFromFormat('H:i', $existingEnd);

            if (!$newStartTime || !$newEndTime || !$existingStartTime || !$existingEndTime) {
                return false;
            }

            return (
                ($newStartTime < $existingEndTime && $newEndTime > $existingStartTime) ||
                ($newStartTime == $existingStartTime && $newEndTime == $existingEndTime)
            );
        } catch (\Exception $e) {
            Log::error('Error in isTimeConflict: ' . $e->getMessage(), [
                'new_start' => $newStart,
                'new_end' => $newEnd,
                'existing_start' => $existingStart,
                'existing_end' => $existingEnd
            ]);
            return false;
        }
    }

    public function openCopyModal($day)
    {
        try {
            if (!in_array($day, ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'])) {
                throw new \Exception('روز نامعتبر است');
            }

            $this->sourceDay = $day;
            $this->selectAllCopyModal = false;
            $this->selectedDays = array_fill_keys(
                ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                false
            );
            $this->dispatchBrowserEvent('open-checkbox-modal');
        } catch (\Exception $e) {
            Log::error('Error in openCopyModal: ' . $e->getMessage(), [
                'day' => $day
            ]);

            $this->modalMessage = $e->getMessage() ?: 'خطا در باز کردن مودال کپی';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    protected function timeToMinutes($time)
    {
        try {
            if (empty($time)) {
                return 0;
            }

            if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
                throw new \Exception('فرمت زمان نامعتبر است');
            }

            [$hours, $minutes] = explode(':', $time);
            $totalMinutes = ((int)$hours * 60) + (int)$minutes;

            if ($totalMinutes < 0 || $totalMinutes > 1440) { // 24 * 60 = 1440 minutes in a day
                throw new \Exception('زمان خارج از محدوده مجاز است');
            }

            return $totalMinutes;
        } catch (\Exception $e) {
            Log::error('Error in timeToMinutes: ' . $e->getMessage(), [
                'time' => $time
            ]);
            return 0;
        }
    }

    public function updatedCalculatorAppointmentCount($value)
    {
        if ($value && !is_nan($value) && $value > 0) {
            $startTime = $this->calculator['start_time'] ?? null;
            $endTime = $this->calculator['end_time'] ?? null;
            if ($startTime && $endTime) {
                $totalMinutes = $this->timeToMinutes($endTime) - $this->timeToMinutes($startTime);
                if ($totalMinutes > 0) {
                    $this->calculator['time_per_appointment'] = round($totalMinutes / $value);
                    $this->calculationMode = 'count';
                } else {
                    $this->calculator['time_per_appointment'] = null;
                    $this->modalMessage = 'زمان پایان باید بعد از زمان شروع باشد';
                    $this->modalType = 'error';
                    $this->modalOpen = true;
                    $this->dispatch('show-toastr', ['message' => 'زمان پایان باید بعد از زمان شروع باشد', 'type' => 'error']);
                }
            } else {
                $this->calculator['time_per_appointment'] = null;
                $this->modalMessage = 'زمان شروع یا پایان وارد نشده است';
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', ['message' => 'زمان شروع یا پایان وارد نشده است', 'type' => 'error']);
            }
        } else {
            $this->calculator['time_per_appointment'] = null;
        }
    }

    public function updatedCalculatorTimePerAppointment($value)
    {
        if ($value && !is_nan($value) && $value > 0) {
            $startTime = $this->calculator['start_time'] ?? null;
            $endTime = $this->calculator['end_time'] ?? null;
            if ($startTime && $endTime) {
                $totalMinutes = $this->timeToMinutes($endTime) - $this->timeToMinutes($startTime);
                if ($totalMinutes > 0) {
                    $this->calculator['appointment_count'] = round($totalMinutes / $value);
                    $this->calculationMode = 'time';
                } else {
                    $this->calculator['appointment_count'] = null;
                    $this->modalMessage = 'زمان پایان باید بعد از زمان شروع باشد';
                    $this->modalType = 'error';
                    $this->modalOpen = true;
                    $this->dispatch('show-toastr', ['message' => 'زمان پایان باید بعد از زمان شروع باشد', 'type' => 'error']);
                }
            } else {
                $this->calculator['appointment_count'] = null;
                $this->modalMessage = 'زمان شروع یا پایان وارد نشده است';
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', ['message' => 'زمان شروع یا پایان وارد نشده است', 'type' => 'error']);
            }
        } else {
            $this->calculator['appointment_count'] = null;
        }
    }

    private function getPersianDay($englishDay)
    {
        $dayMap = [
            'saturday' => 'شنبه',
            'sunday' => 'یکشنبه',
            'monday' => 'دوشنبه',
            'tuesday' => 'سه‌شنبه',
            'wednesday' => 'چهارشنبه',
            'thursday' => 'پنج‌شنبه',
            'friday' => 'جمعه',
        ];
        return $dayMap[$englishDay] ?? $englishDay;
    }

    public function saveCalculator()
    {
        try {
            $this->validate([
                'calculator.day' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
                'calculator.index' => 'required|integer',
                'calculator.appointment_count' => 'required|integer|min:1',
                'calculator.start_time' => 'required|date_format:H:i',
                'calculator.end_time' => 'required|date_format:H:i|after:calculator.start_time',
            ], [
                'calculator.day.required' => 'لطفاً یک روز را انتخاب کنید.',
                'calculator.day.in' => 'روز انتخاب‌شده نامعتبر است.',
                'calculator.index.required' => 'لطفاً ایندکس را وارد کنید.',
                'calculator.index.integer' => 'ایندکس باید یک عدد صحیح باشد.',
                'calculator.appointment_count.required' => 'لطفاً تعداد حداکثر نوبت‌ها را وارد کنید.',
                'calculator.appointment_count.integer' => 'تعداد نوبت‌ها باید یک عدد صحیح باشد.',
                'calculator.appointment_count.min' => 'تعداد نوبت‌ها باید حداقل ۱ باشد.',
                'calculator.start_time.required' => 'لطفاً زمان شروع را وارد کنید.',
                'calculator.start_time.date_format' => 'فرمت زمان شروع باید به صورت ساعت:دقیقه باشد.',
                'calculator.end_time.required' => 'لطفاً زمان پایان را وارد کنید.',
                'calculator.end_time.date_format' => 'فرمت زمان پایان باید به صورت ساعت:دقیقه باشد.',
                'calculator.end_time.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
            ]);

            $day = $this->calculator['day'];
            $index = $this->calculator['index'];
            $appointmentCount = $this->calculator['appointment_count'];
            $startTime = $this->calculator['start_time'];
            $endTime = $this->calculator['end_time'];
            $newSlot = [
                'start' => $startTime,
                'end' => $endTime,
                'max_appointments' => $appointmentCount,
            ];

            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            DB::beginTransaction();
            try {
                $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                    ->where('day', $day)
                    ->where(function ($query) {
                        if ($this->activeClinicId !== 'default') {
                            $query->where('clinic_id', $this->activeClinicId);
                        } else {
                            $query->whereNull('clinic_id');
                        }
                    })
                    ->first();

                if (!$workSchedule) {
                    $workSchedule = DoctorWorkSchedule::create([
                        'doctor_id' => $doctorId,
                        'day' => $day,
                        'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                        'is_working' => true,
                        'work_hours' => json_encode([]),
                    ]);
                }

                $workHours = json_decode($workSchedule->work_hours, true) ?? [];
                $slotExists = array_filter($workHours, function ($slot, $i) use ($newSlot, $index) {
                    return $i !== $index && $slot['start'] === $newSlot['start'] && $slot['end'] === $newSlot['end'];
                }, ARRAY_FILTER_USE_BOTH);

                if (!empty($slotExists)) {
                    throw new \Exception(sprintf(
                        'بازه زمانی %s تا %s قبلاً برای روز %s ثبت شده است',
                        $newSlot['start'],
                        $newSlot['end'],
                        $this->getPersianDay($day)
                    ));
                }

                foreach ($workHours as $i => $slot) {
                    if ($i === $index) {
                        continue;
                    }
                    if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                        throw new \Exception(sprintf(
                            'زمان %s تا %s با بازه زمانی موجود %s تا %s در روز %s تداخل دارد',
                            $newSlot['start'],
                            $newSlot['end'],
                            $slot['start'],
                            $slot['end'],
                            $this->getPersianDay($day)
                        ));
                    }
                }

                $workHours[$index] = $newSlot;
                $workSchedule->update(['work_hours' => json_encode($workHours), 'is_working' => true]);

                $this->slots[$day][$index] = [
                    'id' => $workSchedule->id . '-' . $index,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'max_appointments' => $appointmentCount,
                ];

                // بهینه‌سازی: ذخیره تنظیمات نوبت‌دهی برای تمام روزهای هفته در یک تراکنش
                $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                $newSetting = [
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                    'days' => $daysOfWeek,
                    'work_hour_key' => (int)$index,
                ];

                $schedules = DoctorWorkSchedule::where('doctor_id', $doctorId)
                    ->whereIn('day', $daysOfWeek)
                    ->where(function ($query) {
                        if ($this->activeClinicId !== 'default') {
                            $query->where('clinic_id', $this->activeClinicId);
                        } else {
                            $query->whereNull('clinic_id');
                        }
                    })
                    ->get();

                $existingDays = $schedules->pluck('day')->toArray();
                $missingDays = array_diff($daysOfWeek, $existingDays);

                // ایجاد رکوردهای جدید برای روزهای缺失
                foreach ($missingDays as $missingDay) {
                    DoctorWorkSchedule::create([
                        'doctor_id' => $doctorId,
                        'day' => $missingDay,
                        'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                        'is_working' => false,
                        'work_hours' => json_encode([]),
                        'appointment_settings' => json_encode([]),
                    ]);
                }

                // به‌روزرسانی تنظیمات نوبت‌دهی برای تمام روزها
                foreach ($schedules as $schedule) {
                    $appointmentSettings = json_decode($schedule->appointment_settings, true) ?? [];
                    $existingIndex = array_search(
                        (int)$index,
                        array_column($appointmentSettings, 'work_hour_key')
                    );

                    if ($existingIndex !== false) {
                        $appointmentSettings[$existingIndex] = $newSetting;
                    } else {
                        $appointmentSettings[] = $newSetting;
                    }

                    $schedule->update(['appointment_settings' => json_encode(array_values($appointmentSettings))]);
                }

                DB::commit();

                $this->refreshWorkSchedules();

                $this->modalMessage = 'ساعت کاری و تنظیمات نوبت‌دهی با موفقیت ذخیره شد';
                $this->modalType = 'success';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'success',
                ]);

                $this->dispatch('close-calculator-modal');
                $this->dispatch('refresh-work-hours');
                $this->reset(['calculator', 'calculationMode']);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();
            $errorMessage = implode('، ', $errorMessages);
            $this->modalMessage = $errorMessage ?: 'لطفاً تمام فیلدهای مورد نیاز را پر کنید';
            $this->modalType = 'error';
            $this->modalOpen = true;
            Log::error('Validation error in saveCalculator: ' . $errorMessage);
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
            $this->dispatch('close-calculator-modal');
        } catch (\Exception $e) {
            Log::error('Error in saveCalculator: ' . $e->getMessage(), ['calculator' => $this->calculator]);
            $this->modalMessage = $e->getMessage() ?: 'خطا در ذخیره‌سازی ساعات کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
            $this->dispatch('close-calculator-modal');
        }
    }

    public function setEmergencyModalOpen($isOpen)
    {
        $this->isEmergencyModalOpen = $isOpen;
    }

    public function updatedSelectedClinicId()
    {
        $this->activeClinicId = $this->selectedClinicId;
        $this->reset(['workSchedules', 'isWorking', 'slots']);
        $this->mount($this->clinicId);
        $this->dispatch('refresh-clinic-data');
    }

    public function saveTimeSlot()
    {
        try {
            $this->validate([
                'selectedDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
                'startTime' => 'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
                'maxAppointments' => 'required|integer|min:1',
            ]);

            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            DB::beginTransaction();
            try {
                $workSchedule = DoctorWorkSchedule::withoutGlobalScopes()
                    ->where('doctor_id', $doctorId)
                    ->where('day', $this->selectedDay)
                    ->where(function ($query) {
                        if ($this->activeClinicId !== 'default') {
                            $query->where('clinic_id', $this->activeClinicId);
                        } else {
                            $query->whereNull('clinic_id');
                        }
                    })
                    ->first();

                if (!$workSchedule) {
                    $workSchedule = DoctorWorkSchedule::create([
                        'doctor_id' => $doctorId,
                        'day' => $this->selectedDay,
                        'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                        'is_working' => true,
                        'work_hours' => json_encode([]),
                    ]);
                }

                $existingWorkHours = is_array($workSchedule->work_hours) ? $workSchedule->work_hours : json_decode($workSchedule->work_hours, true) ?? [];
                $newSlot = [
                    'start' => $this->startTime,
                    'end' => $this->endTime,
                    'max_appointments' => (int) $this->maxAppointments,
                ];

                // بررسی تداخل زمانی با یک حلقه
                foreach ($existingWorkHours as $slot) {
                    if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                        throw new \Exception(sprintf(
                            'زمان %s تا %s با بازه زمانی موجود %s تا %s در روز %s تداخل دارد',
                            $newSlot['start'],
                            $newSlot['end'],
                            $slot['start'],
                            $slot['end'],
                            $this->getPersianDay($this->selectedDay)
                        ));
                    }
                }

                $existingWorkHours[] = $newSlot;
                $workSchedule->update([
                    'work_hours' => json_encode($existingWorkHours),
                    'is_working' => true
                ]);

                $newSlotIndex = count($existingWorkHours) - 1;
                $this->slots[$this->selectedDay][] = [
                    'id' => $workSchedule->id . '-' . $newSlotIndex,
                    'start_time' => $this->startTime,
                    'end_time' => $this->endTime,
                    'max_appointments' => (int) $this->maxAppointments,
                ];

                DB::commit();

                $this->modalMessage = 'ساعت کاری با موفقیت ذخیره شد';
                $this->modalType = 'success';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'success',
                ]);
                $this->reset(['startTime', 'endTime', 'maxAppointments', 'selectedDay']);
                $this->dispatch('refresh-work-hours');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();
            $errorMessage = implode('، ', $errorMessages);
            $this->modalMessage = $errorMessage ?: 'لطفاً تمام فیلدهای مورد نیاز را پر کنید';
            $this->modalType = 'error';
            $this->modalOpen = true;
            Log::error('Validation error in saveTimeSlot: ' . $errorMessage);
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in saveTimeSlot: ' . $e->getMessage());
            $this->modalMessage = $e->getMessage() ?: 'خطا در ذخیره‌سازی ساعت کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    public function deleteTimeSlot($day, $index)
    {
        try {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            DB::beginTransaction();
            try {
                $workSchedule = DoctorWorkSchedule::withoutGlobalScopes()
                    ->where('doctor_id', $doctorId)
                    ->where('day', $day)
                    ->where(function ($query) {
                        if ($this->activeClinicId !== 'default') {
                            $query->where('clinic_id', $this->activeClinicId);
                        } else {
                            $query->whereNull('clinic_id');
                        }
                    })
                    ->first();

                if (!$workSchedule) {
                    throw new \Exception('ساعات کاری برای این روز یافت نشد');
                }

                $existingWorkHours = is_array($workSchedule->work_hours) ? $workSchedule->work_hours : json_decode($workSchedule->work_hours, true) ?? [];

                if (!isset($existingWorkHours[$index])) {
                    throw new \Exception('اسلات انتخاب‌شده یافت نشد');
                }

                // حذف اسلات و بازسازی آرایه
                unset($existingWorkHours[$index]);
                $updatedWorkHours = array_values($existingWorkHours);

                // به‌روزرسانی رکورد
                $workSchedule->update([
                    'work_hours' => json_encode($updatedWorkHours),
                    'is_working' => !empty($updatedWorkHours)
                ]);

                // به‌روزرسانی آرایه slots
                unset($this->slots[$day][$index]);
                $this->slots[$day] = array_values($this->slots[$day]);

                // اگر هیچ اسلاتی باقی نمانده، یک اسلات خالی اضافه کن
                if (empty($this->slots[$day])) {
                    $this->slots[$day][] = [
                        'id' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'max_appointments' => null,
                    ];
                }

                DB::commit();

                $this->modalMessage = 'اسلات با موفقیت حذف شد';
                $this->modalType = 'success';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'success',
                ]);
                $this->dispatch('refresh-work-hours');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error in deleteTimeSlot: ' . $e->getMessage(), [
                'day' => $day,
                'index' => $index,
                'doctor_id' => $doctorId ?? null
            ]);

            $this->modalMessage = $e->getMessage() ?: 'خطا در حذف اسلات';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    public function removeSlot($day, $index)
    {
        $this->deleteTimeSlot($day, $index);
    }

    public function updateWorkDayStatus($day)
    {
        try {
            $dayMap = [
                'شنبه' => 'saturday',
                'یکشنبه' => 'sunday',
                'دوشنبه' => 'monday',
                'سه‌شنبه' => 'tuesday',
                'چهارشنبه' => 'wednesday',
                'پنج‌شنبه' => 'thursday',
                'جمعه' => 'friday',
            ];

            if (!isset($dayMap[$day])) {
                throw new \Exception('روز نامعتبر است');
            }

            $englishDay = $dayMap[$day];
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
            $isWorking = isset($this->isWorking[$englishDay]) ? (bool) $this->isWorking[$englishDay] : false;

            DB::beginTransaction();
            try {
                $workSchedule = DoctorWorkSchedule::withoutGlobalScopes()
                    ->where([
                        'doctor_id' => $doctorId,
                        'day' => $englishDay,
                        'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                    ])
                    ->first();

                if ($workSchedule) {
                    $workSchedule->update(['is_working' => $isWorking]);
                } else {
                    DoctorWorkSchedule::create([
                        'doctor_id' => $doctorId,
                        'day' => $englishDay,
                        'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                        'is_working' => $isWorking,
                        'work_hours' => json_encode([]),
                    ]);
                }

                $this->isWorking[$englishDay] = $isWorking;
                $this->refreshWorkSchedules();

                DB::commit();

                $this->modalMessage = $isWorking
                    ? "روز {$day} با موفقیت فعال شد"
                    : "روز {$day} با موفقیت غیرفعال شد";
                $this->modalType = 'success';
                $this->modalOpen = true;

                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'success',
                ]);
                $this->dispatch('refresh-work-hours');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error in updateWorkDayStatus: ' . $e->getMessage(), [
                'day' => $day,
                'doctor_id' => $doctorId ?? null,
                'is_working' => $isWorking ?? null
            ]);

            $this->modalMessage = $e->getMessage() ?: 'خطا در بروزرسانی وضعیت روز کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    public function addSlot($day)
    {
        if (!empty($this->slots[$day])) {
            $lastSlot = end($this->slots[$day]);
            if (
                !$lastSlot['start_time'] ||
                !$lastSlot['end_time'] ||
                !$lastSlot['max_appointments']
            ) {
                $this->modalMessage = 'ابتدا ردیف قبلی را تکمیل کنید';
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => 'ابتدا ردیف قبلی را تکمیل کنید',
                    'type' => 'error',
                ]);
                return;
            }
        }

        $newIndex = count($this->slots[$day]);
        $this->slots[$day][] = [
            'id' => null,
            'start_time' => null,
            'end_time' => null,
            'max_appointments' => null,
        ];

        $this->dispatch('refresh-timepicker');
    }

    public function updatedIsWorking($value, $key)
    {
        $dayMap = [
            'saturday' => 'شنبه',
            'sunday' => 'یکشنبه',
            'monday' => 'دوشنبه',
            'tuesday' => 'سه‌شنبه',
            'wednesday' => 'چهارشنبه',
            'thursday' => 'پنج‌شنبه',
            'friday' => 'جمعه',
        ];

        $englishDay = $key;
        $persianDay = $dayMap[$englishDay] ?? null;
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        if (!$persianDay) {
            $this->modalMessage = 'روز نامعتبر است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            return;
        }

        $isWorking = (bool) $value;

        try {
            $workSchedule = DoctorWorkSchedule::where([
                'doctor_id' => $doctorId,
                'day' => $englishDay,
                'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
            ])->first();

            if ($workSchedule) {
                $workSchedule->update([
                    'is_working' => $isWorking,
                ]);
            } else {
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctorId,
                    'day' => $englishDay,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                    'is_working' => $isWorking,
                    'work_hours' => json_encode([]),
                ]);
            }

            $this->refreshWorkSchedules();

            $this->modalMessage = $isWorking
                ? "روز {$persianDay} فعال شد"
                : "روز {$persianDay} غیرفعال شد";
            $this->modalType = 'success';
            $this->modalOpen = true;

            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);
            $this->dispatch('refresh-work-hours');
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در بروزرسانی وضعیت روز کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }

    public function updateAutoScheduling()
    {
        try {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            DB::beginTransaction();
            try {
                DoctorAppointmentConfig::withoutGlobalScopes()
                    ->updateOrCreate(
                        [
                            'doctor_id' => $doctorId,
                            'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                        ],
                        [
                            'auto_scheduling' => (bool) $this->autoScheduling,
                            'doctor_id' => $doctorId,
                            'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                        ]
                    );

                DB::commit();

                $this->dispatch('show-toastr', [
                    'message' => $this->autoScheduling ? 'نوبت‌دهی خودکار فعال شد' : 'نوبت‌دهی خودکار غیرفعال شد',
                    'type' => 'success',
                ]);

                // به‌روزرسانی تنظیمات در حافظه
                $this->appointmentConfig->auto_scheduling = (bool) $this->autoScheduling;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error in updateAutoScheduling: ' . $e->getMessage(), [
                'doctor_id' => $doctorId ?? null,
                'auto_scheduling' => $this->autoScheduling ?? null,
                'clinic_id' => $this->activeClinicId ?? null
            ]);

            $this->dispatch('show-toastr', [
                'message' => 'خطا در به‌روزرسانی تنظیمات',
                'type' => 'error',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.dr.panel.work-hours');
    }
}
