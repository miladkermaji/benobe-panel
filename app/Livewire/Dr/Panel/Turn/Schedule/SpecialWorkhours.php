<?php

namespace App\Livewire\Dr\Panel\Turn\Schedule;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\Log;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Doctor;

class SpecialWorkhours extends Component
{
    public $selectedDate;
    public $time;
    public $workSchedule = ['status' => false, 'data' => []];
    public $clinicId = 'default';
    public $isProcessing = false;
    public $emergencyTimes = [];
    public $selectedEmergencyTimes = [];
    public $emergencyModalDay;
    public $emergencyModalIndex;
    public $isEmergencyModalOpen = false;
    public $doctorId;
    public $doctor;
    public $calculator = [
        'day' => null,
        'index' => null,
        'start_time' => null,
        'end_time' => null,
        'appointment_count' => null,
        'time_per_appointment' => null,
        'calculation_mode' => 'count',
    ];
    public $scheduleModalDay;
    public $scheduleModalIndex;
    public $selectedScheduleDays = [];
    public $selectAllScheduleModal = false;
    public $hasWorkHoursMessage = false;

    protected $listeners = [
        'refresh-work-hours' => '$refresh',
        'refresh-timepicker' => '$refresh',
        'close-calculator-modal' => 'closeCalculatorModal',
        'close-emergency-modal' => 'closeEmergencyModal',
        'close-schedule-modal' => 'closeScheduleModal',
        'updateSelectedDate' => 'updateSelectedDate',
        'refreshWorkhours' => '$refresh',
        'set-calculator-values' => 'setCalculatorValues',
    ];

