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
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class Workhours extends Component
{
    public $calculationMode = 'count'; // حالت پیش‌فرض: تعداد نوبت‌ها
    public $selectedClinicId;
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
    public $scheduleSettings = []; // برای ذخیره تنظیمات زمان‌بندی موقت
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
    public $selectedScheduleDays = []; // برای ذخیره روزهای انتخاب‌شده در مودال اسکجول
    public $selectAllCopyModal = false;
    public $emergencyTimes = []; // زمان‌های انتخاب‌شده برای نوبت‌های اورژانسی
    public $emergencyModalDay; // روز انتخاب‌شده برای مودال
    public $emergencyModalIndex; // اندیس اسلات انتخاب‌شده برای مودال
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
    ];
    public function mount()
    {
        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->id();
        $this->selectedClinicId = request()->query('selectedClinicId', 'default');
        $this->appointmentConfig = DoctorAppointmentConfig::firstOrCreate(
            [
                'doctor_id' => $doctorId,
                'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
            ],
            [
                'auto_scheduling' => true,
                'online_consultation' => false,
                'holiday_availability' => false,
            ]
        );
        $this->workSchedules = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where(function ($query) {
                if ($this->selectedClinicId !== 'default') {
                    $query->where('clinic_id', $this->selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->get()
            ->map(function ($schedule) {
                $appointmentSettings = $schedule->appointment_settings
                    ? json_decode($schedule->appointment_settings, true) ?? []
                    : [];
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'is_working' => $schedule->is_working,
                    'work_hours' => $schedule->work_hours ? json_decode($schedule->work_hours, true) ?? [] : [],
                    'appointment_settings' => $appointmentSettings,
                    'emergency_times' => $schedule->emergency_times ? json_decode($schedule->emergency_times, true) ?? [] : [],
                ];
            })
            ->toArray();
        $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($daysOfWeek as $day) {
            if (!collect($this->workSchedules)->firstWhere('day', $day)) {
                DoctorWorkSchedule::create([
                    'doctor_id' => $doctorId,
                    'day' => $day,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
                    'is_working' => false,
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]);
            }
        }
        $this->workSchedules = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where(function ($query) {
                if ($this->selectedClinicId !== 'default') {
                    $query->where('clinic_id', $this->selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->get()
            ->map(function ($schedule) {
                $appointmentSettings = $schedule->appointment_settings
                    ? json_decode($schedule->appointment_settings, true) ?? []
                    : [];
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'is_working' => $schedule->is_working,
                    'work_hours' => $schedule->work_hours ? json_decode($schedule->work_hours, true) ?? [] : [],
                    'appointment_settings' => $appointmentSettings,
                    'emergency_times' => $schedule->emergency_times ? json_decode($schedule->emergency_times, true) ?? [] : [],
                ];
            })
            ->toArray();
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
        $this->calendarDays = $this->appointmentConfig->calendar_days;
        $this->onlineConsultation = $this->appointmentConfig->online_consultation;
        $this->holidayAvailability = $this->appointmentConfig->holiday_availability;

        $this->selectedScheduleDays = array_fill_keys(
            ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            false
        );

    }

    public function forceRefreshSettings()
    {
        // این متد صرفاً برای اطمینان از رفرش لیست تنظیمات استفاده می‌شود
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
                if ($this->selectedClinicId !== 'default') {
                    $query->where('clinic_id', $this->selectedClinicId);
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
    public $selectAllScheduleModal = false;

    public function updatedSelectAllScheduleModal($value)
    {
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            $this->selectedScheduleDays[$day] = $value;
        }
    }
    public function saveSchedule($startTime, $endTime, $days)
    {
        try {
            $this->validate([
                'scheduleModalDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
                'scheduleModalIndex' => 'required|integer',
            ]);

            // تبدیل زمان به دقیقه برای مقایسه
            $timeToMinutes = function ($time) {
                list($hours, $minutes) = explode(':', $time);
                return (int)$hours * 60 + (int)$minutes;
            };

            $newStartMinutes = $timeToMinutes($startTime);
            $newEndMinutes = $timeToMinutes($endTime);

            if ($newEndMinutes <= $newStartMinutes) {
                throw new \Exception('زمان پایان باید بعد از زمان شروع باشد.');
            }

            // بررسی تداخل
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
                        if ($this->selectedClinicId !== 'default') {
                            $query->where('clinic_id', $this->selectedClinicId);
                        } else {
                            $query->whereNull('clinic_id');
                        }
                    })
                    ->first();

                if ($schedule && $schedule->appointment_settings) {
                    $settings = json_decode($schedule->appointment_settings, true) ?? [];
                    foreach ($settings as $setting) {
                        if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->scheduleModalIndex) {
                            continue; // نادیده گرفتن تنظیمات مربوط به همین اسلات
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

            // ذخیره تنظیمات
            $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $this->scheduleModalDay)
                ->where(function ($query) {
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if (!$schedule) {
                $schedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctorId,
                    'day' => $this->scheduleModalDay,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
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

            // اضافه کردن یا به‌روزرسانی تنظیم
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
            $this->dispatch('refresh-schedule-settings');
        } catch (\Exception $e) {
            Log::error('Error in saveSchedule: ' . $e->getMessage(), ['exception' => $e]);
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
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
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
            Log::error('Error in deleteScheduleSetting: ' . $e->getMessage(), ['exception' => $e]);
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
                if ($this->selectedClinicId !== 'default') {
                    $query->where('clinic_id', $this->selectedClinicId);
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
        Log::info('Work schedules refreshed', ['workSchedules' => $this->workSchedules]);
    }




    public function closeScheduleModal()
    {
        $this->reset(['scheduleModalDay', 'scheduleModalIndex', 'scheduleSettings']);
        $this->dispatch('close-schedule-modal');
    }
    public function updatedSelectAllCopyModal($value)
    {
        $sourceDay = $this->copySource['day'];
        foreach (['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
            if ($day !== $sourceDay) {
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
                if ($this->selectedClinicId !== 'default') {
                    $query->where('clinic_id', $this->selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->first();
        if ($schedule) {
            $schedule->emergency_times = json_encode($this->emergencyTimes);
            $schedule->save();
            $this->workSchedules = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where(function ($query) {
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->get()
                ->map(function ($schedule) {
                    $schedule->emergency_times = $schedule->emergency_times
                        ? json_decode($schedule->emergency_times, true) ?? []
                        : [];
                    $schedule->work_hours = $schedule->work_hours
                        ? json_decode($schedule->work_hours, true) ?? []
                        : [];
                    $schedule->appointment_settings = $schedule->appointment_settings
                        ? json_decode($schedule->appointment_settings, true) ?? []
                        : [];
                    return $schedule;
                })
                ->toArray();
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
    public $storedCopySource = [];
    public $storedSelectedDays = [];
    public function copySchedule($replace = false)
    {
        // ذخیره copySource و selectedDays فقط در اولین فراخوانی
        if (!$replace && !empty($this->copySource['day'])) {
            $this->storedCopySource = $this->copySource;
            $this->storedSelectedDays = $this->selectedDays;
        }
        // استفاده از storedCopySource و storedSelectedDays اگر replace=true یا copySource خالی باشد
        $copySource = $replace || empty($this->copySource['day']) ? $this->storedCopySource : $this->copySource;
        $selectedDays = $replace || empty(array_filter($this->selectedDays)) ? $this->storedSelectedDays : $this->selectedDays;
        // اعتبارسنجی copySource
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
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
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
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();
            if (!$targetSchedule) {
                $targetSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctorId,
                    'day' => $targetDay,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
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
        $this->workSchedules = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where(function ($query) {
                if ($this->selectedClinicId !== 'default') {
                    $query->where('clinic_id', $this->selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->get()
            ->map(function ($schedule) {
                $schedule->emergency_times = $schedule->emergency_times
                    ? json_decode($schedule->emergency_times, true) ?? []
                    : [];
                $schedule->work_hours = $schedule->work_hours
                    ? json_decode($schedule->work_hours, true) ?? []
                    : [];
                $schedule->appointment_settings = $schedule->appointment_settings
                    ? json_decode($schedule->appointment_settings, true) ?? []
                    : [];
                return $schedule;
            })
            ->toArray();
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
    public function openCopyModal($day, $index)
    {
        $this->copySource = [
            'day' => $day,
            'index' => (int) $index,
        ];
        // فقط اگر selectedDays خالی باشد، مقداردهی اولیه کن
        if (empty(array_filter($this->selectedDays))) {
            $this->selectedDays = array_fill_keys(
                ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                false
            );
        }
        $this->selectAllCopyModal = false;
        $this->dispatch('open-checkbox-modal', $this->copySource);
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
            // دریافت بازه زمانی ‌های موجود از دیتابیس
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $day)
                ->where(function ($query) {
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();
            if (!$workSchedule) {
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $day,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
                    'is_working' => true,
                    'work_hours' => json_encode([]),
                ]);
            }
            $workHours = json_decode($workSchedule->work_hours, true) ?? [];
            // بررسی بازه زمانی  تکراری (به جز بازه زمانی  در حال ویرایش)
            $slotExists = array_filter($workHours, function ($slot, $i) use ($newSlot, $index) {
                return $i !== $index && $slot['start'] === $newSlot['start'] && $slot['end'] === $newSlot['end'];
            }, ARRAY_FILTER_USE_BOTH);
            if (!empty($slotExists)) {
                $this->modalMessage = sprintf(
                    'بازه زمانی  %s تا %s قبلاً برای روز %s ثبت شده است',
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
            // بررسی تداخل زمانی با سایر بازه زمانی ‌ها (به جز بازه زمانی  در حال ویرایش)
            foreach ($workHours as $i => $slot) {
                if ($i === $index) {
                    continue; // بازه زمانی  در حال ویرایش را نادیده بگیر
                }
                if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                    $this->modalMessage = sprintf(
                        'زمان %s تا %s با بازه زمانی  موجود %s تا %s در روز %s تداخل دارد',
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
            // به‌روزرسانی بازه زمانی  در آرایه workHours
            $workHours[$index] = $newSlot;
            $workSchedule->update(['work_hours' => json_encode($workHours), 'is_working' => true]);
            // به‌روزرسانی بازه زمانی  در آرایه slots
            $this->slots[$day][$index] = [
                'id' => $workSchedule->id . '-' . $index,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'max_appointments' => $appointmentCount,
            ];
            $this->modalMessage = ' ساعت کاری با موفقیت ذخیره شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);
            // بستن مودال
            $this->dispatch('close-calculator-modal');
            $this->reset(['calculator', 'calculationMode']);
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در ذخیره‌سازی نوبت‌ها';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }
    public $isEmergencyModalOpen = false;
    public function setEmergencyModalOpen($isOpen)
    {
        $this->isEmergencyModalOpen = $isOpen;
    }
    // اصلاح متد saveEmergencyTimes
    public function updatedSelectedClinicId()
    {
        $this->reset(['workSchedules', 'isWorking', 'slots']);
        $this->mount();
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
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();
            if (!$workSchedule) {
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $this->selectedDay,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
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
            // بررسی بازه زمانی  تکراری
            $slotExists = array_filter($existingWorkHours, function ($slot) use ($newSlot) {
                return $slot['start'] === $newSlot['start'] && $slot['end'] === $newSlot['end'];
            });
            if (!empty($slotExists)) {
                $this->modalMessage = sprintf(
                    'بازه زمانی  %s تا %s قبلاً برای روز %s ثبت شده است',
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
            // بررسی تداخل زمانی
            foreach ($existingWorkHours as $index => $slot) {
                if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                    $this->modalMessage = sprintf(
                        'زمان %s تا %s با بازه زمانی  موجود %s تا %s در روز %s تداخل دارد',
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
            // اضافه کردن بازه زمانی  جدید
            $existingWorkHours[] = $newSlot;
            $workSchedule->update(['work_hours' => json_encode($existingWorkHours), 'is_working' => true]);
            // به‌روزرسانی slots در حافظه
            $newSlotIndex = count($existingWorkHours) - 1;
            $newSlotEntry = [
                'id' => $workSchedule->id . '-' . $newSlotIndex,
                'start_time' => $this->startTime,
                'end_time' => $this->endTime,
                'max_appointments' => $this->maxAppointments,
            ];
            // اطمینان از عدم اضافه شدن بازه زمانی  null
            if (!isset($this->slots[$this->selectedDay])) {
                $this->slots[$this->selectedDay] = [];
            }
            $this->slots[$this->selectedDay][] = $newSlotEntry;
            // لاگ‌گذاری موفقیت
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
            // پیدا کردن برنامه کاری برای روز مشخص
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $day)
                ->where(function ($query) {
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
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
            // بررسی وجود اسلات در اندیس مشخص
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
            // حذف اسلات از آرایه
            unset($existingWorkHours[$index]);
            $updatedWorkHours = array_values($existingWorkHours);
            // به‌روزرسانی دیتابیس
            $workSchedule->update([
                'work_hours' => json_encode($updatedWorkHours),
                'is_working' => !empty($updatedWorkHours), // غیرفعال کردن روز اگر هیچ اسلاتی باقی نماند
            ]);
            // به‌روزرسانی آرایه slots در حافظه
            unset($this->slots[$day][$index]);
            $this->slots[$day] = array_values($this->slots[$day]);
            // اطمینان از وجود حداقل یک اسلات خالی
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
            // رفرش UI
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
        // دریافت وضعیت جدید از آرایه isWorking
        $isWorking = isset($this->isWorking[$englishDay]) ? (bool) $this->isWorking[$englishDay] : false;
        try {
            // پیدا کردن رکورد موجود
            $workSchedule = DoctorWorkSchedule::where([
                'doctor_id' => $doctor->id,
                'day' => $englishDay,
                'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
            ])->first();
            if ($workSchedule) {
                // اگر رکورد وجود دارد، فقط is_working را به‌روزرسانی کنیم
                $workSchedule->update([
                    'is_working' => $isWorking,
                ]);
            } else {
                // اگر رکوردی وجود ندارد، رکورد جدید با work_hours خالی ایجاد کنیم
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $englishDay,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
                    'is_working' => $isWorking,
                    'work_hours' => json_encode([]),
                ]);
            }
            // اطمینان از به‌روزرسانی isWorking
            $this->isWorking[$englishDay] = $isWorking;
            // به‌روزرسانی workSchedules
            $this->workSchedules = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where(function ($query) {
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->get();
            // نمایش پیام موفقیت با نام روز
            $this->modalMessage = $isWorking
                ? "روز {$day} با موفقیت فعال شد"
                : "روز {$day} با موفقیت غیرفعال شد";
            $this->modalType = 'success';
            $this->modalOpen = true;
            // ارسال پیام به توستر
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);
            $this->dispatch('refresh-work-hours');
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در بروزرسانی وضعیت روز کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
        }
    }
    public function addSlot($day)
    {
        // بررسی اینکه آیا بازه زمانی ‌های موجود برای این روز وجود دارند
        if (!empty($this->slots[$day])) {
            // گرفتن آخرین بازه زمانی
            $lastSlot = end($this->slots[$day]);
            // بررسی اینکه آیا آخرین بازه زمانی  کامل است
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
        // اضافه کردن بازه زمانی  جدید
        $newIndex = count($this->slots[$day]);
        $this->slots[$day][] = [
            'id' => null,
            'start_time' => null,
            'end_time' => null,
            'max_appointments' => null,
        ];
        // ارسال رویداد برای رفرش تایم‌پیکر
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
            // پیدا کردن رکورد موجود
            $workSchedule = DoctorWorkSchedule::where([
                'doctor_id' => $doctor->id,
                'day' => $englishDay,
                'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
            ])->first();
            if ($workSchedule) {
                // اگر رکورد وجود دارد، فقط is_working را به‌روزرسانی کنیم
                $workSchedule->update([
                    'is_working' => $isWorking,
                ]);
            } else {
                // اگر رکوردی وجود ندارد، رکورد جدید با work_hours خالی ایجاد کنیم
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $englishDay,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
                    'is_working' => $isWorking,
                    'work_hours' => json_encode([]),
                ]);
            }
            // به‌روزرسانی workSchedules
            $this->workSchedules = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where(function ($query) {
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->get();
            $this->modalMessage = $isWorking
                ? "روز {$persianDay} فعال شد"
                : "روز {$persianDay} غیرفعال شد";
            $this->modalType = 'success';
            $this->modalOpen = true;
            // ارسال پیام به توستر
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'success',
            ]);
            $this->dispatch('refresh-work-hours');
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در بروزرسانی وضعیت روز کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
        }
    }
    public function updateAutoScheduling()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        try {
            $appointmentConfig = DoctorAppointmentConfig::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
                ],
                [
                    'auto_scheduling' => $this->autoScheduling,
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
                ]
            );
            // ارسال داده به‌صورت شیء
            $this->dispatch('show-toastr', [
                'message' => $this->autoScheduling ? 'نوبت‌دهی خودکار فعال شد' : 'نوبت‌دهی خودکار غیرفعال شد',
                'type' => $this->autoScheduling ? 'success' : 'error'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', [
                'message' => 'خطا در به‌روزرسانی تنظیمات',
                'type' => 'error'
            ]);
        }
    }
    public function render()
    {
        return view('livewire.dr.panel.work-hours');
    }
}
