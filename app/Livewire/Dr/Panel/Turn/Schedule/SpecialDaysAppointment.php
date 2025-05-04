<?php

namespace App\Livewire\Dr\Panel\Turn\Schedule;

use Carbon\Carbon;
use App\Models\Doctor;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SpecialDaysAppointment extends Component
{
    public $calendarYear;
    public $calendarMonth;
    public $selectedClinicId = 'default';
    public $holidaysData = ['status' => true, 'holidays' => []];
    public $appointmentsData = ['status' => true, 'data' => []];
    public $selectedDate;
    public $showModal = false;
    public $isProcessing = false;

    protected $listeners = [
       'openHolidayModal' => 'handleOpenHolidayModal',
       'openTransferModal' => 'handleOpenTransferModal',
];

    public function mount()
    {
        $this->calendarYear = is_numeric($this->calendarYear) ? (int) $this->calendarYear : (int) Jalalian::now()->getYear();
        $this->calendarMonth = is_numeric($this->calendarMonth) ? (int) $this->calendarMonth : (int) Jalalian::now()->getMonth();
        $this->selectedClinicId = request()->query('selectedClinicId', session('selectedClinicId', 'default'));
        Log::info("Mounting component", [
            'selectedDate' => $this->selectedDate,
            'calendarYear' => $this->calendarYear,
            'calendarMonth' => $this->calendarMonth,
            'selectedClinicId' => $this->selectedClinicId,
            'holidaysData' => $this->holidaysData,
            'appointmentsData' => $this->appointmentsData,
        ]);
        $this->loadCalendarData();
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->showModal = true;
        Log::info("Selecting date: $date");
        $this->dispatch('openXModal', ['id' => 'holiday-modal']);
    }

    public function loadCalendarData()
    {
        Log::info("Loading calendar data with year: {$this->calendarYear}, month: {$this->calendarMonth}");
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
        Log::info("Setting calendar date to year: {$this->calendarYear}, month: {$this->calendarMonth}");
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
            $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)
                ->where('status', 'active');
            if ($this->selectedClinicId === 'default') {
                $holidaysQuery->whereNull('clinic_id');
            } elseif ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
                $holidaysQuery->where('clinic_id', $this->selectedClinicId);
            }
            $holidays = $holidaysQuery->get()->pluck('holiday_dates')->map(function ($holiday) {
                $dates = is_string($holiday) ? json_decode($holiday, true) : $holiday;
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning("Invalid JSON in holiday_dates: $holiday");
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
                    Log::warning("Invalid date format in holiday: $date, error: {$e->getMessage()}");
                    return null;
                }
            })->filter()->unique()->values()->toArray();
            Log::info("Holidays loaded: " . json_encode($holidays));
            return $holidays;
        } catch (\Exception $e) {
            Log::error("Error in getHolidays: " . $e->getMessage());
            return [];
        }
    }

    public function getAppointmentsInMonth($year, $month)
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            if (!is_numeric($year) || !is_numeric($month)) {
                Log::warning("Invalid year or month provided: year={$year}, month={$month}. Using current date.");
                $year = (int) Jalalian::now()->getYear();
                $month = (int) Jalalian::now()->getMonth();
            }

            $jalaliDateString = sprintf("%d/%02d/01", $year, $month);
            $jalaliDate = Jalalian::fromFormat('Y/m/d', $jalaliDateString);
            $startDate = $jalaliDate->toCarbon()->startOfDay();
            $endDate = Jalalian::fromCarbon($startDate)->addMonths(1)->subDays(1)->toCarbon()->endOfDay();

            Log::info("Fetching appointments for Jalali month {$year}-{$month} (Gregorian: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}), doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");

            $appointments = \App\Models\Appointment::where('doctor_id', $doctorId)
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->whereNull('deleted_at')
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->select('appointment_date')
                ->groupBy('appointment_date')
                ->get()
                ->map(function ($appointment) {
                    $date = Carbon::parse($appointment->appointment_date)->format('Y-m-d');
                    $count = \App\Models\Appointment::where('doctor_id', $this->getAuthenticatedDoctor()->id)
                        ->where('appointment_date', $appointment->appointment_date)
                        ->where('status', '!=', 'cancelled')
                        ->whereNull('deleted_at')
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

            if (empty($appointments)) {
                Log::info("No appointments found for month {$year}-{$month} with doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");
            } else {
                Log::info("Appointments found for month {$year}-{$month}: " . json_encode($appointments));
            }

            return [
                'status' => true,
                'data' => $appointments,
            ];
        } catch (\Exception $e) {
            Log::error("Error in getAppointmentsInMonth: " . $e->getMessage());
            return [
                'status' => false,
                'data' => [],
            ];
        }
    }
    public function handleOpenTransferModal($modalId, $gregorianDate)
    {
        Log::info("Received openTransferModal event", [
            'modalId' => $modalId,
            'gregorianDate' => $gregorianDate,
        ]);

        if ($modalId === 'transfer-modal' && $gregorianDate) {
            Log::info("Opening transfer modal for date: {$gregorianDate}");
            $this->selectedDate = $gregorianDate;
            $this->dispatch('openXModal', id: 'transfer-modal');
        } else {
            Log::error("Invalid or missing parameters in handleOpenTransferModal", [
                'modalId' => $modalId,
                'gregorianDate' => $gregorianDate,
            ]);
            $this->dispatch('show-toastr', type: 'error', message: 'خطا: تاریخ یا شناسه مودال نامعتبر است.');
        }
    }
    public $workSchedule = ['status' => false, 'data' => []];

    public function handleOpenHolidayModal($modalId, $gregorianDate)
    {
        Log::info("Received openHolidayModal event", [
            'modalId' => $modalId,
            'gregorianDate' => $gregorianDate,
        ]);

        if ($modalId === 'holiday-modal' && $gregorianDate) {
            Log::info("Opening holiday modal for date: {$gregorianDate}");
            $this->selectedDate = $gregorianDate;
            $this->showModal = true;
            $this->holidaysData = [
                'status' => true,
                'holidays' => $this->getHolidays(),
            ];
            $this->workSchedule = $this->getWorkScheduleForDate($gregorianDate);
            Log::info("Selected date set to: {$this->selectedDate}, showModal: {$this->showModal}, holidaysData: ", $this->holidaysData);
            Log::info("Work schedule: ", $this->workSchedule);
            $this->dispatch('openXModal', id: 'holiday-modal');
        } else {
            Log::error("Invalid or missing parameters in handleOpenHolidayModal", [
                'modalId' => $modalId,
                'gregorianDate' => $gregorianDate,
            ]);
            $this->dispatch('show-toastr', type: 'error', message: 'خطا: تاریخ یا شناسه مودال نامعتبر است.');
        }
    }
  public $calculator = [
    'day' => null,
    'index' => null,
    'start_time' => null,
    'end_time' => null,
    'appointment_count' => null,
    'time_per_appointment' => null,
    'calculation_mode' => 'count',
];