    public function mount($selectedDate, $workSchedule, $clinicId = 'default')
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }
        $this->doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
        $this->doctor = Doctor::with(['clinics', 'workSchedules'])->find($this->doctorId);

        $this->selectedDate = $selectedDate;
        $this->workSchedule = $workSchedule;
        $this->clinicId = $clinicId;
        $this->emergencyTimes = $this->getEmergencyTimes();
        $this->hasWorkHoursMessage = $workSchedule['status'] && !empty($workSchedule['data']['work_hours']);
        Log::info("Mounting SpecialWorkhours", [
            'selectedDate' => $this->selectedDate,
            'workSchedule' => $this->workSchedule,
            'clinicId' => $this->clinicId,
        ]);
    }

    public function updateSelectedDate($date, $workSchedule = null)
    {
        try {
            $parsedDate = Carbon::parse($date);
            if (!$parsedDate->isValid()) {
                throw new \Exception("Invalid date format: {$date}");
            }
            $this->selectedDate = $parsedDate->toDateString();
            if ($workSchedule) {
                $this->workSchedule = $workSchedule;
            } else {
                $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            }
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->emergencyTimes = $this->getEmergencyTimes();
            Log::info("Selected date updated in SpecialWorkhours: {$this->selectedDate}", ['workSchedule' => $this->workSchedule]);
        } catch (\Exception $e) {
            Log::error("Error in updateSelectedDate: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در انتخاب تاریخ: ' . $e->getMessage());
        }
    }

    public function loadWorkSchedule()
    {
        $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
        $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
        $this->emergencyTimes = $this->getEmergencyTimes();
        Log::info("Work schedule loaded: ", $this->workSchedule);
    }

    private function getAuthenticatedDoctor()
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }
        return $doctor;
    }

    public function getWorkScheduleForDate($gregorianDate)
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $date = Carbon::parse($gregorianDate);
            $dayOfWeek = strtolower($date->format('l'));
            $cacheKey = "work_schedule_{$doctorId}_{$gregorianDate}_{$this->clinicId}";

            // تلاش برای دریافت از کش
            $cachedSchedule = Cache::remember($cacheKey, now()->addHours(24), function () use ($doctorId, $gregorianDate, $dayOfWeek) {
                Log::info("Fetching work schedule from DB for date: {$gregorianDate}, day: {$dayOfWeek}, doctor_id: {$doctorId}, clinic_id: {$this->clinicId}");

                $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                    ->where('date', $gregorianDate)
                    ->when($this->clinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                    ->when($this->clinicId && $this->clinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->clinicId))
                    ->first();

                if ($specialSchedule) {
                    $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
                    $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
                    foreach ($workHours as $index => $slot) {
                        if (!isset($appointmentSettings[$index])) {
                            $appointmentSettings[$index] = [
                                'max_appointments' => $slot['max_appointments'] ?? 0,
                                'appointment_duration' => 0,
                            ];
                        } else {
                            $appointmentSettings[$index]['max_appointments'] = $slot['max_appointments'] ?? $appointmentSettings[$index]['max_appointments'] ?? 0;
                        }
                    }
                    return [
                        'status' => true,
                        'data' => [
                            'day' => $dayOfWeek,
                            'work_hours' => $workHours,
                            'appointment_settings' => $appointmentSettings,
                        ],
                    ];
                }

                $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                    ->where('day', $dayOfWeek)
                    ->where('is_working', true)
                    ->when($this->clinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                    ->when($this->clinicId && $this->clinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->clinicId))
                    ->first();

                if (!$schedule) {
                    Log::info("No work schedule found for {$dayOfWeek} with doctor_id: {$doctorId}, clinic_id: {$this->clinicId}");
                    return ['status' => false, 'data' => []];
                }

                $workHours = $schedule->work_hours ? json_decode($schedule->work_hours, true) : [];
                $appointmentSettings = $schedule->appointment_settings ? json_decode($schedule->appointment_settings, true) : [];
                foreach ($workHours as $index => $slot) {
                    if (!isset($appointmentSettings[$index])) {
                        $appointmentSettings[$index] = [
                            'max_appointments' => $slot['max_appointments'] ?? 0,
                            'appointment_duration' => 0,
                        ];
                    } else {
                        $appointmentSettings[$index]['max_appointments'] = $slot['max_appointments'] ?? $appointmentSettings[$index]['max_appointments'] ?? 0;
                    }
                }
                return [
                    'status' => true,
                    'data' => [
                        'day' => $dayOfWeek,
                        'work_hours' => $workHours,
                        'appointment_settings' => $appointmentSettings,
                    ],
                ];
            });

            return $cachedSchedule;

        } catch (\Exception $e) {
            Log::error("Error in getWorkScheduleForDate: " . $e->getMessage());
            return ['status' => false, 'data' => []];
        }
    }

    public function getEmergencyTimes()
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->clinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->clinicId && $this->clinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->clinicId))
                ->first();

            $emergencyTimes = $specialSchedule && $specialSchedule->emergency_times
                ? json_decode($specialSchedule->emergency_times, true) ?? []
                : [];

            $this->selectedEmergencyTimes = $emergencyTimes[$this->emergencyModalIndex] ?? [];

            // Generate possible emergency times based on work hours and appointment duration
            if ($this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours'])) {
                $slot = $this->workSchedule['data']['work_hours'][$this->emergencyModalIndex] ?? null;
                $appointmentSettings = $this->workSchedule['data']['appointment_settings'][$this->emergencyModalIndex] ?? null;

                if ($slot && $appointmentSettings && !empty($slot['start']) && !empty($slot['end']) && $appointmentSettings['appointment_duration'] > 0) {
                    $start = Carbon::createFromFormat('H:i', $slot['start']);
                    $end = Carbon::createFromFormat('H:i', $slot['end']);
                    $duration = (int) $appointmentSettings['appointment_duration'];
                    $maxAppointments = (int) ($slot['max_appointments'] ?? 0);

                    $possibleTimes = [];
                    $current = $start->copy();

                    while ($current->lessThan($end) && count($possibleTimes) < $maxAppointments) {
                        $possibleTimes[] = $current->format('H:i');
                        $current->addMinutes($duration);
                    }

                    return [
                        'possible' => $possibleTimes,
                        'selected' => $this->selectedEmergencyTimes,
                    ];
                }
            }

            return [
                'possible' => [],
                'selected' => $this->selectedEmergencyTimes,
            ];
        } catch (\Exception $e) {
            Log::error("Error in getEmergencyTimes: " . $e->getMessage());
            return [
                'possible' => [],
                'selected' => [],
            ];
        }
    }

    public function addSlot()
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            if (empty($this->selectedDate)) {
                Log::error("Selected date is empty or invalid");
                $this->dispatch('show-toastr', type: 'error', message: 'تاریخ انتخاب‌شده نامعتبر است.');
                return;
            }

            $parsedDate = Carbon::parse($this->selectedDate);
            if (!$parsedDate->isValid()) {
                Log::error("Invalid date format for selectedDate: {$this->selectedDate}");
                $this->dispatch('show-toastr', type: 'error', message: 'فرمت تاریخ نامعتبر است.');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->clinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->clinicId && $this->clinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->clinicId))
                ->first();

            if ($specialSchedule) {
                $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
                foreach ($workHours as $index => $slot) {
                    if (empty($slot['start']) || empty($slot['end']) || empty($slot['max_appointments']) || $slot['max_appointments'] <= 0) {
                        $this->dispatch('show-toastr', type: 'error', message: 'لطفاً ابتدا ردیف قبلی را کامل کنید.');
                        return;
                    }
                }
            }

            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $parsedDate->toDateString(),
                    'clinic_id' => $this->clinicId === 'default' ? null : $this->clinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]
            );

            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];

            $newIndex = count($workHours);
            $workHours[$newIndex] = [
                'start' => '',
                'end' => '',
                'max_appointments' => '',
            ];
            $appointmentSettings[$newIndex] = [
                'max_appointments' => '',
                'appointment_duration' => '',
            ];

            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->save();

            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->dispatch('show-toastr', type: 'success', message: 'بازه زمانی جدید اضافه شد.');
            $this->dispatch('refresh-timepicker');
        } catch (\Exception $e) {
            Log::error("Error in addSlot: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در افزودن بازه زمانی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function removeSlot($index)
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->clinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->clinicId && $this->clinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->clinicId))
                ->first();

            if (!$specialSchedule) {
                $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این تاریخ یافت نشد.');
                return;
            }

            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];

            if (isset($workHours[$index])) {
                unset($workHours[$index]);
                unset($appointmentSettings[$index]);
                $workHours = array_values($workHours);
                $appointmentSettings = array_values($appointmentSettings);

                $specialSchedule->work_hours = json_encode($workHours);
                $specialSchedule->appointment_settings = json_encode($appointmentSettings);
                $specialSchedule->save();

                $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
                $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
                $this->dispatch('show-toastr', type: 'success', message: 'بازه زمانی حذف شد.');
            } else {
                $this->dispatch('show-toastr', type: 'error', message: 'بازه زمانی نامعتبر است.');
            }
        } catch (\Exception $e) {
            Log::error("Error in removeSlot: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در حذف بازه زمانی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function setCalculatorValues($values = [])
    {
        if ($this->isProcessing) {
            return;
        }

        try {
            if (is_array($values) && isset($values[0]) && is_array($values[0])) {
                $values = $values[0];
            } elseif (!is_array($values)) {
                Log::warning("Invalid values format in setCalculatorValues", ['values' => $values]);
                $values = [];
            }

            $this->calculator['appointment_count'] = isset($values['appointment_count']) ? (int) $values['appointment_count'] : null;
            $this->calculator['time_per_appointment'] = isset($values['time_per_appointment']) ? (int) $values['time_per_appointment'] : null;
            $this->calculator['calculation_mode'] = isset($values['calculation_mode']) ? $values['calculation_mode'] : 'count';

            Log::info("Calculator values updated", $this->calculator);
        } catch (\Exception $e) {
            Log::error("Error in setCalculatorValues: " . $e->getMessage(), ['values' => $values]);
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در تنظیم مقادیر محاسبه‌گر: ' . $e->getMessage());
        }
    }

    public function saveCalculator()
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $day = $this->calculator['day'];
            $index = $this->calculator['index'];
            $appointmentCount = $this->calculator['appointment_count'];
            $timePerAppointment = $this->calculator['time_per_appointment'];
            $calculationMode = $this->calculator['calculation_mode'];

            if (empty($day) || $index === null || !is_numeric($appointmentCount) || $appointmentCount <= 0) {
                Log::error("Invalid or incomplete calculator data", $this->calculator);
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً تعداد نوبت‌ها را به‌درستی وارد کنید.');
                return;
            }

            if ($calculationMode === 'time' && (!is_numeric($timePerAppointment) || $timePerAppointment <= 0)) {
                Log::error("Invalid time_per_appointment in time mode", $this->calculator);
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً زمان هر نوبت را به‌درستی وارد کنید.');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'clinic_id' => $this->clinicId === 'default' ? null : $this->clinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]
            );

            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [];

            $startTime = $this->workSchedule['data']['work_hours'][$index]['start'] ?? $this->calculator['start_time'] ?? '00:00';
            $endTime = $this->workSchedule['data']['work_hours'][$index]['end'] ?? $this->calculator['end_time'] ?? '23:59';

            if (empty($startTime) || empty($endTime)) {
                Log::error("Invalid start or end time", ['start' => $startTime, 'end' => $endTime]);
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً زمان شروع و پایان را وارد کنید.');
                return;
            }

            // Generate emergency times
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
            $duration = $timePerAppointment ?? 0;
            $possibleEmergencyTimes = [];
            $current = $start->copy();

            while ($current->lessThan($end) && count($possibleEmergencyTimes) < $appointmentCount) {
                $possibleEmergencyTimes[] = $current->format('H:i');
                $current->addMinutes($duration);
            }

            $emergencyTimes[$index] = $this->selectedEmergencyTimes;

            $workHours[$index] = [
                'start' => $startTime,
                'end' => $endTime,
                'max_appointments' => $appointmentCount,
            ];

            $appointmentSettings[$index] = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'work_hour_key' => $index,
                'max_appointments' => $appointmentCount,
                'appointment_duration' => $timePerAppointment ?? 0,
            ];

            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();

            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->dispatch('show-toastr', type: 'success', message: 'تنظیمات نوبت‌دهی ذخیره شد.');
            $this->dispatch('close-calculator-modal');
        } catch (\Exception $e) {
            Log::error("Error in saveCalculator: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در ذخیره تنظیمات: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function saveEmergencyTimes()
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'clinic_id' => $this->clinicId === 'default' ? null : $this->clinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]
            );

            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [];
            $emergencyTimes[$this->emergencyModalIndex] = $this->selectedEmergencyTimes;

            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();

            $this->dispatch('show-toastr', type: 'success', message: 'زمان‌های اورژانسی ذخیره شد.');
            $this->dispatch('close-emergency-modal');
        } catch (\Exception $e) {
            Log::error("Error in saveEmergencyTimes: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در ذخیره زمان‌های اورژانسی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function saveSchedule($startTime, $endTime)
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'clinic_id' => $this->clinicId === 'default' ? null : $this->clinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]
            );

            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $selectedDays = array_keys(array_filter($this->selectedScheduleDays, fn ($value) => $value));

            if (empty($selectedDays)) {
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً حداقل یک روز انتخاب کنید.');
                return;
            }

            $appointmentSettings[$this->scheduleModalIndex] = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'days' => $selectedDays,
                'work_hour_key' => $this->scheduleModalIndex,
                'max_appointments' => $appointmentSettings[$this->scheduleModalIndex]['max_appointments'] ?? 0,
                'appointment_duration' => $appointmentSettings[$this->scheduleModalIndex]['appointment_duration'] ?? 0,
            ];

            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->save();

            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->dispatch('show-toastr', type: 'success', message: 'تنظیمات زمان‌بندی ذخیره شد.');
            $this->dispatch('close-schedule-modal');
        } catch (\Exception $e) {
            Log::error("Error in saveSchedule: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در ذخیره تنظیمات زمان‌بندی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function deleteScheduleSetting($day, $index)
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->clinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->clinicId && $this->clinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->clinicId))
                ->first();

            if (!$specialSchedule) {
                $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این تاریخ یافت نشد.');
                return;
            }

            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];

            if (isset($appointmentSettings[$index])) {
                unset($appointmentSettings[$index]);
                $appointmentSettings = array_values($appointmentSettings);
                $specialSchedule->appointment_settings = json_encode($appointmentSettings);
                $specialSchedule->save();

                $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
                $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
                $this->dispatch('show-toastr', type: 'success', message: 'تنظیم زمان‌بندی حذف شد.');
            } else {
                $this->dispatch('show-toastr', type: 'error', message: 'تنظیم زمان‌بندی نامعتبر است.');
            }
        } catch (\Exception $e) {
            Log::error("Error in deleteScheduleSetting: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در حذف تنظیم زمان‌بندی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function setCalculationMode($mode)
    {
        if ($this->isProcessing) {
            return;
        }

        $this->calculator['calculation_mode'] = $mode;
        Log::info("Calculation mode set to: {$mode}");
    }

    public function openCalculatorModal($day, $index)
    {
        $this->calculator = [
            'day' => $day,
            'index' => $index,
            'start_time' => $this->workSchedule['data']['work_hours'][$index]['start'] ?? '00:00',
            'end_time' => $this->workSchedule['data']['work_hours'][$index]['end'] ?? '23:59',
            'appointment_count' => $this->workSchedule['data']['work_hours'][$index]['max_appointments'] ?? null,
            'time_per_appointment' => $this->workSchedule['data']['appointment_settings'][$index]['appointment_duration'] ?? null,
            'calculation_mode' => $this->workSchedule['data']['appointment_settings'][$index]['appointment_duration'] ? 'time' : 'count',
        ];

        $this->dispatch('open-modal', id: 'CalculatorModal');
        $this->dispatch('initialize-calculator', [
            'start_time' => $this->calculator['start_time'],
            'end_time' => $this->calculator['end_time'],
            'index' => $index,
            'day' => $day,
        ]);
    }

    public function openEmergencyModal($day, $index)
    {
        $this->isEmergencyModalOpen = true;
        $this->emergencyTimes = $this->getPossibleEmergencyTimes($day, $index);
        $this->dispatch('open-modal', id: 'emergencyModal');
    }

    public function openScheduleModal($day, $index)
    {
        $this->scheduleModalDay = $day;
        $this->scheduleModalIndex = $index;
        $this->dispatch('open-modal', id: 'scheduleModal');
    }

    public function closeCalculatorModal()
    {
        $this->calculator = [
            'day' => null,
            'index' => null,
            'start_time' => null,
            'end_time' => null,
            'appointment_count' => null,
            'time_per_appointment' => null,
            'calculation_mode' => 'count',
        ];
        $this->dispatch('close-modal', id: 'CalculatorModal');
    }

    public function closeEmergencyModal()
    {
        $this->isEmergencyModalOpen = false;
        $this->emergencyModalDay = null;
        $this->emergencyModalIndex = null;
        $this->emergencyTimes = [];
        $this->selectedEmergencyTimes = [];
        $this->dispatch('close-modal', id: 'emergencyModal');
    }

    public function closeScheduleModal()
    {
        $this->scheduleModalDay = null;
        $this->scheduleModalIndex = null;
        $this->selectedScheduleDays = [];
        $this->selectAllScheduleModal = false;
        $this->dispatch('close-modal', id: 'scheduleModal');
    }

    public function updatedSelectedEmergencyTimes()
    {
        Log::info("Selected emergency times updated", ['selectedEmergencyTimes' => $this->selectedEmergencyTimes]);
    }

    public function updatedWorkScheduleDataWorkHours($value, $nested)
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'clinic_id' => $this->clinicId === 'default' ? null : $this->clinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]
            );

            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];

            [$index, $field] = explode('.', $nested);
            $workHours[$index][$field] = $value;

            if (!isset($appointmentSettings[$index])) {
                $appointmentSettings[$index] = [
                    'max_appointments' => $workHours[$index]['max_appointments'] ?? 0,
                    'appointment_duration' => 0,
                ];
            }

            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->save();

            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);

            $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->clinicId}";
            Cache::forget($cacheKey);

            $this->dispatch('show-toastr', type: 'success', message: 'تغییرات ساعت کاری ذخیره شد.');
        } catch (\Exception $e) {
            Log::error("Error in updatedWorkScheduleDataWorkHours: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در ذخیره تغییرات: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render()
    {
        return view('livewire.dr.panel.turn.schedule.special-workhours');
    }
}
