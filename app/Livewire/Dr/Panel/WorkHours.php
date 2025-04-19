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
    public $modalType = '';
    public $modalMessage = '';
    public $holidays = [];
    public $nextAvailableDate;
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

        // لود تنظیمات نوبت‌دهی
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

        // لود برنامه‌های کاری
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
                // اطمینان از اینکه emergency_times به‌صورت آرایه است
                $schedule->emergency_times = $schedule->emergency_times
                    ? json_decode($schedule->emergency_times, true) ?? []
                    : [];
                return $schedule;
            })
            ->toArray();

        // ایجاد رکوردهای پیش‌فرض برای روزهای هفته
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

        // دوباره لود برنامه‌های کاری
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
                // اطمینان از اینکه emergency_times به‌صورت آرایه است
                $schedule->emergency_times = $schedule->emergency_times
                    ? json_decode($schedule->emergency_times, true) ?? []
                    : [];
                return $schedule;
            })
            ->toArray();

        // تنظیم isWorking و slots
        $this->isWorking = array_fill_keys($daysOfWeek, false);
        $this->slots = array_fill_keys($daysOfWeek, []);

        foreach ($daysOfWeek as $day) {
            $schedule = collect($this->workSchedules)->firstWhere('day', $day);
            if ($schedule) {
                $this->isWorking[$day] = (bool) $schedule['is_working'];
                $workHours = !empty($schedule['work_hours']) ? json_decode($schedule['work_hours'], true) : [];
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

            // آپدیت workSchedules برای بازتاب تغییرات
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
                    return $schedule;
                })
                ->toArray();

            Log::info('Emergency times saved and workSchedules updated', [
                'day' => $this->emergencyModalDay,
                'emergency_times' => $this->emergencyTimes,
                'workSchedules' => $this->workSchedules,
            ]);

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
    public function updatedSelectAllCopyModal($value)
    {
        foreach (['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
            $this->selectedCopyDays[$day] = $value;
        }
    }
    public $isEmergencyModalOpen = false;

    public function setEmergencyModalOpen($isOpen)
    {
        $this->isEmergencyModalOpen = $isOpen;
    }

    // اصلاح متد saveEmergencyTimes
    

    public function copySlot()
    {
        $this->validate([
            'copySource.day' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'copySource.index' => 'required|integer',
            'selectedCopyDays' => 'required|array',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $sourceDay = $this->copySource['day'];
        $sourceIndex = $this->copySource['index'];

        // دریافت بازه زمانی  منبع
        $sourceSlot = $this->slots[$sourceDay][$sourceIndex] ?? null;
        if (!$sourceSlot || !$sourceSlot['start_time'] || !$sourceSlot['end_time'] || !$sourceSlot['max_appointments']) {
            $this->modalMessage = 'بازه زمانی  منبع نامعتبر است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
            return;
        }

        $newSlot = [
            'start' => $sourceSlot['start_time'],
            'end' => $sourceSlot['end_time'],
            'max_appointments' => $sourceSlot['max_appointments'],
        ];

        // فیلتر کردن روزهای انتخاب‌شده (به جز روز منبع)
        $targetDays = array_keys(array_filter($this->selectedCopyDays, fn ($value) => $value && $value !== $sourceDay));

        if (empty($targetDays)) {
            $this->modalMessage = 'هیچ روز مقصدی انتخاب نشده است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
            return;
        }

        foreach ($targetDays as $day) {
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

            $workHours = $workSchedule ? json_decode($workSchedule->work_hours, true) ?? [] : [];

            // بررسی تداخل زمانی
            foreach ($workHours as $existingSlot) {
                if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $existingSlot['start'], $existingSlot['end'])) {
                    $this->modalMessage = sprintf(
                        'تداخل زمانی در روز %s با بازه زمانی  موجود: از %s تا %s',
                        $this->getPersianDay($day),
                        $existingSlot['start'],
                        $existingSlot['end']
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

            if (!$workSchedule) {
                $workSchedule = DoctorWorkSchedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $day,
                    'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
                    'is_working' => true,
                    'work_hours' => json_encode([$newSlot]),
                ]);
            } else {
                $workHours[] = $newSlot;
                $workSchedule->update(['work_hours' => json_encode($workHours), 'is_working' => true]);
            }
        }

        $this->modalMessage = 'بازه زمانی  با موفقیت کپی شد';
        $this->modalType = 'success';
        $this->modalOpen = true;
        $this->dispatch('show-toastr', [
            'message' => $this->modalMessage,
            'type' => 'success',
        ]);
        $this->reset(['copySource', 'selectedCopyDays', 'selectAllCopyModal']);
        $this->mount();
    }

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

    public function copyWorkHours()
    {
        $this->validate([
            'sourceDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'targetDays' => 'required|array|min:1',
            'override' => 'boolean',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        DB::beginTransaction();
        try {
            $sourceWorkSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $this->sourceDay)
                ->where(function ($query) {
                    if ($this->selectedClinicId !== 'default') {
                        $query->where('clinic_id', $this->selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            if (!$sourceWorkSchedule || empty($sourceWorkSchedule->work_hours)) {
                $this->modalMessage = 'روز مبدأ یافت نشد یا فاقد ساعات کاری است';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            $sourceWorkHours = json_decode($sourceWorkSchedule->work_hours, true) ?? [];

            foreach ($this->targetDays as $targetDay) {
                $targetWorkSchedule = DoctorWorkSchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day' => $targetDay,
                        'clinic_id' => $this->selectedClinicId !== 'default' ? $this->selectedClinicId : null,
                    ],
                    [
                        'is_working' => true,
                        'work_hours' => json_encode([])
                    ]
                );

                if ($this->override) {
                    $targetWorkSchedule->work_hours = json_encode($sourceWorkHours);
                } else {
                    $existingWorkHours = json_decode($targetWorkSchedule->work_hours, true) ?? [];
                    foreach ($sourceWorkHours as $sourceSlot) {
                        foreach ($existingWorkHours as $existingSlot) {
                            $sourceStart = Carbon::createFromFormat('H:i', $sourceSlot['start']);
                            $sourceEnd = Carbon::createFromFormat('H:i', $sourceSlot['end']);
                            $existingStart = Carbon::createFromFormat('H:i', $existingSlot['start']);
                            $existingEnd = Carbon::createFromFormat('H:i', $existingSlot['end']);

                            if (
                                ($sourceStart >= $existingStart && $sourceStart < $existingEnd) ||
                                ($sourceEnd > $existingStart && $sourceEnd <= $existingEnd) ||
                                ($sourceStart <= $existingStart && $sourceEnd >= $existingEnd)
                            ) {
                                $this->modalMessage = 'بازه زمانی ' . $sourceStart->format('H:i') . ' تا ' . $sourceEnd->format('H:i') . ' با بازه‌های موجود تداخل دارد';
                                $this->modalType = 'error';
                                $this->modalOpen = true;
                                return;
                            }
                        }
                    }
                    $mergedWorkHours = array_merge($existingWorkHours, $sourceWorkHours);
                    $targetWorkSchedule->work_hours = json_encode($mergedWorkHours);
                }
                $targetWorkSchedule->save();
            }

            DB::commit();
            $this->modalMessage = 'ساعات کاری با موفقیت کپی شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->reset(['sourceDay', 'targetDays', 'override']);
            $this->mount();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->modalMessage = 'خطا در کپی ساعات کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
        }
    }

    public function copySingleSlot()
    {
        $this->validate([
            'sourceDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'targetDays' => 'required|array',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i',
            'maxAppointments' => 'required|integer|min:1',
            'override' => 'boolean',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $targetDays = array_diff($this->targetDays, [$this->sourceDay]);

        if (empty($targetDays)) {
            $this->modalMessage = 'هیچ روز مقصدی انتخاب نشده است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
            return;
        }

        $newSlot = [
            'start' => $this->startTime,
            'end' => $this->endTime,
            'max_appointments' => $this->maxAppointments,
        ];

        $conflictingSlots = [];
        foreach ($targetDays as $day) {
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

            if ($workSchedule && $workSchedule->work_hours) {
                $workHours = json_decode($workSchedule->work_hours, true);
                foreach ($workHours as $slot) {
                    if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                        $conflictingSlots[] = [
                            'day' => $this->getPersianDay($day),
                            'start' => $slot['start'],
                            'end' => $slot['end'],
                        ];
                    }
                }
            }
        }

        if (!empty($conflictingSlots) && !$this->override) {
            $conflictMessages = array_map(function ($slot) {
                return sprintf('روز %s: از %s تا %s', $slot['day'], $slot['start'], $slot['end']);
            }, $conflictingSlots);
            $this->modalMessage = 'تداخل زمانی در روزهای زیر وجود دارد: ' . implode(', ', $conflictMessages);
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
            return;
        }

        foreach ($targetDays as $day) {
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
                    'work_hours' => json_encode([$newSlot]),
                ]);
            } else {
                $workHours = json_decode($workSchedule->work_hours, true) ?? [];
                if ($this->override) {
                    $workHours = array_filter($workHours, function ($slot) use ($newSlot) {
                        return !$this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end']);
                    });
                }
                $workHours[] = $newSlot;
                $workSchedule->update(['work_hours' => json_encode(array_values($workHours)), 'is_working' => true]);
            }
        }

        $this->modalMessage = $this->override ? 'بازه‌ها جایگزین شدند' : 'بازه‌ها کپی شدند';
        $this->modalType = 'success';
        $this->modalOpen = true;
        $this->dispatch('show-toastr', [
            'message' => $this->modalMessage,
            'type' => 'success',
        ]);
        $this->reset(['sourceDay', 'targetDays', 'startTime', 'endTime', 'maxAppointments', 'override']);
        $this->mount();
    }

    private function isTimeConflict($newStart, $newEnd, $existingStart, $existingEnd)
    {
        try {
            $newStartTime = Carbon::createFromFormat('H:i', $newStart);
            $newEndTime = Carbon::createFromFormat('H:i', $newEnd);
            $existingStartTime = Carbon::createFromFormat('H:i', $existingStart);
            $existingEndTime = Carbon::createFromFormat('H:i', $existingEnd);

            // بررسی تداخل: اگر بازه جدید با بازه موجود همپوشانی داشته باشد
            $hasConflict = (
                ($newStartTime < $existingEndTime && $newEndTime > $existingStartTime) ||
                ($newStartTime == $existingStartTime && $newEndTime == $existingEndTime) // بازه‌های کاملاً یکسان
            );

           

            return $hasConflict;
        } catch (\Exception $e) {
           
            return false;
        }
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

    public function toggleHoliday()
    {
        $this->validate([
            'holidayDate' => 'required|date',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        $holidayRecord = DoctorHoliday::where('doctor_id', $doctor->id)
            ->where(function ($query) {
                if ($this->selectedClinicId === 'default') {
                    $query->whereNull('clinic_id');
                } elseif ($this->selectedClinicId) {
                    $query->where('clinic_id', $this->selectedClinicId);
                }
            })
            ->firstOrCreate([
                'doctor_id' => $doctor->id,
                'clinic_id' => ($this->selectedClinicId !== 'default' ? $this->selectedClinicId : null),
            ], [
                'holiday_dates' => json_encode([])
            ]);

        $holidayDates = json_decode($holidayRecord->holiday_dates, true) ?? [];

        if (in_array($this->holidayDate, $holidayDates)) {
            $holidayDates = array_diff($holidayDates, [$this->holidayDate]);
            $this->modalMessage = 'این تاریخ از حالت تعطیلی خارج شد';
            $isHoliday = false;
        } else {
            $holidayDates[] = $this->holidayDate;
            $this->modalMessage = 'این تاریخ تعطیل شد';
            $isHoliday = true;
            SpecialDailySchedule::where('date', $this->holidayDate)
                ->where(function ($query) {
                    if ($this->selectedClinicId === 'default') {
                        $query->whereNull('clinic_id');
                    } elseif ($this->selectedClinicId) {
                        $query->where('clinic_id', $this->selectedClinicId);
                    }
                })
                ->delete();
        }

        $holidayRecord->update(['holiday_dates' => json_encode(array_values($holidayDates))]);
        $this->modalType = 'success';
        $this->modalOpen = true;
        $this->reset(['holidayDate']);
        $this->loadHolidays();
        $this->dispatch('refresh-calendar');
    }

    public function cancelAppointments()
    {
        if (empty($this->appointmentIds)) {
            $this->modalMessage = 'هیچ نوبتی انتخاب نشده است';
            $this->modalType = 'error';
            $this->modalOpen = true;
            return;
        }

        $gregorianDate = $this->holidayDate;
        $jalaliDate = $this->holidayDate;
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $this->holidayDate)) {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $this->holidayDate)->toCarbon()->toDateString();
            $jalaliDate = $this->holidayDate;
        }

        $query = Appointment::withTrashed()
            ->whereIn('id', $this->appointmentIds)
            ->where('appointment_date', $gregorianDate);

        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } else {
            $query->where('clinic_id', $this->selectedClinicId);
        }

        $appointments = $query->get();

        if ($appointments->isEmpty()) {
            $this->modalMessage = 'هیچ نوبتی با این مشخصات یافت نشد';
            $this->modalType = 'error';
            $this->modalOpen = true;
            return;
        }

        $allCancelledOrAttended = $appointments->every(function ($appointment) {
            return $appointment->status === 'cancelled' || $appointment->status === 'attended';
        });

        if ($allCancelledOrAttended) {
            $this->modalMessage = 'نوبت‌ها یا قبلاً لغو شده‌اند یا ویزیت شده‌اند';
            $this->modalType = 'error';
            $this->modalOpen = true;
            return;
        }

        $recipients = [];
        $newlyCancelled = false;

        foreach ($appointments as $appointment) {
            if ($appointment->status !== 'cancelled' && $appointment->status !== 'attended') {
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

        $this->modalMessage = $newlyCancelled ? 'نوبت‌ها با موفقیت لغو شدند' : 'برخی نوبت‌ها قبلاً لغو یا ویزیت شده بودند';
        $this->modalType = 'success';
        $this->modalOpen = true;
        $this->reset(['appointmentIds']);
        $this->mount();
    }

    public function rescheduleAppointment()
    {
        $this->validate([
            'oldDate' => 'required',
            'newDate' => 'required|date_format:Y-m-d',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        try {
            $oldDateGregorian = $this->oldDate;
            if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $this->oldDate)) {
                $oldDateGregorian = Jalalian::fromFormat('Y/m/d', $this->oldDate)->toCarbon()->toDateString();
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+Z$/', $this->oldDate)) {
                $oldDateGregorian = Carbon::parse($this->oldDate)->toDateString();
            } else {
                $this->modalMessage = 'فرمت تاریخ قدیم نامعتبر است';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            $newDateGregorian = Carbon::parse($this->newDate)->toDateString();
            if ($oldDateGregorian === $newDateGregorian) {
                $this->modalMessage = 'تاریخ جدید نمی‌تواند با تاریخ فعلی یکسان باشد';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            $today = Carbon::today()->toDateString();
            if (Carbon::parse($newDateGregorian)->lt($today)) {
                $this->modalMessage = 'نمی‌توانید نوبت‌ها را به تاریخ گذشته منتقل کنید';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            if ($this->selectedClinicId !== 'default' && !Clinic::where('id', $this->selectedClinicId)->exists()) {
                $this->modalMessage = 'کلینیک نامعتبر است';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            $appointmentsQuery = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $oldDateGregorian)
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                    $query->where('clinic_id', $this->selectedClinicId);
                }, function ($query) {
                    $query->whereNull('clinic_id');
                });

            $appointments = $appointmentsQuery->get();

            if ($appointments->isEmpty()) {
                $this->modalMessage = 'هیچ نوبتی برای این تاریخ یافت نشد';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            $workHours = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', strtolower(Carbon::parse($newDateGregorian)->format('l')))
                ->when($this->selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                }, function ($query) {
                    $query->where('clinic_id', $this->selectedClinicId);
                })
                ->first();

            if (!$workHours) {
                $this->modalMessage = 'ساعات کاری برای تاریخ جدید تعریف نشده است';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            $recipients = [];
            $oldDateJalali = Jalalian::fromDateTime($oldDateGregorian)->format('Y/m/d');
            $newDateJalali = Jalalian::fromDateTime($newDateGregorian)->format('Y/m/d');

            foreach ($appointments as $appointment) {
                if ($appointment->status === 'attended') {
                    continue;
                }
                $appointment->appointment_date = $newDateGregorian;
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

            $this->modalMessage = 'نوبت‌ها با موفقیت جابجا شدند';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->reset(['oldDate', 'newDate']);
            $this->mount();
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در جابجایی نوبت‌ها';
            $this->modalType = 'error';
            $this->modalOpen = true;
        }
    }

    public function updateFirstAvailableAppointment()
    {
        $this->validate([
            'oldDate' => 'required|date',
            'newDate' => 'required|date',
        ]);

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        try {
            $appointmentsQuery = Appointment::where('doctor_id', $doctor->id)
                ->where('appointment_date', $this->oldDate)
                ->when($this->selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                    $query->where('clinic_id', $this->selectedClinicId);
                });

            $appointments = $appointmentsQuery->get();

            if ($appointments->isEmpty()) {
                $this->modalMessage = 'هیچ نوبتی برای بروزرسانی یافت نشد';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            $selectedDate = Carbon::parse($this->newDate);
            $dayOfWeek = strtolower($selectedDate->format('l'));
            $workHours = DoctorWorkSchedule::where('doctor_id', $doctor->id)
                ->where('day', $dayOfWeek)
                ->when($this->selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                    $query->where('clinic_id', $this->selectedClinicId);
                })
                ->first();

            if (!$workHours) {
                $this->modalMessage = 'ساعات کاری پزشک برای تاریخ جدید یافت نشد';
                $this->modalType = 'error';
                $this->modalOpen = true;
                return;
            }

            $recipients = [];
            $oldDateJalali = Jalalian::fromDateTime($this->oldDate)->format('Y/m/d');
            $newDateJalali = Jalalian::fromDateTime($this->newDate)->format('Y/m/d');

            foreach ($appointments as $appointment) {
                $appointment->appointment_date = $this->newDate;
                $appointment->save();

                if ($appointment->patient && $appointment->patient->mobile) {
                    $recipients[] = $appointment->patient->mobile;
                }
            }

            if (!empty($recipients)) {
                $messageContent = "کاربر گرامی، نوبت شما از تاریخ {$oldDateJalali} به تاریخ {$newDateJalali} تغییر یافت.";
                foreach ($recipients as $recipient) {
                    $user = User::where('mobile', $recipient)->first();
                    $userFullName = $user ? $user->first_name . " " . $user->last_name : 'کاربر گرامی';

                    $messagesService = new MessageService(
                        SmsService::create(100252, $recipient, [$userFullName, $oldDateJalali, $newDateJalali, 'به نوبه'])
                    );
                    $messagesService->send();
                }
            }

            $this->modalMessage = 'نوبت‌ها با موفقیت بروزرسانی شدند';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->reset(['oldDate', 'newDate']);
            $this->mount();
        } catch (\Exception $e) {
            $this->modalMessage = 'خطا در بروزرسانی نوبت‌ها';
            $this->modalType = 'error';
            $this->modalOpen = true;
        }
    }

    public function getNextAvailableDate()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctor->id)
            ->when($this->selectedClinicId === 'default', function ($query) {
                $query->whereNull('clinic_id');
            })
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                $query->where('clinic_id', $this->selectedClinicId);
            });

        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);

        $daysToCheck = DoctorAppointmentConfig::where('doctor_id', $doctor->id)->value('calendar_days') ?? 30;
        $today = Carbon::now()->startOfDay();
        $datesToCheck = collect();

        for ($i = 1; $i <= $daysToCheck; $i++) {
            $date = $today->copy()->addDays($i)->format('Y-m-d');
            $datesToCheck->push($date);
        }

        $this->nextAvailableDate = $datesToCheck->first(function ($date) use ($doctor, $holidayDates) {
            if (in_array($date, $holidayDates)) {
                return false;
            }

            $appointmentQuery = Appointment::where('doctor_id', $doctor->id)
                ->where('appointment_date', $date)
                ->when($this->selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                    $query->where('clinic_id', $this->selectedClinicId);
                });

            return !$appointmentQuery->exists();
        });

        if ($this->nextAvailableDate) {
            $this->modalMessage = 'تاریخ بعدی در دسترس: ' . $this->nextAvailableDate;
            $this->modalType = 'success';
        } else {
            $this->modalMessage = 'هیچ نوبت خالی یافت نشد';
            $this->modalType = 'error';
        }
        $this->modalOpen = true;
    }

    public function loadHolidays()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $holidayQuery = DoctorHoliday::where('doctor_id', $doctor->id);

        if ($this->selectedClinicId === 'default') {
            $holidayQuery->whereNull('clinic_id');
        } elseif ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
            $holidayQuery->where('clinic_id', $this->selectedClinicId);
        }

        $holidayRecord = $holidayQuery->first();
        $this->holidays = $holidayRecord && !empty($holidayRecord->holiday_dates) ? json_decode($holidayRecord->holiday_dates, true) : [];
        $this->dispatch('refresh-calendar', ['holidays' => $this->holidays]);
    }

    public function getHolidayStatus($date)
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        $holidayRecord = DoctorHoliday::where('doctor_id', $doctor->id)
            ->where(function ($query) {
                if ($this->selectedClinicId === 'default') {
                    $query->whereNull('clinic_id');
                } elseif ($this->selectedClinicId) {
                    $query->where('clinic_id', $this->selectedClinicId);
                }
            })
            ->first();

        $holidayDates = json_decode($holidayRecord->holiday_dates ?? '[]', true);
        $isHoliday = in_array($date, $holidayDates);

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->where(function ($query) {
                if ($this->selectedClinicId === 'default') {
                    $query->whereNull('clinic_id');
                } elseif ($this->selectedClinicId) {
                    $query->where('clinic_id', $this->selectedClinicId);
                }
            })
            ->get();

        $dayOfWeek = strtolower(Carbon::parse($date)->englishDayOfWeek);
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $dayOfWeek)
            ->where(function ($query) {
                if ($this->selectedClinicId === 'default') {
                    $query->whereNull('clinic_id');
                } elseif ($this->selectedClinicId) {
                    $query->where('clinic_id', $this->selectedClinicId);
                }
            })
            ->first();

        $hasWorkHours = $workSchedule && !empty(json_decode($workSchedule->work_hours, true));

        $this->modalMessage = $isHoliday ? 'این تاریخ تعطیل است' : 'این تاریخ تعطیل نیست';
        $this->modalType = $isHoliday ? 'warning' : 'success';
        $this->modalOpen = true;
        $this->dispatch('update-holiday-status', [
            'is_holiday' => $isHoliday,
            'has_appointments' => !$appointments->isEmpty(),
            'has_work_hours' => $hasWorkHours,
        ]);
    }

    public function render()
    {
        return view('livewire.dr.panel.work-hours');
    }
}