public function setCalculatorData($day, $index)
{
    $this->calculator['day'] = $day;
    $this->calculator['index'] = $index;
    Log::info("Calculator data set: day={$day}, index={$index}");
}

public function setCalculatorTimes($startTime, $endTime)
{
    $this->calculator['start_time'] = $startTime;
    $this->calculator['end_time'] = $endTime;
    Log::info("Calculator times set: start_time={$startTime}, end_time={$endTime}");
}

public function setCalculationMode($mode)
{
    $this->calculator['calculation_mode'] = $mode;
    Log::info("Calculation mode set: mode={$mode}");
}

public function setCalculatorValues($values)
{
    $this->calculator['appointment_count'] = $values['appointment_count'];
    $this->calculator['time_per_appointment'] = $values['time_per_appointment'];
    Log::info("Calculator values set: ", $values);
}

public function getCalculatorData()
{
    return $this->calculator;
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

        if (!$day || $index === null || !$appointmentCount || !$timePerAppointment) {
            Log::error("Incomplete calculator data", $this->calculator);
            $this->dispatch('show-toastr', type: 'error', message: 'اطلاعات ناقص است.');
            return;
        }

        $doctorId = $this->getAuthenticatedDoctor()->id;
        $schedule = \App\Models\DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $day)
            ->where('is_working', true)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->first();

        if ($schedule) {
            $workHours = $schedule->work_hours ? json_decode($schedule->work_hours, true) : [];
            $appointmentSettings = $schedule->appointment_settings ? json_decode($schedule->appointment_settings, true) : [];

            // به‌روزرسانی max_appointments در work_hours
            if (isset($workHours[$index])) {
                $workHours[$index]['max_appointments'] = $appointmentCount;
            }

            // به‌روزرسانی appointment_settings
            $appointmentSettings[$index] = [
                'start_time' => '00:00',
                'end_time' => '23:59',
                'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'work_hour_key' => $index,
                'max_appointments' => $appointmentCount,
                'appointment_duration' => $timePerAppointment,
            ];

            $schedule->work_hours = json_encode($workHours);
            $schedule->appointment_settings = json_encode($appointmentSettings);
            $schedule->save();

            // به‌روزرسانی workSchedule برای نمایش تغییرات در مودال
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);

            $this->dispatch('show-toastr', type: 'success', message: 'تنظیمات نوبت‌دهی ذخیره شد.');
            $this->dispatch('closeXModal', id: 'calculator-modal');
        } else {
            Log::error("No work schedule found for day: {$day}");
            $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این روز یافت نشد.');
        }
    } catch (\Exception $e) {
        Log::error("Error in saveCalculator: " . $e->getMessage());
        $this->dispatch('show-toastr', type: 'error', message: 'خطا در ذخیره تنظیمات: ' . $e->getMessage());
    } finally {
        $this->isProcessing = false;
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
                Log::error("No selected date for adding holiday", [
                    'selectedDate' => $this->selectedDate,
                    'showModal' => $this->showModal,
                    'clinic_id' => $this->selectedClinicId,
                ]);
                $this->dispatch('show-toastr', type: 'error', message: 'هیچ تاریخی انتخاب نشده است.');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            Log::info("Adding holiday for date: {$this->selectedDate}, doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");

            $holiday = DoctorHoliday::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
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

                $this->holidaysData = [
                    'status' => true,
                    'holidays' => $this->getHolidays(),
                ];

                Log::info("Updated holidaysData before dispatch:", $this->holidaysData);

                $this->dispatch('calendarDataUpdated', [
                    'holidaysData' => $this->holidaysData,
                    'appointmentsData' => $this->appointmentsData,
                    'calendarYear' => $this->calendarYear,
                    'calendarMonth' => $this->calendarMonth,
                ]);
                $this->dispatch('holidayUpdated', date: $this->selectedDate, isHoliday: true);
                $this->dispatch('show-toastr', type: 'success', message: 'این تاریخ تعطیل شد.');

                Log::info("Holiday added successfully for {$this->selectedDate}");
            } else {
                Log::info("Date {$this->selectedDate} is already a holiday");
                $this->dispatch('show-toastr', type: 'warning', message: 'این تاریخ قبلاً تعطیل است.');
            }

            $this->showModal = false;
            $this->selectedDate = null;
            $this->dispatch('closeXModal', id: 'holiday-modal');
        } catch (\Exception $e) {
            Log::error("Error in addHoliday: " . $e->getMessage(), [
                'selectedDate' => $this->selectedDate,
                'clinic_id' => $this->selectedClinicId,
            ]);
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
                Log::error("No selected date for removing holiday", [
                    'selectedDate' => $this->selectedDate,
                    'showModal' => $this->showModal,
                    'clinic_id' => $this->selectedClinicId,
                ]);
                $this->dispatch('show-toastr', type: 'error', message: 'هیچ تاریخی انتخاب نشده است.');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            Log::info("Removing holiday for date: {$this->selectedDate}, doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");

            $holiday = DoctorHoliday::where('doctor_id', $doctorId)
                ->where('status', 'active')
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
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

                    Log::info("Updated holidaysData before dispatch:", $this->holidaysData);

                    $this->dispatch('calendarDataUpdated', [
                        'holidaysData' => $this->holidaysData,
                        'appointmentsData' => $this->appointmentsData,
                        'calendarYear' => $this->calendarYear,
                        'calendarMonth' => $this->calendarMonth,
                    ]);
                    $this->dispatch('holidayUpdated', date: $this->selectedDate, isHoliday: false);
                    $this->dispatch('show-toastr', type: 'success', message: 'این تاریخ از حالت تعطیلی خارج شد.');

                    Log::info("Holiday removed successfully for {$this->selectedDate}");
                } else {
                    Log::info("No holiday found for {$this->selectedDate}");
                    $this->dispatch('show-toastr', type: 'warning', message: 'این تاریخ تعطیل نیست.');
                }
            } else {
                Log::info("No holiday record found for {$this->selectedDate}");
                $this->dispatch('show-toastr', type: 'warning', message: 'هیچ تعطیلی برای این تاریخ ثبت نشده است.');
            }

            $this->showModal = false;
            $this->selectedDate = null;
            $this->dispatch('closeXModal', id: 'holiday-modal');
        } catch (\Exception $e) {
            Log::error("Error in removeHoliday: " . $e->getMessage(), [
                'selectedDate' => $this->selectedDate,
                'clinic_id' => $this->selectedClinicId,
            ]);
            $message = 'خطا در حذف تعطیلی: ' . $e->getMessage();
            $this->dispatch('show-toastr', type: 'error', message: $message);
        } finally {
            $this->isProcessing = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDate = null;
        $this->dispatch('closeXModal', id: 'holiday-modal');
    }

    public function goToFirstAvailableDate()
    {
        // متد برای رفتن به اولین نوبت خالی (فعلاً خالی است)
    }
    public function getWorkScheduleForDate($gregorianDate)
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $date = Carbon::parse($gregorianDate);
            $dayOfWeek = strtolower($date->format('l')); // مثلاً "thursday"

            Log::info("Fetching work schedule for date: {$gregorianDate}, day: {$dayOfWeek}, doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");

            $query = \App\Models\DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayOfWeek)
                ->where('is_working', true);

            if ($this->selectedClinicId === 'default') {
                $query->whereNull('clinic_id');
            } elseif ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
                $query->where('clinic_id', $this->selectedClinicId);
            }

            $schedule = $query->first();

            if (!$schedule) {
                Log::info("No work schedule found for {$dayOfWeek} with doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");
                return [
                    'status' => false,
                    'data' => [],
                ];
            }

            $workHours = $schedule->work_hours ? json_decode($schedule->work_hours, true) : [];
            $appointmentSettings = $schedule->appointment_settings ? json_decode($schedule->appointment_settings, true) : [];

            // هماهنگ‌سازی appointment_settings با work_hours
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

    public function render()
    {
        Log::info("Rendering component", [
            'selectedDate' => $this->selectedDate,
            'showModal' => $this->showModal,
            'holidaysData' => $this->holidaysData,
            'appointmentsData' => $this->appointmentsData,
        ]);
        return view('livewire.dr.panel.turn.schedule.special-days-appointment');
    }
}
