<?php

namespace App\Livewire\Dr\Panel\Turn\Schedule;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorWorkSchedule;
use App\Models\SpecialDailySchedule;

class SpecialWorkhours extends Component
{
    public $selectedDate;
    public $workSchedule = ['status' => false, 'data' => []];
    public $clinicId = 'default';
    public $isEditable = false; // مقدار پیش‌فرض برای غیرفعال بودن
    public $isProcessing = false;
    public $emergencyTimes = [];
    public $emergencyModalDay;
    public $emergencyModalIndex;
    public $isEmergencyModalOpen = false;
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

    protected $listeners = [
        'refresh-work-hours' => '$refresh',
        'refresh-timepicker' => '$refresh',
        'close-calculator-modal' => 'closeCalculatorModal',
        'close-emergency-modal' => 'closeEmergencyModal',
        'close-schedule-modal' => 'closeScheduleModal',
        'updateSelectedDate' => 'updateSelectedDate',
        'enableWorkHoursEditing' => 'enableEditing',
        'refreshWorkhours' => '$refresh', // listener جدید
    ];



    public function mount($selectedDate, $workSchedule, $clinicId = 'default', $isEditable = false)
    {
        $this->selectedDate = $selectedDate;
        $this->workSchedule = $workSchedule;
        $this->clinicId = $clinicId;
        $this->isEditable = $isEditable;
        $this->emergencyTimes = $this->getEmergencyTimes();
        Log::info("Mounting SpecialWorkhours", [
            'selectedDate' => $this->selectedDate,
            'workSchedule' => $this->workSchedule,
            'clinicId' => $this->clinicId,
            'isEditable' => $this->isEditable,
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
            $this->emergencyTimes = $this->getEmergencyTimes();
            // حذف توستر برای تجربه کاربری بهتر
            // $this->dispatch('show-toastr', type: 'success', message: 'تاریخ انتخاب‌شده به‌روزرسانی شد.');
            Log::info("Selected date updated in SpecialWorkhours: {$this->selectedDate}", [
                'workSchedule' => $this->workSchedule,
            ]);
        } catch (\Exception $e) {
            Log::error("Error in updateSelectedDate: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در انتخاب تاریخ: ' . $e->getMessage());
        }
    }

    public function loadWorkSchedule()
    {
        $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
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
            $dayOfWeek = strtolower($date->format('l')); // مثلاً "thursday"

            Log::info("Fetching work schedule for date: {$gregorianDate}, day: {$dayOfWeek}, doctor_id: {$doctorId}, clinic_id: {$this->clinicId}");

            // ابتدا بررسی جدول special_daily_schedules
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

            // اگر در special_daily_schedules نبود، از doctor_work_schedules بگیر
            $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayOfWeek)
                ->where('is_working', true)
                ->when($this->clinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->clinicId && $this->clinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->clinicId))
                ->first();

            if (!$schedule) {
                Log::info("No work schedule found for {$dayOfWeek} with doctor_id: {$doctorId}, clinic_id: {$this->clinicId}");
                return [
                    'status' => false,
                    'data' => [],
                ];
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
        } catch (\Exception $e) {
            Log::error("Error in getWorkScheduleForDate: " . $e->getMessage());
            return [
                'status' => false,
                'data' => [],
            ];
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

            return $specialSchedule && $specialSchedule->emergency_times
                ? json_decode($specialSchedule->emergency_times, true) ?? []
                : [];
        } catch (\Exception $e) {
            Log::error("Error in getEmergencyTimes: " . $e->getMessage());
            return [];
        }
    }

    public function addSlot()
    {
        if ($this->isProcessing || !$this->isEditable) {
            return;
        }

        $this->isProcessing = true;
        try {
            // اعتبارسنجی selectedDate
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

            // اعتبارسنجی ردیف‌های قبلی
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->clinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->clinicId && $this->clinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->clinicId))
                ->first();

