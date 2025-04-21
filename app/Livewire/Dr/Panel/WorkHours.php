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
        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $this->clinicId = $clinicId; // clinicId از پراپرتی‌های کامپوننت
        $this->selectedClinicId = request()->query('selectedClinicId', 'default'); // برای صفحه قبلی

        // انتخاب clinicId فعال: اولویت با clinicId از URL، سپس selectedClinicId
        $this->activeClinicId = $this->clinicId ?? $this->selectedClinicId;

        $this->appointmentConfig = DoctorAppointmentConfig::firstOrCreate(
            [
                'doctor_id' => $doctorId,
                'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
            ],
            [
                'auto_scheduling' => true,
                'online_consultation' => false,
                'holiday_availability' => false,
            ]
        );

        $this->refreshWorkSchedules();

        $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($daysOfWeek as $day) {
            if (!collect($this->workSchedules)->firstWhere('day', $day)) {
                DoctorWorkSchedule::create([
                    'doctor_id' => $doctorId,
                    'day' => $day,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                    'is_working' => false,
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]);
            }
        }

        $this->refreshWorkSchedules();

        $this->isWorking = array_fill_keys($daysOfWeek, false);
        $this->slots = array_fill_keys($daysOfWeek, []);
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

        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
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

            $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
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

            $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
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
        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $this->workSchedules = DoctorWorkSchedule::where('doctor_id', $doctorId)
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
                    'is_working' => $schedule->is_working,
                    'work_hours' => json_decode($schedule->work_hours, true) ?? [],
                    'appointment_settings' => json_decode($schedule->appointment_settings, true) ?? [],
                    'emergency_times' => json_decode($schedule->emergency_times, true) ?? [],
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
        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
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
            ]);

            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

            DoctorAppointmentConfig::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                ],
                [
                    'calendar_days' => $this->calendarDays,
                    'holiday_availability' => $this->holidayAvailability,
                    'auto_scheduling' => $this->autoScheduling,
                    'online_consultation' => $this->onlineConsultation,
                ]
            );

            $this->modalMessage = 'تنظیمات با موفقیت ذخیره شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در ذخیره تنظیمات';
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
        if (!$replace && !empty($this->copySource['day'])) {
            $this->storedCopySource = $this->copySource;
            $this->storedSelectedDays = $this->selectedDays;
        }

        $copySource = $replace || empty($this->copySource['day']) ? $this->storedCopySource : $this->copySource;
        $selectedDays = $replace || empty(array_filter($this->selectedDays)) ? $this->storedSelectedDays : $this->selectedDays;

        if (!isset($copySource['day']) || !in_array($copySource['day'], ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday']) || !isset($copySource['index']) || !is_numeric($copySource['index']) || $copySource['index'] < 0) {
            $this->modalMessage = 'داده‌های منبع کپی نامعتبر است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => 'داده‌های منبع کپی نامعتبر است',
                'type' => 'error',
            ]);
            $this->dispatch('close-checkbox-modal');
            return;
        }

        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $sourceDay = $copySource['day'];
        $sourceIndex = (int) $copySource['index'];

        if (empty(array_filter($selectedDays))) {
            $this->modalMessage = 'هیچ روزی برای کپی انتخاب نشده است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => 'هیچ روزی برای کپی انتخاب نشده است',
                'type' => 'error',
            ]);
            $this->dispatch('close-checkbox-modal');
            return;
        }

        $sourceSchedule = collect($this->workSchedules)->firstWhere('day', $sourceDay);
        if (!$sourceSchedule) {
            $this->modalMessage = 'برنامه کاری برای روز مبدا یافت نشد';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => 'برنامه کاری برای روز مبدا یافت نشد',
                'type' => 'error',
            ]);
            $this->dispatch('close-checkbox-modal');
            return;
        }

        $sourceWorkHours = !empty($sourceSchedule['work_hours']) ? $sourceSchedule['work_hours'] : [];
        $sourceAppointmentSettings = !empty($sourceSchedule['appointment_settings']) ? $sourceSchedule['appointment_settings'] : [];
        $sourceEmergencyTimes = !empty($sourceSchedule['emergency_times']) ? $sourceSchedule['emergency_times'] : [];

        if (empty($sourceWorkHours[$sourceIndex])) {
            $this->modalMessage = 'اسلات انتخاب‌شده برای کپی یافت نشد';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => 'اسلات انتخاب‌شده برای کپی یافت نشد',
                'type' => 'error',
            ]);
            $this->dispatch('close-checkbox-modal');
            return;
        }

        $sourceSlot = $sourceWorkHours[$sourceIndex];
        $conflicts = [];
        $selectedTargetDays = array_keys(array_filter($selectedDays));

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
            $this->modalMessage = 'هیچ روزی برای کپی بدون تداخل یافت نشد';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => 'هیچ روزی برای کپی بدون تداخل یافت نشد',
                'type' => 'error',
            ]);
            $this->dispatch('close-checkbox-modal');
            return;
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
    }

    private function isTimeConflict($newStart, $newEnd, $existingStart, $existingEnd)
    {
        try {
            $newStartTime = Carbon::createFromFormat('H:i', $newStart);
            $newEndTime = Carbon::createFromFormat('H:i', $newEnd);
            $existingStartTime = Carbon::createFromFormat('H:i', $existingStart);
            $existingEndTime = Carbon::createFromFormat('H:i', $existingEnd);
            return (
                ($newStartTime < $existingEndTime && $newEndTime > $existingStartTime) ||
                ($newStartTime == $existingStartTime && $newEndTime == $existingEndTime)
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    public function openCopyModal($day)
    {
        $this->sourceDay = $day;
        $this->selectAllCopyModal = false;
        $this->selectedDays = [];
        $this->dispatchBrowserEvent('open-checkbox-modal');
    }

    protected function timeToMinutes($time)
    {
        if (!$time) {
            return 0;
        }
        [$hours, $minutes] = explode(':', $time);
        return ($hours * 60) + $minutes;
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
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
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
                    'doctor_id' => $doctor->id,
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
                $this->modalMessage = sprintf(
                    'بازه زمانی %s تا %s قبلاً برای روز %s ثبت شده است',
                    $newSlot['start'],
                    $newSlot['end'],
                    $this->getPersianDay($day)
                );
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'error',
                ]);
                return;
            }

            foreach ($workHours as $i => $slot) {
                if ($i === $index) {
                    continue;
                }
                if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                    $this->modalMessage = sprintf(
                        'زمان %s تا %s با بازه زمانی موجود %s تا %s در روز %s تداخل دارد',
                        $newSlot['start'],
                        $newSlot['end'],
                        $slot['start'],
                        $slot['end'],
                        $this->getPersianDay($day)
                    );
                    $this->modalType = 'error';
                    $this->modalOpen = true;
                    $this->dispatch('show-toastr', [
                        'message' => $this->modalMessage,
                        'type' => 'error',
                    ]);
                    return;
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

            $this->modalMessage = 'ساعت کاری با موفقیت ذخیره شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);

            $this->dispatch('close-calculator-modal');
            $this->reset(['calculator', 'calculationMode']);
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در ذخیره‌سازی ساعات کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
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
        $this->validate([
            'selectedDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',
            'maxAppointments' => 'required|integer|min:1',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        try {
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
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
                    'doctor_id' => $doctor->id,
                    'day' => $this->selectedDay,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                    'is_working' => true,
                    'work_hours' => json_encode([]),
                ]);
            }

            $existingWorkHours = json_decode($workSchedule->work_hours, true) ?? [];
            $newSlot = [
                'start' => $this->startTime,
                'end' => $this->endTime,
                'max_appointments' => $this->maxAppointments,
            ];

            $slotExists = array_filter($existingWorkHours, function ($slot) use ($newSlot) {
                return $slot['start'] === $newSlot['start'] && $slot['end'] === $newSlot['end'];
            });

            if (!empty($slotExists)) {
                $this->modalMessage = sprintf(
                    'بازه زمانی %s تا %s قبلاً برای روز %s ثبت شده است',
                    $newSlot['start'],
                    $newSlot['end'],
                    $this->getPersianDay($this->selectedDay)
                );
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'error',
                ]);
                return;
            }

            foreach ($existingWorkHours as $index => $slot) {
                if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                    $this->modalMessage = sprintf(
                        'زمان %s تا %s با بازه زمانی موجود %s تا %s در روز %s تداخل دارد',
                        $newSlot['start'],
                        $newSlot['end'],
                        $slot['start'],
                        $slot['end'],
                        $this->getPersianDay($this->selectedDay)
                    );
                    $this->modalType = 'error';
                    $this->modalOpen = true;
                    $this->dispatch('show-toastr', [
                        'message' => $this->modalMessage,
                        'type' => 'error',
                    ]);
                    return;
                }
            }

            $existingWorkHours[] = $newSlot;
            $workSchedule->update(['work_hours' => json_encode($existingWorkHours), 'is_working' => true]);

            $newSlotIndex = count($existingWorkHours) - 1;
            $newSlotEntry = [
                'id' => $workSchedule->id . '-' . $newSlotIndex,
                'start_time' => $this->startTime,
                'end_time' => $this->endTime,
                'max_appointments' => $this->maxAppointments,
            ];

            if (!isset($this->slots[$this->selectedDay])) {
                $this->slots[$this->selectedDay] = [];
            }
            $this->slots[$this->selectedDay][] = $newSlotEntry;

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
            $this->modalMessage = 'خطا در ذخیره‌سازی ساعت کاری';
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
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        try {
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
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
                $this->modalMessage = 'ساعات کاری برای این روز یافت نشد';
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'error',
                ]);
                return;
            }

            $existingWorkHours = json_decode($workSchedule->work_hours, true) ?? [];

            if (!isset($existingWorkHours[$index])) {
                $this->modalMessage = 'اسلات انتخاب‌شده یافت نشد';
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'error',
                ]);
                return;
            }

            unset($existingWorkHours[$index]);
            $updatedWorkHours = array_values($existingWorkHours);

            $workSchedule->update([
                'work_hours' => json_encode($updatedWorkHours),
                'is_working' => !empty($updatedWorkHours),
            ]);

            unset($this->slots[$day][$index]);
            $this->slots[$day] = array_values($this->slots[$day]);

            if (empty($this->slots[$day])) {
                $this->slots[$day][] = [
                    'id' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'max_appointments' => null,
                ];
            }

            $this->modalMessage = 'اسلات با موفقیت حذف شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);

            $this->dispatch('refresh-work-hours');
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در حذف اسلات';
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
            $this->modalMessage = 'روز نامعتبر است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            return;
        }

        $englishDay = $dayMap[$day];
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $isWorking = isset($this->isWorking[$englishDay]) ? (bool) $this->isWorking[$englishDay] : false;

        try {
            $workSchedule = DoctorWorkSchedule::where([
                'doctor_id' => $doctor->id,
                'day' => $englishDay,
                'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
            ])->first();

            if ($workSchedule) {
                $workSchedule->update([
                    'is_working' => $isWorking,
                ]);
            } else {
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $englishDay,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                    'is_working' => $isWorking,
                    'work_hours' => json_encode([]),
                ]);
            }

            $this->isWorking[$englishDay] = $isWorking;
            $this->refreshWorkSchedules();

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
            $this->modalMessage = 'خطا در بروزرسانی وضعیت روز کاری';
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

        if (!$persianDay) {
            $this->modalMessage = 'روز نامعتبر است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            return;
        }

        $isWorking = (bool) $value;

        try {
            $workSchedule = DoctorWorkSchedule::where([
                'doctor_id' => $doctor->id,
                'day' => $englishDay,
                'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
            ])->first();

            if ($workSchedule) {
                $workSchedule->update([
                    'is_working' => $isWorking,
                ]);
            } else {
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctor->id,
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
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        try {
            DoctorAppointmentConfig::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                ],
                [
                    'auto_scheduling' => $this->autoScheduling,
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $this->activeClinicId !== 'default' ? $this->activeClinicId : null,
                ]
            );

            $this->dispatch('show-toastr', [
                'message' => $this->autoScheduling ? 'نوبت‌دهی خودکار فعال شد' : 'نوبت‌دهی خودکار غیرفعال شد',
                'type' => $this->autoScheduling ? 'success' : 'error',
            ]);
        } catch (\Exception $e) {
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
