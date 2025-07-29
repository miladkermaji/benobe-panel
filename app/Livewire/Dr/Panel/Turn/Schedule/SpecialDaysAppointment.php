<?php

namespace App\Livewire\Dr\Panel\Turn\Schedule;

use Carbon\Carbon;
use App\Models\Doctor;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Traits\HasSelectedClinic;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\Log;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Auth;

class SpecialDaysAppointment extends Component
{
    use HasSelectedClinic;
    public $time;
    public $editingSettingIndex = null;
    public $calendarYear;
    public $isLoading = false;
    public $calendarMonth;
    public $selectedClinicId = 'default';
    public $holidaysData = ['status' => true, 'holidays' => []];
    public $appointmentsData = ['status' => true, 'data' => []];
    public $selectedDate;
    public $showModal = false;
    public $isProcessing = false;
    public $workSchedule = ['status' => false, 'data' => []];
    public $emergencyTimes = [];
    public $selectedEmergencyTimes = [];
    public $emergencyModalDay;
    public $emergencyModalIndex;
    public $isEmergencyModalOpen = false;
    public $isFromSpecialDailySchedule = false; // متغیر جدید
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
    public $showAddSlotModal = false;
    public $savePreviousRows = true;
    public $scheduleSettings = [];
    public $selectedCopyScheduleDays = [
        'saturday' => false,
        'sunday' => false,
        'monday' => false,
        'tuesday' => false,
        'wednesday' => false,
        'thursday' => false,
        'friday' => false,
    ];
    public $selectAllCopyScheduleModal = false;
    protected $listeners = [
        'openHolidayModal' => 'handleOpenHolidayModal',
        'openTransferModal' => 'handleOpenTransferModal',
        'refresh-work-hours' => '$refresh',
        'refresh-timepicker' => '$refresh',
        'close-calculator-modal' => 'closeCalculatorModal',
        'close-emergency-modal' => 'closeEmergencyModal',
        'close-schedule-modal' => 'closeScheduleModal',
        'updateSelectedDate' => 'updateSelectedDate',
        'refreshWorkhours' => '$refresh',
        'set-calculator-values' => 'setCalculatorValues',
        'initialize-calculator' => 'initializeCalculator',
        'confirmDeleteSlot' => 'confirmDeleteSlot',
        'confirm-add-slot' => 'openAddSlotModal', // تغییر به باز کردن مودال
    ];
    public function mount()
    {
        $this->calendarYear = is_numeric($this->calendarYear) ? (int) $this->calendarYear : (int) Jalalian::now()->getYear();
        $this->calendarMonth = is_numeric($this->calendarMonth) ? (int) $this->calendarMonth : (int) Jalalian::now()->getMonth();
        $this->selectedClinicId = $this->getSelectedMedicalCenterId();

        $this->loadCalendarData();
        $this->selectedScheduleDays = [
            'saturday' => false,
            'sunday' => false,
            'monday' => false,
            'tuesday' => false,
            'wednesday' => false,
            'thursday' => false,
            'friday' => false,
        ];
    }
    public function selectDate($date)
    {
        $this->isLoading = true;
        $this->dispatch('toggle-loading', ['isLoading' => true]); // ارسال لودینگ
        $this->selectedDate = $date;
        $this->showModal = true;
        $this->workSchedule = $this->getWorkScheduleForDate($date);
        $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
        $this->isLoading = false;
        $this->dispatch('toggle-loading', ['isLoading' => false]);
        $this->dispatch('open-modal', ['name' => 'holiday-modal']);
    }
    public function loadCalendarData()
    {
        $this->holidaysData = [
            'status' => true,
            'holidays' => $this->getHolidays(),
        ];
        $this->appointmentsData = $this->getAppointmentsInMonth($this->calendarYear, $this->calendarMonth);
    }
    public function setCalendarDate($year, $month)
    {
        $this->calendarYear = is_numeric($year) ? (int) $year : (int) Jalalian::now()->getYear();
        $this->calendarMonth = is_numeric($month) ? (int) $month : (int) Jalalian::now()->getMonth();
        $this->loadCalendarData();
        $this->dispatch('calendarDataUpdated', [
            'holidaysData' => $this->holidaysData,
            'appointmentsData' => $this->appointmentsData,
            'calendarYear' => $this->calendarYear,
            'calendarMonth' => $this->calendarMonth,
        ]);
    }
    public function setSelectedClinicId($clinicId)
    {
        $this->selectedClinicId = $clinicId;
        session(['selectedClinicId' => $clinicId]);
        $this->loadCalendarData();
        $this->dispatch('calendarDataUpdated', [
            'holidaysData' => $this->holidaysData,
            'appointmentsData' => $this->appointmentsData,
            'calendarYear' => $this->calendarYear,
            'calendarMonth' => $this->calendarMonth,
        ]);
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
    public function getHolidays()
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)->where('status', 'active');
            if ($this->selectedClinicId === 'default') {
                $holidaysQuery->whereNull('medical_center_id');
            } elseif ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
                $holidaysQuery->where('medical_center_id', $this->selectedClinicId);
            }
            $holidays = $holidaysQuery->get()->pluck('holiday_dates')->map(function ($holiday) {
                $dates = is_string($holiday) ? json_decode($holiday, true) : $holiday;
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return [];
                }
                return is_array($dates) ? $dates : [];
            })->flatten()->filter()->map(function ($date) {
                try {
                    if (preg_match('/^14\d{2}[-\/]\d{2}[-\/]\d{2}$/', $date)) {
                        return Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
                    }
                    return Carbon::parse($date)->format('Y-m-d');
                } catch (\Exception $e) {
                    return null;
                }
            })->filter()->unique()->values()->toArray();
            return $holidays;
        } catch (\Exception $e) {
            return [];
        }
    }
    public function getAppointmentsInMonth($year, $month)
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            if (!is_numeric($year) || !is_numeric($month)) {
                $year = (int) Jalalian::now()->getYear();
                $month = (int) Jalalian::now()->getMonth();
            }
            $jalaliDateString = sprintf("%d/%02d/01", $year, $month);
            $jalaliDate = Jalalian::fromFormat('Y/m/d', $jalaliDateString);
            $startDate = $jalaliDate->toCarbon()->startOfDay();
            $endDate = Jalalian::fromCarbon($startDate)->addMonths(1)->subDays(1)->toCarbon()->endOfDay();
            $appointments = \App\Models\Appointment::where('doctor_id', $doctorId)
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->where('status', 'scheduled')
                ->whereNull('deleted_at')
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                ->select('appointment_date')
                ->groupBy('appointment_date')
                ->get()
                ->map(function ($appointment) {
                    $date = Carbon::parse($appointment->appointment_date)->format('Y-m-d');
                    $count = \App\Models\Appointment::where('doctor_id', $this->getAuthenticatedDoctor()->id)
                        ->where('appointment_date', $appointment->appointment_date)
                        ->where('status', 'scheduled')
                        ->whereNull('deleted_at')
                        ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                        ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                        ->count();
                    return [
                        'date' => $date,
                        'count' => $count,
                    ];
                })
                ->filter(function ($appointment) {
                    return $appointment['count'] > 0;
                })
                ->values()
                ->toArray();
            return [
                'status' => true,
                'data' => $appointments,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'data' => [],
            ];
        }
    }
    public function handleOpenHolidayModal($modalId, $gregorianDate)
    {
        if ($modalId === 'holiday-modal' && $gregorianDate) {
            $this->isLoading = true;
            $this->selectedDate = $gregorianDate;
            $this->showModal = true;
            $this->holidaysData = [
                'status' => true,
                'holidays' => $this->getHolidays(),
            ];
            $this->workSchedule = $this->getWorkScheduleForDate($gregorianDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->isLoading = false;
            $this->dispatch('toggle-loading', ['isLoading' => false]); // اضافه کردن رویداد
            $this->dispatch('updateSelectedDate', $gregorianDate, $this->workSchedule);
            $this->dispatch('open-modal', id: 'holiday-modal');
        } else {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا: تاریخ یا شناسه مودال نامعتبر است.');
        }
    }
    public function handleOpenTransferModal($modalId, $gregorianDate)
    {
        if ($modalId === 'transfer-modal' && $gregorianDate) {
            $this->selectedDate = $gregorianDate;
            $this->dispatch('open-modal', id: 'transfer-modal');
        }
    }
    public function addHoliday()
    {
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;
        try {
            if (empty($this->selectedDate)) {
                $this->dispatch('show-toastr', type: 'error', message: 'هیچ تاریخی انتخاب نشده است.');
                return;
            }
            $selectedDate = Carbon::parse($this->selectedDate);
            if ($selectedDate->isPast()) {
                $this->dispatch('show-toastr', type: 'warning', message: 'نمی‌توانید روزهای گذشته را تعطیل کنید.');
                return;
            }
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $hasAppointments = isset($this->appointmentsData['data']) && collect($this->appointmentsData['data'])->contains('date', $this->selectedDate);
            if ($hasAppointments) {
                $this->dispatch('openTransferModal', [
                    'modalId' => 'transfer-modal',
                    'gregorianDate' => $this->selectedDate,
                ]);
                return;
            }
            $holiday = DoctorHoliday::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                    'status' => 'active',
                ],
                [
                    'holiday_dates' => json_encode([]),
                ]
            );
            $holidayDates = is_string($holiday->holiday_dates)
                ? json_decode($holiday->holiday_dates, true) ?? []
                : $holiday->holiday_dates ?? [];
            if (!in_array($this->selectedDate, $holidayDates)) {
                $holidayDates[] = $this->selectedDate;
                $holiday->holiday_dates = json_encode($holidayDates);
                $holiday->save();
                SpecialDailySchedule::where('doctor_id', $doctorId)
                    ->where('date', $this->selectedDate)
                    ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                    ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                    ->delete();
                $this->holidaysData = [
                    'status' => true,
                    'holidays' => $this->getHolidays(),
                ];
                $this->dispatch('holidayUpdated', date: $this->selectedDate, isHoliday: true);
                $this->dispatch('show-toastr', type: 'success', message: 'این تاریخ تعطیل شد.');
            } else {
                $this->dispatch('show-toastr', type: 'warning', message: 'این تاریخ قبلاً تعطیل است.');
            }
            $this->showModal = false;
            $this->selectedDate = null;
            $this->dispatch('close-modal', id: 'holiday-modal');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در افزودن تعطیلی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function removeHoliday()
    {
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;
        try {
            if (empty($this->selectedDate)) {
                $this->dispatch('show-toastr', type: 'error', message: 'هیچ تاریخی انتخاب نشده است.');
                return;
            }
            $selectedDate = Carbon::parse($this->selectedDate);
            if ($selectedDate->isPast()) {
                $this->dispatch('show-toastr', type: 'warning', message: 'نمی‌توانید روزهای گذشته را از تعطیلی خارج کنید.');
                return;
            }
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $holiday = DoctorHoliday::where('doctor_id', $doctorId)
                ->where('status', 'active')
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                ->first();
            if ($holiday) {
                $holidayDates = is_string($holiday->holiday_dates)
                    ? json_decode($holiday->holiday_dates, true) ?? []
                    : $holiday->holiday_dates ?? [];
                if (in_array($this->selectedDate, $holidayDates)) {
                    $holidayDates = array_filter($holidayDates, fn ($date) => $date !== $this->selectedDate);
                    if (empty($holidayDates)) {
                        $holiday->delete();
                    } else {
                        $holiday->holiday_dates = json_encode(array_values($holidayDates));
                        $holiday->save();
                    }
                    $this->holidaysData = [
                        'status' => true,
                        'holidays' => $this->getHolidays(),
                    ];
                    $this->dispatch('holidayUpdated', date: $this->selectedDate, isHoliday: false);
                    $this->dispatch('show-toastr', type: 'success', message: 'این تاریخ از حالت تعطیلی خارج شد.');
                } else {
                    $this->dispatch('show-toastr', type: 'warning', message: 'این تاریخ تعطیل نیست.');
                }
            } else {
                $this->dispatch('show-toastr', type: 'warning', message: 'هیچ تعطیلی برای این تاریخ ثبت نشده است.');
            }
            $this->showModal = false;
            $this->selectedDate = null;
            $this->dispatch('close-modal', id: 'holiday-modal');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در حذف تعطیلی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDate = null;
        $this->workSchedule = ['status' => false, 'data' => []];
        $this->dispatch('close-modal', id: 'holiday-modal');
    }
    public function getWorkScheduleForDate($date)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        // ابتدا بررسی SpecialDailySchedule
        $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
            ->first();
        if ($specialSchedule && !empty($specialSchedule->work_hours)) {
            $workHours = json_decode($specialSchedule->work_hours, true);
            $appointmentSettings = json_decode($specialSchedule->appointment_settings, true) ?? [];
            $emergencyTimes = json_decode($specialSchedule->emergency_times, true) ?? [[]];
            // تنظیم متغیر برای نشان دادن منبع داده
            $this->isFromSpecialDailySchedule = true;
            return [
                'status' => true,
                'data' => [
                    'day' => strtolower(Carbon::parse($date)->englishDayOfWeek),
                    'work_hours' => $workHours,
                    'appointment_settings' => $appointmentSettings,
                    'emergency_times' => $emergencyTimes,
                ],
            ];
        }
        // اگر SpecialDailySchedule خالی بود، از DoctorWorkSchedule بخوان
        $dayOfWeek = strtolower(Carbon::parse($date)->englishDayOfWeek);
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayOfWeek)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
            ->first();
        // تنظیم متغیر برای نشان دادن منبع داده
        $this->isFromSpecialDailySchedule = false;
        if ($workSchedule && !empty($workSchedule->work_hours)) {
            $workHours = json_decode($workSchedule->work_hours, true);
            $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? [];
            $emergencyTimes = json_decode($workSchedule->emergency_times, true) ?? [];
            // نرمال‌سازی emergency_times برای DoctorWorkSchedule
            if (!is_array($emergencyTimes)) {
                $emergencyTimes = [[]];
            } else {
                // اگر emergency_times یک آرایه تخت است، آن را به [[]] تبدیل می‌کنیم
                if (!empty($emergencyTimes) && !is_array($emergencyTimes[0])) {
                    $emergencyTimes = [$emergencyTimes];
                }
                $emergencyTimes = array_map(function ($times) {
                    return is_array($times) ? $times : [];
                }, $emergencyTimes);
            }
            return [
                'status' => true,
                'data' => [
                    'day' => $dayOfWeek,
                    'work_hours' => $workHours,
                    'appointment_settings' => $appointmentSettings,
                    'emergency_times' => $emergencyTimes,
                ],
            ];
        }
        // اگر هیچ داده‌ای وجود نداشت
        return [
            'status' => false,
            'data' => [
                'day' => $dayOfWeek,
                'work_hours' => [[
                    'start' => '',
                    'end' => '',
                    'max_appointments' => '',
                ]],
                'appointment_settings' => [],
                'emergency_times' => [[]],
            ],
            'message' => 'تنظیماتی تعریف نشده است.',
        ];
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
                // بررسی منبع داده‌ها برای تنظیم isFromSpecialDailySchedule
                $specialSchedule = SpecialDailySchedule::where('doctor_id', $this->getAuthenticatedDoctor()->id)
                    ->where('date', $this->selectedDate)
                    ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                    ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                    ->first();
                $this->isFromSpecialDailySchedule = $specialSchedule && !empty($specialSchedule->work_hours);
            } else {
                $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            }
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->emergencyTimes = $this->getEmergencyTimes();
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در انتخاب تاریخ: ' . $e->getMessage());
        }
    }
    public function loadWorkSchedule()
    {
        $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
        $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
        $this->emergencyTimes = $this->getEmergencyTimes();
    }
    public function getEmergencyTimes()
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            // دریافت همه زمان‌های اورژانسی از workSchedule
            $allEmergencyTimes = $this->workSchedule['data']['emergency_times'] ?? [[]];
            // اگر از DoctorWorkSchedule است، emergency_times یک آرایه تخت درون یک آرایه است
            $emergencyTimes = $this->isFromSpecialDailySchedule
                ? ($allEmergencyTimes[$this->emergencyModalIndex] ?? [])
                : (isset($allEmergencyTimes[0]) && is_array($allEmergencyTimes[0]) ? $allEmergencyTimes[0] : []);
            // تولید زمان‌های ممکن
            $possibleTimes = [];
            if ($this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']) && isset($this->emergencyModalIndex)) {
                $slot = $this->workSchedule['data']['work_hours'][$this->emergencyModalIndex] ?? null;
                $appointmentSettings = $this->workSchedule['data']['appointment_settings'][$this->emergencyModalIndex] ?? null;
                if ($slot && $appointmentSettings && !empty($slot['start']) && !empty($slot['end']) && isset($slot['max_appointments']) && $slot['max_appointments'] > 0) {
                    // اعتبارسنجی فرمت زمان
                    try {
                        $start = Carbon::createFromFormat('H:i', $slot['start']);
                        $end = Carbon::createFromFormat('H:i', $slot['end']);
                    } catch (\Exception $e) {
                        return [
                            'possible' => [],
                            'selected' => $emergencyTimes,
                        ];
                    }
                    if (!$start || !$end || $end->lessThanOrEqualTo($start)) {
                        return [
                            'possible' => [],
                            'selected' => $emergencyTimes,
                        ];
                    }
                    $maxAppointments = (int) $slot['max_appointments'];
                    $totalMinutes = abs($end->diffInMinutes($start));
                    $slotDuration = $maxAppointments > 0 ? floor($totalMinutes / $maxAppointments) : 0;
                    if ($slotDuration <= 0) {
                        return [
                            'possible' => [],
                            'selected' => $emergencyTimes,
                        ];
                    }
                    $possibleTimes = [];
                    $current = $start->copy();
                    while ($current->lessThan($end) && count($possibleTimes) < $maxAppointments) {
                        $possibleTimes[] = $current->format('H:i');
                        $current->addMinutes($slotDuration);
                    }
                } else {
                    return [
                        'possible' => [],
                        'selected' => $emergencyTimes,
                    ];
                }
            } else {
                return [
                    'possible' => [],
                    'selected' => $emergencyTimes,
                ];
            }
            // تنظیم زمان‌های انتخاب‌شده برای UI
            $selectedEmergencyTimes = [];
            // فیلتر کردن زمان‌های اورژانسی برای بازه فعلی
            foreach ($emergencyTimes as $time) {
                try {
                    $timeCarbon = Carbon::createFromFormat('H:i', $time);
                    // بررسی اینکه زمان در بازه فعلی قرار دارد
                    if ($timeCarbon->greaterThanOrEqualTo($start) && $timeCarbon->lessThan($end)) {
                        $selectedEmergencyTimes[$time] = true;
                    }
                } catch (\Exception $e) {
                }
            }
            // تنظیم selectedEmergencyTimes برای نمایش در UI
            $this->selectedEmergencyTimes = $selectedEmergencyTimes;
            return [
                'possible' => $possibleTimes,
                'selected' => array_keys($selectedEmergencyTimes),
            ];
        } catch (\Exception $e) {
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
            $doctorId = $this->getAuthenticatedDoctor()->id;
            // بررسی منبع داده‌ها
            $specialSchedule = SpecialDailySchedule::where([
                'doctor_id' => $doctorId,
                'date' => $this->selectedDate,
                'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
            ])->first();
            $workHours = $specialSchedule && $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $isFromDoctorWorkSchedule = empty($workHours) && $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            if ($isFromDoctorWorkSchedule) {
                // اگر داده‌ها از DoctorWorkSchedule لود شده باشند، مودال add-slot-modal را باز کن
                $this->showAddSlotModal = true;
                $this->savePreviousRows = true;
                $this->dispatch('open-modal', id: 'add-slot-modal');
            } else {
                // اگر داده‌ها از SpecialDailySchedule لود شده باشند، ردیف خالی اضافه کن
                // بررسی تکمیل بودن ردیف قبلی
                if (!empty($workHours)) {
                    $lastIndex = count($workHours) - 1;
                    $lastSlot = $workHours[$lastIndex];
                    if (
                        empty($lastSlot['start']) ||
                        empty($lastSlot['end']) ||
                        empty($lastSlot['max_appointments']) ||
                        !is_numeric($lastSlot['max_appointments']) ||
                        (int)$lastSlot['max_appointments'] <= 0
                    ) {
                        $this->dispatch('show-toastr', type: 'error', message: 'ابتدا ردیف قبلی را تکمیل کنید.');
                        return;
                    }
                }
                // ایجاد یا به‌روزرسانی SpecialDailySchedule
                $specialSchedule = SpecialDailySchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctorId,
                        'date' => $this->selectedDate,
                        'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                    ],
                    [
                        'work_hours' => json_encode([]),
                        'appointment_settings' => json_encode([]),
                        'emergency_times' => json_encode([[]]),
                    ]
                );
                $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
                $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
                $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [[]];
                // اضافه کردن ردیف خالی
                $newIndex = count($workHours);
                $workHours[$newIndex] = [
                    'start' => '',
                    'end' => '',
                    'max_appointments' => '',
                ];
                $appointmentSettings[$newIndex] = [
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                    'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'work_hour_key' => $newIndex,
                ];
                $emergencyTimes[$newIndex] = [];
                // ذخیره داده‌ها
                $specialSchedule->work_hours = json_encode($workHours);
                $specialSchedule->appointment_settings = json_encode($appointmentSettings);
                $specialSchedule->emergency_times = json_encode($emergencyTimes);
                $specialSchedule->save();
                // به‌روزرسانی workSchedule برای نمایش ردیف جدید در UI
                $this->workSchedule = [
                    'status' => true,
                    'data' => [
                        'day' => strtolower(Carbon::parse($this->selectedDate)->englishDayOfWeek),
                        'work_hours' => $workHours,
                        'appointment_settings' => $appointmentSettings,
                        'emergency_times' => $emergencyTimes,
                    ],
                ];
                $this->hasWorkHoursMessage = !empty($workHours);
                $this->dispatch('show-toastr', type: 'success', message: 'ردیف جدید اضافه شد.');
                $this->dispatch('refresh-timepicker');
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در افزودن بازه زمانی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    public function confirmAddSlot($savePrevious = true)
    {
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;
        try {
            $this->savePreviousRows = $savePrevious;
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([[]]),
                ]
            );
            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [[]];
            if (!$this->savePreviousRows) {
                $workHours = [];
                $appointmentSettings = [];
                $emergencyTimes = [[]];
            } else {
                $validWorkHours = [];
                $validAppointmentSettings = [];
                $validEmergencyTimes = [];
                foreach ($this->workSchedule['data']['work_hours'] as $index => $slot) {
                    if (!empty($slot['start']) && !empty($slot['end']) && !empty($slot['max_appointments']) && $slot['max_appointments'] > 0) {
                        if ($this->hasTimeOverlap($slot['start'], $slot['end'], array_diff_key($validWorkHours, [$index => $slot]))) {
                            $this->dispatch('show-toastr', type: 'error', message: 'تداخل زمانی در ساعات کاری وجود دارد.');
                            return;
                        }
                        $validWorkHours[$index] = $slot;
                        $validAppointmentSettings[$index] = $this->workSchedule['data']['appointment_settings'][$index] ?? [
                            'max_appointments' => $slot['max_appointments'],
                            'appointment_duration' => 0,
                            'start_time' => '00:00',
                            'end_time' => '23:59',
                            'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                            'work_hour_key' => $index,
                        ];
                        // اطمینان از اینکه emergency_times فقط آرایه‌های معتبر را شامل می‌شود
                        $emergencyTime = $this->workSchedule['data']['emergency_times'][$index] ?? [];
                        if (!is_array($emergencyTime)) {
                            $emergencyTime = [];
                        }
                        $validEmergencyTimes[$index] = $emergencyTime;
                    }
                }
                $workHours = $validWorkHours;
                $appointmentSettings = $validAppointmentSettings;
                $emergencyTimes = $validEmergencyTimes;
            }
            $newIndex = count($workHours);
            $workHours[$newIndex] = [
                'start' => '',
                'end' => '',
                'max_appointments' => '',
            ];
            $appointmentSettings[$newIndex] = [
                'max_appointments' => '',
                'appointment_duration' => '',
                'start_time' => '00:00',
                'end_time' => '23:59',
                'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'work_hour_key' => $newIndex,
            ];
            $emergencyTimes[$newIndex] = []; // همیشه خالی برای ردیف جدید
            // اطمینان از اینکه emergency_times یک آرایه از آرایه‌ها است
            $emergencyTimes = array_values(array_map(function ($times) {
                return is_array($times) ? $times : [];
            }, $emergencyTimes));
            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($this->normalizeAppointmentSettingsFormat($appointmentSettings, $workHours));
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();
            $this->workSchedule = [
                'status' => true,
                'data' => [
                    'day' => strtolower(Carbon::parse($this->selectedDate)->englishDayOfWeek),
                    'work_hours' => $workHours,
                    'appointment_settings' => $this->normalizeAppointmentSettingsFormat($appointmentSettings, $workHours),
                    'emergency_times' => $emergencyTimes,
                ],
            ];
            $this->hasWorkHoursMessage = !empty($workHours);
            $this->dispatch('show-toastr', type: 'success', message: $this->savePreviousRows ? 'ردیف جدید اضافه شد.' : 'ردیف‌های قبلی حذف شدند و یک ردیف خالی اضافه شد.');
            $this->dispatch('refresh-timepicker');
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در افزودن بازه زمانی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    public function closeAddSlotModal()
    {
        $this->showAddSlotModal = false;
        $this->savePreviousRows = true;
        $this->dispatch('close-modal', id: 'add-slot-modal');
    }
    private function addNewSlot($specialSchedule, $doctorId, $workHours = null, $appointmentSettings = null, $emergencyTimes = null)
    {
        $workHours = $workHours ?? ($specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : []);
        $appointmentSettings = $appointmentSettings ?? ($specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : []);
        $emergencyTimes = $emergencyTimes ?? ($specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : []);
        // اعتبارسنجی تداخل زمانی برای ردیف جدید
        $newIndex = count($workHours);
        foreach ($workHours as $slot) {
            if (!empty($slot['start']) && !empty($slot['end'])) {
                $existingStart = Carbon::createFromFormat('H:i', $slot['start']);
                $existingEnd = Carbon::createFromFormat('H:i', $slot['end']);
                // برای جلوگیری از تداخل، ردیف جدید خالی اضافه می‌شه و کاربر باید زمان‌ها رو وارد کنه
            }
        }
        $workHours[$newIndex] = [
            'start' => '',
            'end' => '',
            'max_appointments' => '',
        ];
        $appointmentSettings[$newIndex] = [
            'max_appointments' => '',
            'appointment_duration' => '',
            'start_time' => '00:00',
            'end_time' => '23:59',
            'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'work_hour_key' => $newIndex,
        ];
        $emergencyTimes[$newIndex] = [];
        $specialSchedule->work_hours = json_encode($workHours);
        $specialSchedule->appointment_settings = json_encode($appointmentSettings);
        $specialSchedule->emergency_times = json_encode($emergencyTimes);
        $specialSchedule->save();

        $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
        $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
        $this->dispatch('show-toastr', type: 'success', message: 'بازه زمانی جدید اضافه شد.');
        $this->dispatch('refresh-timepicker');
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
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                ->first();
            $isFromDoctorWorkSchedule = !$specialSchedule;
            if ($isFromDoctorWorkSchedule) {

                $this->workSchedule['data']['work_hours'][$index] = [
                    'start' => '',
                    'end' => '',
                    'max_appointments' => '',
                ];
                $this->dispatch('show-toastr', type: 'success', message: 'بازه زمانی خالی شد. لطفاً مقادیر جدید را وارد کنید.');
                $this->dispatch('refresh-timepicker');
            } else {

                $this->dispatch('confirm-delete-slot', index: $index); // ← فرمت واضح‌تر
            }
        } catch (\Exception $e) {

            $this->dispatch('show-toastr', type: 'error', message: 'خطا در حذف بازه زمانی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function confirmDeleteSlot($index)
    {


        if ($this->isProcessing) {

            return;
        }
        $this->isProcessing = true;

        try {
            if (!isset($index) || $index === null || $index === 'undefined') {
                $this->dispatch('show-toastr', type: 'error', message: 'اندیس بازه زمانی نامعتبر است.');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                ->first();

            if (!$specialSchedule) {
                $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این تاریخ یافت نشد.');
                return;
            }

            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [[]];



            if (!isset($workHours[$index])) {
                $this->dispatch('show-toastr', type: 'error', message: 'بازه زمانی نامعتبر است.');
                return;
            }

            // حذف بازه
            unset($workHours[$index]);
            unset($appointmentSettings[$index]);
            unset($emergencyTimes[$index]);

            // بازسازی اندیس‌ها
            $workHours = array_values($workHours);
            $appointmentSettings = array_values($appointmentSettings);
            $emergencyTimes = array_values(array_map(fn ($times) => is_array($times) ? array_values($times) : [], $emergencyTimes));



            // ذخیره یا حذف
            if (empty($workHours)) {

                $specialSchedule->delete();
            } else {

                $specialSchedule->work_hours = json_encode($workHours);
                $specialSchedule->appointment_settings = json_encode($appointmentSettings);
                $specialSchedule->emergency_times = json_encode($emergencyTimes);
                $specialSchedule->save();
            }

            // به‌روزرسانی workSchedule
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);


            $this->dispatch('show-toastr', type: 'success', message: 'بازه زمانی حذف شد.');
            $this->dispatch('refresh-timepicker');
            $this->dispatch('refresh');
            $this->dispatch('refreshWorkhours');
        } catch (\Exception $e) {

            $this->dispatch('show-toastr', type: 'error', message: 'خطا در حذف بازه زمانی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    public function setCalculatorValues($values)
    {
        if ($this->isProcessing) {
            return;
        }
        try {
            $startTime = $this->calculator['start_time'] ?? null;
            $endTime = $this->calculator['end_time'] ?? null;
            if (empty($startTime) || empty($endTime)) {
                $this->dispatch('show-toastr', type: 'error', message: 'زمان شروع یا پایان مشخص نشده است.');
                return;
            }
            // اعتبارسنجی فرمت زمان
            if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $startTime) ||
                !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $endTime)) {
                $this->dispatch('show-toastr', type: 'error', message: 'فرمت زمان نامعتبر است.');
                return;
            }
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
            // بررسی ترتیب زمانی
            if ($end->lte($start)) {
                $this->dispatch('show-toastr', type: 'error', message: 'زمان پایان باید بعد از زمان شروع باشد.');
                return;
            }
            $totalMinutes = abs($end->diffInMinutes($start));
            if ($totalMinutes <= 0) {
                $this->dispatch('show-toastr', type: 'error', message: 'بازه زمانی نامعتبر است.');
                return;
            }
            $this->calculator['calculation_mode'] = $values['calculation_mode'] ?? $this->calculator['calculation_mode'];
            $mode = $this->calculator['calculation_mode'];
            if ($mode === 'count' && isset($values['appointment_count']) && is_numeric($values['appointment_count']) && $values['appointment_count'] > 0) {
                $this->calculator['appointment_count'] = (int) $values['appointment_count'];
                $this->calculator['time_per_appointment'] = floor($totalMinutes / $this->calculator['appointment_count']);
            } elseif ($mode === 'time' && isset($values['time_per_appointment']) && is_numeric($values['time_per_appointment']) && $values['time_per_appointment'] > 0) {
                $this->calculator['time_per_appointment'] = (int) $values['time_per_appointment'];
                $this->calculator['appointment_count'] = floor($totalMinutes / $this->calculator['time_per_appointment']);
            } else {
                return;
            }
            $this->dispatch('update-calculator-ui', $this->calculator);
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در به‌روزرسانی  ساعت کاری: ' . $e->getMessage());
        }
    }
    public function saveCalculator()
    {
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;
        try {
            $this->ensureSpecialDailyScheduleFromDoctorWorkSchedule();
            $startTime = $this->calculator['start_time'];
            $endTime = $this->calculator['end_time'];
            $appointmentCount = $this->calculator['appointment_count'];
            $timePerAppointment = $this->calculator['time_per_appointment'];
            $index = $this->calculator['index'];
            if (empty($startTime) || empty($endTime) || empty($appointmentCount) || $appointmentCount <= 0) {
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً تمامی فیلدها را پر کنید.');
                return;
            }
            // اعتبارسنجی فرمت زمان
            try {
                $start = Carbon::createFromFormat('H:i', $startTime);
                $end = Carbon::createFromFormat('H:i', $endTime);
                if ($end->lte($start)) {
                    $this->dispatch('show-toastr', type: 'error', message: 'زمان پایان باید بعد از زمان شروع باشد.');
                    return;
                }
            } catch (\Exception $e) {
                $this->dispatch('show-toastr', type: 'error', message: 'فرمت زمان نامعتبر است.');
                return;
            }
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([[]]),
                ]
            );
            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [];
            // بررسی تداخل زمانی
            $newSlot = [
                'start' => $startTime,
                'end' => $endTime,
                'max_appointments' => $appointmentCount,
            ];
            if ($this->hasTimeOverlap($startTime, $endTime, array_diff_key($workHours, [$index => []]))) {
                $this->dispatch('show-toastr', type: 'error', message: 'تداخل زمانی در ساعات کاری وجود دارد.');
                return;
            }
            // به‌روزرسانی work_hours
            $workHours[$index] = $newSlot;
            // به‌روزرسانی appointment_settings با فرمت موردنظر
            $appointmentSettings[$index] = [
                'start_time' => '00:00',
                'end_time' => '23:59',
                'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'work_hour_key' => $index,
            ];
            // اطمینان از وجود emergency_times برای این ردیف
            $emergencyTimes[$index] = $emergencyTimes[$index] ?? [];
            // ذخیره داده‌ها در دیتابیس
            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($this->normalizeAppointmentSettingsFormat($appointmentSettings, $workHours));
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();
            // به‌روزرسانی workSchedule
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->dispatch('show-toastr', type: 'success', message: 'تنظیمات ساعت کاری ذخیره شد.');
            $this->dispatch('close-modal', 'CalculatorModal');
            $this->dispatch('refresh-timepicker');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در ذخیره تنظیمات ساعت کاری: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    private function hasTimeOverlap($newStart, $newEnd, $existingWorkHours)
    {
        try {
            $newStart = Carbon::createFromFormat('H:i', $newStart);
            $newEnd = Carbon::createFromFormat('H:i', $newEnd);
            foreach ($existingWorkHours as $slot) {
                if (!empty($slot['start']) && !empty($slot['end'])) {
                    $existingStart = Carbon::createFromFormat('H:i', $slot['start']);
                    $existingEnd = Carbon::createFromFormat('H:i', $slot['end']);
                    // بررسی تداخل با شرط‌های اصلاح‌شده
                    // تداخل زمانی رخ می‌دهد که:
                    // 1. newStart در بازه existingStart و existingEnd باشد (به جز existingEnd)
                    // 2. newEnd در بازه existingStart و existingEnd باشد (به جز existingStart)
                    // 3. existingStart در بازه newStart و newEnd باشد (به جز newEnd)
                    // 4. existingEnd در بازه newStart و newEnd باشد (به جز newStart)
                    if (
                        ($newStart->greaterThanOrEqualTo($existingStart) && $newStart->lessThan($existingEnd)) ||
                        ($newEnd->greaterThan($existingStart) && $newEnd->lessThanOrEqualTo($existingEnd)) ||
                        ($existingStart->greaterThanOrEqualTo($newStart) && $existingStart->lessThan($newEnd)) ||
                        ($existingEnd->greaterThan($newStart) && $existingEnd->lessThanOrEqualTo($newEnd))
                    ) {
                        return true;
                    }
                }
            }
            return false;
        } catch (\Exception $e) {
            return false; // در صورت خطا، تداخل فرض نمی‌شود
        }
    }
    public function saveEmergencyTimes()
    {
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;
        try {
            $this->ensureSpecialDailyScheduleFromDoctorWorkSchedule();
            $doctorId = $this->getAuthenticatedDoctor()->id;
            // دریافت یا ایجاد ردیف در SpecialDailySchedule
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([[]]),
                ]
            );
            // دریافت داده‌های فعلی
            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [[]];
            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            if (!$this->isFromSpecialDailySchedule && $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours'])) {
                // کپی داده‌ها از DoctorWorkSchedule
                $workHours = $this->workSchedule['data']['work_hours'];
                $appointmentSettings = [];
                $emergencyTimes = array_pad([], count($workHours), []);
                foreach ($workHours as $index => $slot) {
                    $appointmentSettings[$index] = [
                        'start_time' => '00:00',
                        'end_time' => '23:59',
                        'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                        'work_hour_key' => $index,
                        'max_appointments' => $slot['max_appointments'] ?? 0,
                        'appointment_duration' => 0,
                    ];
                    $emergencyTimes[$index] = $this->workSchedule['data']['emergency_times'][$index] ?? [];
                }
            }
            // اعتبارسنجی emergencyModalIndex
            if (!isset($this->emergencyModalIndex)) {
                $this->dispatch('show-toastr', type: 'error', message: 'بازه زمانی انتخاب‌شده نامعتبر است.');
                return;
            }
            // اعتبارسنجی work_hours
            $currentSlot = $workHours[$this->emergencyModalIndex] ?? null;
            if (!$currentSlot || empty($currentSlot['start']) || empty($currentSlot['end'])) {
                $this->dispatch('show-toastr', type: 'error', message: 'بازه کاری نامعتبر است.');
                return;
            }
            // اعتبارسنجی زمان‌های اورژانسی
            $selectedTimes = array_keys(array_filter($this->selectedEmergencyTimes, fn ($value) => $value));
            $validEmergencyTimes = array_values(array_filter($selectedTimes, fn ($time) => in_array($time, $this->emergencyTimes['possible'] ?? [])));
            // بررسی تداخل زمانی
            $slotStart = Carbon::createFromFormat('H:i', $currentSlot['start']);
            $slotEnd = Carbon::createFromFormat('H:i', $currentSlot['end']);
            foreach ($validEmergencyTimes as $time) {
                $emergencyTime = Carbon::createFromFormat('H:i', $time);
                if ($emergencyTime->lessThan($slotStart) || $emergencyTime->greaterThanOrEqualTo($slotEnd)) {
                    $this->dispatch('show-toastr', type: 'error', message: "زمان اورژانسی $time خارج از بازه کاری است.");
                    return;
                }
            }
            // بررسی تداخل با سایر بازه‌ها
            foreach (array_diff_key($workHours, [$this->emergencyModalIndex => []]) as $otherSlot) {
                if (!empty($otherSlot['start']) && !empty($otherSlot['end'])) {
                    $otherStart = Carbon::createFromFormat('H:i', $otherSlot['start']);
                    $otherEnd = Carbon::createFromFormat('H:i', $otherSlot['end']);
                    foreach ($validEmergencyTimes as $time) {
                        $emergencyTime = Carbon::createFromFormat('H:i', $time);
                        if ($emergencyTime->greaterThanOrEqualTo($otherStart) && $emergencyTime->lessThan($otherEnd)) {
                            $this->dispatch('show-toastr', type: 'error', message: "زمان اورژانسی $time با بازه کاری دیگر تداخل دارد.");
                            return;
                        }
                    }
                }
            }
            // تنظیم appointment_settings پیش‌فرض اگر وجود نداشته باشد
            if (!isset($appointmentSettings[$this->emergencyModalIndex])) {
                $appointmentSettings[$this->emergencyModalIndex] = [
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                    'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'work_hour_key' => $this->emergencyModalIndex,
                    'max_appointments' => $currentSlot['max_appointments'] ?? 0,
                    'appointment_duration' => 0,
                ];
            }
            // به‌روزرسانی emergency_times
            $emergencyTimes[$this->emergencyModalIndex] = $validEmergencyTimes;
            $emergencyTimes = array_values(array_map(fn ($times) => is_array($times) ? array_values($times) : [], $emergencyTimes));
            // ذخیره داده‌ها
            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($this->normalizeAppointmentSettingsFormat($appointmentSettings, $workHours));
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();
            // به‌روزرسانی workSchedule
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->dispatch('show-toastr', type: 'success', message: 'زمان‌های اورژانسی ذخیره شد.');
            $this->dispatch('close-emergency-modal');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در ذخیره زمان‌های اورژانسی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    public function updatedSelectAllScheduleModal($value)
    {
        // وقتی selectAllScheduleModal تغییر می‌کند، تمام روزها را تنظیم کن
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            $this->selectedScheduleDays[$day] = $value;
        }
    }
    public function updatedSelectedScheduleDays($value, $day)
    {
        // بررسی اینکه آیا همه روزها انتخاب شده‌اند
        $allSelected = count(array_filter($this->selectedScheduleDays)) === 7;
        $this->selectAllScheduleModal = $allSelected;
    }
    public function saveSchedule()
    {
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;
        try {
            $this->ensureSpecialDailyScheduleFromDoctorWorkSchedule();
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([[]]),
                ]
            );
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [[]];
            $index = $this->scheduleModalIndex;
            $days = array_keys(array_filter($this->selectedScheduleDays));
            $startTime = $this->workSchedule['data']['work_hours'][$index]['start'] ?? null;
            $endTime = $this->workSchedule['data']['work_hours'][$index]['end'] ?? null;
            if (empty($startTime) || empty($endTime) || empty($days)) {
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً زمان شروع، پایان و حداقل یک روز را وارد کنید.');
                return;
            }
            // حذف تنظیمات قبلی این work_hour_key برای روزهای انتخاب‌شده
            $updatedSettings = [];
            foreach ($appointmentSettings as $setting) {
                if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$index) {
                    // ساختار جدید
                    if (isset($setting['day']) && !in_array($setting['day'], $days)) {
                        $updatedSettings[] = $setting;
                    }
                    // ساختار قدیمی
                    if (isset($setting['days']) && is_array($setting['days'])) {
                        $remainingDays = array_diff($setting['days'], $days);
                        if (!empty($remainingDays)) {
                            $updatedSettings[] = [
                                'days' => array_values($remainingDays),
                                'start_time' => $setting['start_time'],
                                'end_time' => $setting['end_time'],
                                'work_hour_key' => $setting['work_hour_key'],
                            ];
                        }
                    }
                } else {
                    $updatedSettings[] = $setting;
                }
            }
            // افزودن تنظیمات جدید برای روزهای انتخاب‌شده
            foreach ($days as $day) {
                $updatedSettings[] = [
                    'day' => $day,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'work_hour_key' => (int)$index,
                ];
            }
            $specialSchedule->appointment_settings = json_encode($this->normalizeAppointmentSettingsFormat($updatedSettings, $workHours));
            $specialSchedule->save();
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->dispatch('refresh-schedule-settings');
            $this->dispatch('show-toastr', type: 'success', message: 'تنظیم زمان‌بندی با موفقیت ذخیره شد.');
            $this->dispatch('close-schedule-modal');
        } catch (\Exception $e) {
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
        if (empty($this->selectedDate)) {
            $this->dispatch('show-toastr', type: 'error', message: 'تاریخ انتخاب نشده است.');
            return;
        }
        $this->isProcessing = true;
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = \App\Models\SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                ->first();
            if (!$specialSchedule) {
                $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این تاریخ یافت نشد.');
                return;
            }
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            // حذف فقط تنظیم با day و work_hour_key
            $updatedSettings = array_filter(
                $appointmentSettings,
                fn ($setting) => !(isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$index && isset($setting['day']) && $setting['day'] === $day)
            );
            $specialSchedule->appointment_settings = json_encode(array_values($updatedSettings));
            $specialSchedule->save();
            // مقداردهی مجدد state و UI
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->selectedScheduleDays = [
                'saturday' => false,
                'sunday' => false,
                'monday' => false,
                'tuesday' => false,
                'wednesday' => false,
                'thursday' => false,
                'friday' => false,
            ];
            $this->scheduleSettings = [];
            $settings = $this->workSchedule['data']['appointment_settings'] ?? [];
            foreach ($settings as $setting) {
                if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$index && isset($setting['day'])) {
                    $this->selectedScheduleDays[$setting['day']] = true;
                    $this->scheduleSettings[$setting['day']] = [
                        'start_time' => $setting['start_time'],
                        'end_time' => $setting['end_time'],
                    ];
                }
            }
            $this->selectAllScheduleModal = count(array_filter($this->selectedScheduleDays)) === 7;
            $this->dispatch('refresh-schedule-settings');
            $persianDay = [
                'saturday' => 'شنبه',
                'sunday' => 'یکشنبه',
                'monday' => 'دوشنبه',
                'tuesday' => 'سه‌شنبه',
                'wednesday' => 'چهارشنبه',
                'thursday' => 'پنج‌شنبه',
                'friday' => 'جمعه',
            ][$day] ?? $day;
            $this->dispatch('show-toastr', type: 'success', message: 'تنظیمات برای روز ' . $persianDay . ' با موفقیت حذف شد');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در حذف تنظیمات زمان‌بندی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    protected function openScheduleModalForEdit($day, $index, $setting)
    {
        try {
            // تنظیم متغیرهای مودال
            $this->scheduleModalDay = $day;
            $this->scheduleModalIndex = $setting['work_hour_key'];
            // تنظیم زمان‌های شروع و پایان
            $this->workSchedule['data']['work_hours'][$this->scheduleModalIndex]['start'] = $setting['start_time'];
            $this->workSchedule['data']['work_hours'][$this->scheduleModalIndex]['end'] = $setting['end_time'];
            $this->workSchedule['data']['work_hours'][$this->scheduleModalIndex]['max_appointments'] = $setting['max_appointments'] ?? 0;
            // تنظیم روزهای انتخاب‌شده
            $this->selectedScheduleDays = array_fill_keys(
                ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                false
            );
            foreach ($setting['days'] as $day) {
                $this->selectedScheduleDays[$day] = true;
            }
            $this->selectAllScheduleModal = count(array_filter($this->selectedScheduleDays)) === 7;
            // ذخیره اندیس تنظیم برای ویرایش
            $this->editingSettingIndex = $index;
            // باز کردن مودال
            $this->dispatch('open-modal', id: 'scheduleModal');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در باز کردن مودال ویرایش: ' . $e->getMessage());
        }
    }
    public function setCalculationMode($mode)
    {
        if ($this->isProcessing) {
            return;
        }
        $this->calculator['calculation_mode'] = $mode;
    }
    public function openCalculatorModal($day, $index)
    {
        try {
            // بررسی وجود داده‌های work_hours
            if (!isset($this->workSchedule['data']['work_hours'][$index])) {
                $this->dispatch('show-toastr', type: 'error', message: 'داده‌های برنامه کاری برای این بازه زمانی یافت نشد.');
                return;
            }
            $startTime = $this->workSchedule['data']['work_hours'][$index]['start'] ?? null;
            $endTime = $this->workSchedule['data']['work_hours'][$index]['end'] ?? null;
            // اعتبارسنجی زمان‌های شروع و پایان
            if (empty($startTime) || empty($endTime)) {
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً ابتدا زمان شروع و پایان را وارد کنید.');
                return;
            }
            // بررسی فرمت زمان
            try {
                $start = Carbon::createFromFormat('H:i', $startTime);
                $end = Carbon::createFromFormat('H:i', $endTime);
            } catch (\Exception $e) {
                $this->dispatch('show-toastr', type: 'error', message: 'فرمت زمان نامعتبر است.');
                return;
            }
            if (!$start || !$end) {
                $this->dispatch('show-toastr', type: 'error', message: 'زمان شروع یا پایان نامعتبر است.');
                return;
            }
            // محاسبه تفاوت زمانی
            $totalMinutes = abs($end->diffInMinutes($start));
            if ($end->lessThanOrEqualTo($start)) {
                $this->dispatch('show-toastr', type: 'error', message: 'زمان پایان باید بعد از زمان شروع باشد.');
                return;
            }
            // تنظیم مقادیر ساعت کاری
            $this->calculator = [
                'day' => $day,
                'index' => $index,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'appointment_count' => $this->workSchedule['data']['work_hours'][$index]['max_appointments'] ?? null,
                'time_per_appointment' => $this->workSchedule['data']['appointment_settings'][$index]['appointment_duration'] ?? 0,
                'calculation_mode' => isset($this->workSchedule['data']['appointment_settings'][$index]['appointment_duration']) && $this->workSchedule['data']['appointment_settings'][$index]['appointment_duration'] > 0 ? 'time' : 'count',
            ];
            // باز کردن مودال با شناسه معتبر
            $this->dispatch('open-modal', id: 'CalculatorModal');
            $this->dispatch('initialize-calculator', [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'index' => $index,
                'day' => $day,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در باز کردن مودال ساعت کاری: ' . $e->getMessage());
        }
    }
    public function openEmergencyModal($day, $index)
    {
        try {
            if (!isset($this->workSchedule['data']['work_hours'][$index])) {
                $this->dispatch('show-toastr', type: 'error', message: 'بازه زمانی انتخاب‌شده نامعتبر است.');
                return;
            }
            $this->isEmergencyModalOpen = true;
            $this->emergencyModalDay = $day;
            $this->emergencyModalIndex = $index; // تنظیم صریح emergencyModalIndex
            $emergencyData = $this->getEmergencyTimes();
            $this->emergencyTimes = [
                'possible' => $emergencyData['possible'] ?? [],
                'selected' => $emergencyData['selected'] ?? [],
            ];
            $this->selectedEmergencyTimes = array_fill_keys($emergencyData['selected'] ?? [], true);
            $this->dispatch('open-modal', id: 'emergencyModal');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در باز کردن مودال اورژانسی: ' . $e->getMessage());
        }
    }
    public function openScheduleModal($day, $index)
    {
        try {
            $this->scheduleModalDay = $day;
            $this->scheduleModalIndex = $index;
            $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            $this->selectedScheduleDays = array_fill_keys($daysOfWeek, false);
            $this->scheduleSettings = [];
            // فقط تنظیمات appointment_settings مربوط به work_hour_key و هر روز را لود کن
            $settings = $this->workSchedule['data']['appointment_settings'] ?? [];
            foreach ($settings as $setting) {
                if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$index) {
                    // ساختار جدید (day)
                    if (isset($setting['day'])) {
                        $d = $setting['day'];
                        $this->selectedScheduleDays[$d] = true;
                        $this->scheduleSettings[$d] = [
                            'start_time' => $setting['start_time'],
                            'end_time' => $setting['end_time'],
                        ];
                    }
                    // ساختار قدیمی (days)
                    if (isset($setting['days']) && is_array($setting['days'])) {
                        foreach ($setting['days'] as $d) {
                            $this->selectedScheduleDays[$d] = true;
                            $this->scheduleSettings[$d] = [
                                'start_time' => $setting['start_time'],
                                'end_time' => $setting['end_time'],
                            ];
                        }
                    }
                }
            }
            // اگر هیچ تنظیمی برای روز جاری وجود نداشت
            if (empty($this->scheduleSettings[$day])) {
                $this->selectedScheduleDays[$day] = true;
                $this->scheduleSettings[$day] = [
                    'start_time' => null,
                    'end_time' => null,
                ];
            }
            $this->selectAllScheduleModal = count(array_filter($this->selectedScheduleDays)) === 7;
            $this->dispatch('refresh-schedule-settings');
            $this->dispatch('open-modal', id: 'scheduleModal');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در باز کردن مودال زمان‌بندی: ' . $e->getMessage());
        }
    }
    public function closeScheduleModal()
    {
        $this->scheduleModalDay = null;
        $this->scheduleModalIndex = null;
        $this->selectedScheduleDays = [
            'saturday' => false,
            'sunday' => false,
            'monday' => false,
            'tuesday' => false,
            'wednesday' => false,
            'thursday' => false,
            'friday' => false,
        ];
        $this->selectAllScheduleModal = false;
        $this->editingSettingIndex = null;
        $this->dispatch('close-modal', id: 'scheduleModal');
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
    public function updatedSelectedEmergencyTimes()
    {
    }
    public function updatedWorkScheduleDataWorkHours($value, $nested)
    {
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;
        try {
            $this->ensureSpecialDailyScheduleFromDoctorWorkSchedule();
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
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
            $specialSchedule->appointment_settings = json_encode($this->normalizeAppointmentSettingsFormat($appointmentSettings, $workHours));
            $specialSchedule->save();
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);

            $this->dispatch('show-toastr', type: 'success', message: 'تغییرات ساعت کاری ذخیره شد.');
        } catch (\Exception $e) {
        } finally {
            $this->isProcessing = false;
        }
    }
    public function initializeCalculator($params)
    {
    }
    public function updatedSelectAllCopyScheduleModal($value)
    {
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            if ($day !== ($this->scheduleModalDay ?? null)) {
                $this->selectedCopyScheduleDays[$day] = $value;
            }
        }
    }
    public function copyScheduleSetting($replace = false)
    {
        if ($this->isProcessing) {
            return;
        }
        if (empty($this->selectedDate)) {
            $this->dispatch('show-toastr', type: 'error', message: 'تاریخ انتخاب نشده است.');
            return;
        }
        $this->isProcessing = true;
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = \App\Models\SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('medical_center_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('medical_center_id', $this->selectedClinicId))
                ->first();
            if (!$specialSchedule) {
                $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این تاریخ یافت نشد.');
                return;
            }
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $index = $this->scheduleModalIndex;
            $sourceDay = $this->scheduleModalDay;
            // اضافه شد: تعریف workHours برای نرمال‌سازی
            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            // پیدا کردن منبع فقط از رکوردی که day آن scheduleModalDay است و work_hour_key برابر با index است
            $sourceSetting = null;
            foreach ($appointmentSettings as $setting) {
                if (
                    isset($setting['work_hour_key'], $setting['day']) &&
                    (int)$setting['work_hour_key'] === (int)$index &&
                    $setting['day'] === $sourceDay
                ) {
                    $sourceSetting = $setting;
                    break;
                }
            }
            if (!$sourceSetting) {
                $this->dispatch('show-toastr', type: 'error', message: 'ابتدا یک روز را به عنوان منبع انتخاب کنید.');
                return;
            }
            $targetDays = array_keys(array_filter($this->selectedCopyScheduleDays));
            if (empty($targetDays)) {
                $this->dispatch('show-toastr', type: 'error', message: 'هیچ روزی برای کپی انتخاب نشده است.');
                return;
            }
            $updatedSettings = $appointmentSettings;
            foreach ($targetDays as $day) {
                // حذف قبلی برای همین work_hour_key و day
                $updatedSettings = array_filter(
                    $updatedSettings,
                    fn ($setting) => !(isset($setting['work_hour_key'], $setting['day']) && (int)$setting['work_hour_key'] === (int)$index && $setting['day'] === $day)
                );
                // اضافه کردن جدید
                $updatedSettings[] = [
                    'day' => $day,
                    'start_time' => $sourceSetting['start_time'],
                    'end_time' => $sourceSetting['end_time'],
                    'work_hour_key' => (int)$index,
                ];
            }
            $specialSchedule->appointment_settings = json_encode($this->normalizeAppointmentSettingsFormat(array_values($updatedSettings), $workHours));
            $specialSchedule->save();
            // مقداردهی مجدد state و UI
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->selectedScheduleDays = [
                'saturday' => false,
                'sunday' => false,
                'monday' => false,
                'tuesday' => false,
                'wednesday' => false,
                'thursday' => false,
                'friday' => false,
            ];
            $this->scheduleSettings = [];
            $settings = $this->workSchedule['data']['appointment_settings'] ?? [];
            foreach ($settings as $setting) {
                if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$index && isset($setting['day'])) {
                    $this->selectedScheduleDays[$setting['day']] = true;
                    $this->scheduleSettings[$setting['day']] = [
                        'start_time' => $setting['start_time'],
                        'end_time' => $setting['end_time'],
                    ];
                }
            }
            $this->selectAllScheduleModal = count(array_filter($this->selectedScheduleDays)) === 7;
            $this->dispatch('refresh-schedule-settings');
            $this->dispatch('show-toastr', type: 'success', message: 'تنظیمات با موفقیت کپی شد.');
            $this->dispatch('close-modal', ['name' => 'copy-schedule-modal']);
            $this->selectAllCopyScheduleModal = false;
            $this->selectedCopyScheduleDays = [
                'saturday' => false,
                'sunday' => false,
                'monday' => false,
                'tuesday' => false,
                'wednesday' => false,
                'thursday' => false,
                'friday' => false,
            ];
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در کپی تنظیمات: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }
    public function render()
    {
        return view('livewire.dr.panel.turn.schedule.special-days-appointment');
    }
    /**
     * نرمال‌سازی فرمت appointment_settings به آرایه‌ای از آبجکت‌های دارای day, start_time, end_time, work_hour_key
     */
    private function normalizeAppointmentSettingsFormat($appointmentSettings, $workHours = [])
    {
        $normalized = [];
        // اگر ساختار قدیمی (days) وجود دارد، به جدید تبدیل کن
        foreach ($appointmentSettings as $setting) {
            if (isset($setting['days']) && is_array($setting['days'])) {
                foreach ($setting['days'] as $day) {
                    $normalized[] = [
                        'day' => $day,
                        'start_time' => $setting['start_time'] ?? ($workHours[$setting['work_hour_key']]['start'] ?? '09:00'),
                        'end_time' => $setting['end_time'] ?? ($workHours[$setting['work_hour_key']]['end'] ?? '23:00'),
                        'work_hour_key' => $setting['work_hour_key'],
                    ];
                }
            } elseif (isset($setting['day'])) {
                // اگر ساختار جدید است، همان را اضافه کن
                $normalized[] = [
                    'day' => $setting['day'],
                    'start_time' => $setting['start_time'] ?? ($workHours[$setting['work_hour_key']]['start'] ?? '09:00'),
                    'end_time' => $setting['end_time'] ?? ($workHours[$setting['work_hour_key']]['end'] ?? '23:00'),
                    'work_hour_key' => $setting['work_hour_key'],
                ];
            }
        }
        return $normalized;
    }
    private function ensureSpecialDailyScheduleFromDoctorWorkSchedule()
    {
        if (!$this->isFromSpecialDailySchedule && $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours'])) {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'medical_center_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([[]]),
                ]
            );
            $workHours = $this->workSchedule['data']['work_hours'];
            $appointmentSettings = $this->workSchedule['data']['appointment_settings'] ?? [];
            $emergencyTimes = $this->workSchedule['data']['emergency_times'] ?? [[]];
            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($this->normalizeAppointmentSettingsFormat($appointmentSettings, $workHours));
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();
            $this->isFromSpecialDailySchedule = true;
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
        }
    }
}