            if ($specialSchedule) {
                $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
                foreach ($workHours as $index => $slot) {
                    if (
                        empty($slot['start']) ||
                        empty($slot['end']) ||
                        empty($slot['max_appointments']) ||
                        $slot['max_appointments'] <= 0
                    ) {
                        $this->dispatch('show-toastr', type: 'error', message: 'لطفاً ابتدا ردیف قبلی را کامل کنید.');
                        return;
                    }
                }
            }

            // ایجاد یا به‌روزرسانی برنامه کاری
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
                'start' => '08:00',
                'end' => '09:00',
                'max_appointments' => 0,
            ];
            $appointmentSettings[$newIndex] = [
                'max_appointments' => 0,
                'appointment_duration' => 0,
            ];

            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->save();

            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
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
        if ($this->isProcessing || !$this->isEditable) {
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

    public function saveCalculator()
    {
        if ($this->isProcessing || !$this->isEditable) {
            return;
        }

        $this->isProcessing = true;
        try {
            $day = $this->calculator['day'];
            $index = $this->calculator['index'];
            $appointmentCount = $this->calculator['appointment_count'];
            $timePerAppointment = $this->calculator['time_per_appointment'];

            if (
                empty($day) ||
                $index === null ||
                !is_numeric($appointmentCount) ||
                $appointmentCount <= 0 ||
                !is_numeric($timePerAppointment) ||
                $timePerAppointment <= 0
            ) {
                Log::error("Invalid or incomplete calculator data", $this->calculator);
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً تعداد نوبت‌ها و زمان هر نوبت را به‌درستی وارد کنید.');
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

            if (isset($workHours[$index])) {
                $workHours[$index]['max_appointments'] = $appointmentCount;
            } else {
                Log::warning("Work hour index {$index} not found in work_hours", $workHours);
                $this->dispatch('show-toastr', type: 'error', message: 'بازه زمانی معتبر نیست.');
                return;
            }

            $appointmentSettings[$index] = [
                'start_time' => $workHours[$index]['start'] ?? '00:00',
                'end_time' => $workHours[$index]['end'] ?? '23:59',
                'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'work_hour_key' => $index,
                'max_appointments' => $appointmentCount,
                'appointment_duration' => $timePerAppointment,
            ];

            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->save();

            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
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
        if ($this->isProcessing || !$this->isEditable) {
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

            $specialSchedule->emergency_times = json_encode($this->emergencyTimes);
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

    public function openScheduleModal($day, $index)
    {
        if ($this->isProcessing || !$this->isEditable) {
            return;
        }

        $this->scheduleModalDay = $day;
        $this->scheduleModalIndex = $index;
        $this->selectedScheduleDays = [];
        $this->selectAllScheduleModal = false;
        $this->dispatch('refresh-schedule-settings');
    }

    public function saveSchedule($startTime, $endTime)
    {
        if ($this->isProcessing || !$this->isEditable) {
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
        if ($this->isProcessing || !$this->isEditable) {
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

    public function enableEditing()
    {
        $this->isEditable = true;
        Log::info("Work hours editing enabled for date: {$this->selectedDate}");
        $this->dispatch('show-toastr', type: 'info', message: 'حالت ویرایش فعال شد.');
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
    }

    public function closeEmergencyModal()
    {
        $this->isEmergencyModalOpen = false;
        $this->emergencyModalDay = null;
        $this->emergencyModalIndex = null;
        $this->emergencyTimes = [];
    }

    public function closeScheduleModal()
    {
        $this->scheduleModalDay = null;
        $this->scheduleModalIndex = null;
        $this->selectedScheduleDays = [];
        $this->selectAllScheduleModal = false;
    }
    public function updatedSelectedDate($value)
    {
        $this->selectedDate = $value;
        $this->loadWorkSchedule();
        Log::info("Selected date updated in SpecialWorkhours: {$this->selectedDate}");
    }
    public function render()
    {
        return view('livewire.dr.panel.turn.schedule.special-workhours');
    }
}
