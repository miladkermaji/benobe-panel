<?php

namespace App\Livewire\Dr\Panel\Turn\Schedule;

use Carbon\Carbon;
use App\Models\Doctor;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\Cache;

class SpecialDaysAppointment extends Component
{
    public $time;
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
        'confirm-add-slot' => 'openAddSlotModal', // تغییر به باز کردن مودال
    ];

    public function mount()
    {
        $this->calendarYear = is_numeric($this->calendarYear) ? (int) $this->calendarYear : (int) Jalalian::now()->getYear();
        $this->calendarMonth = is_numeric($this->calendarMonth) ? (int) $this->calendarMonth : (int) Jalalian::now()->getMonth();
        $this->selectedClinicId = request()->query('selectedClinicId', session('selectedClinicId', 'default'));
        $this->loadCalendarData();
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
            $cacheKey = "holidays_{$doctorId}_{$this->selectedClinicId}";

            return Cache::remember($cacheKey, now()->addHours(24), function () use ($doctorId) {
                $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)->where('status', 'active');
                if ($this->selectedClinicId === 'default') {
                    $holidaysQuery->whereNull('clinic_id');
                } elseif ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
                    $holidaysQuery->where('clinic_id', $this->selectedClinicId);
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
            });
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

                SpecialDailySchedule::where('doctor_id', $doctorId)
                    ->where('date', $this->selectedDate)
                    ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                    ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                    ->delete();

                $this->holidaysData = [
                    'status' => true,
                    'holidays' => $this->getHolidays(),
                ];

                $this->dispatch('calendarDataUpdated', [
                    'holidaysData' => $this->holidaysData,
                    'appointmentsData' => $this->appointmentsData,
                    'calendarYear' => $this->calendarYear,
                    'calendarMonth' => $this->calendarMonth,
                ]);

                $cacheKey = "holidays_{$doctorId}_{$this->selectedClinicId}";
                Cache::forget($cacheKey);

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

                    $this->dispatch('calendarDataUpdated', [
                        'holidaysData' => $this->holidaysData,
                        'appointmentsData' => $this->appointmentsData,
                        'calendarYear' => $this->calendarYear,
                        'calendarMonth' => $this->calendarMonth,
                    ]);

                    $cacheKey = "holidays_{$doctorId}_{$this->selectedClinicId}";
                    Cache::forget($cacheKey);

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
        $this->workSchedule = ['status' => false, 'data' => []];
        $this->dispatch('close-modal', id: 'holiday-modal');
    }

    public function getWorkScheduleForDate($date)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $cacheKey = "work_schedule_{$doctorId}_{$date}_{$this->selectedClinicId}";

        Log::info("Fetching work schedule for date: {$date}, doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($doctorId, $date) {
            // ابتدا بررسی SpecialDailySchedule
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $date)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            if ($specialSchedule && !empty($specialSchedule->work_hours)) {
                $workHours = json_decode($specialSchedule->work_hours, true);
                $appointmentSettings = json_decode($specialSchedule->appointment_settings, true) ?? [];
                $emergencyTimes = json_decode($specialSchedule->emergency_times, true) ?? [];

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
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            // تنظیم متغیر برای نشان دادن منبع داده
            $this->isFromSpecialDailySchedule = false;

            if ($workSchedule && !empty($workSchedule->work_hours)) {
                $workHours = json_decode($workSchedule->work_hours, true);
                $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? [];
                $emergencyTimes = json_decode($workSchedule->emergency_times, true) ?? [];

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
        });
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
                    ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                    ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                    ->first();
                $this->isFromSpecialDailySchedule = $specialSchedule && !empty($specialSchedule->work_hours);
            } else {
                $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            }
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->emergencyTimes = $this->getEmergencyTimes();
            Log::info("Selected date updated in SpecialDaysAppointment: {$this->selectedDate}", ['workSchedule' => $this->workSchedule, 'isFromSpecialDailySchedule' => $this->isFromSpecialDailySchedule]);
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

    public function getEmergencyTimes()
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;

            // خواندن زمان‌های انتخاب‌شده از SpecialDailySchedule
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            $emergencyTimes = $specialSchedule && $specialSchedule->emergency_times
                ? json_decode($specialSchedule->emergency_times, true) ?? []
                : [];

            // تولید زمان‌های ممکن
            $possibleTimes = [];
            if ($this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours'])) {
                $slot = $this->workSchedule['data']['work_hours'][$this->emergencyModalIndex] ?? null;
                $appointmentSettings = $this->workSchedule['data']['appointment_settings'][$this->emergencyModalIndex] ?? null;

                if ($slot && $appointmentSettings && !empty($slot['start']) && !empty($slot['end']) && isset($slot['max_appointments']) && $slot['max_appointments'] > 0) {
                    // اعتبارسنجی فرمت زمان
                    try {
                        $start = Carbon::createFromFormat('H:i', $slot['start']);
                        $end = Carbon::createFromFormat('H:i', $slot['end']);
                    } catch (\Exception $e) {
                        Log::error("Invalid time format in getEmergencyTimes", [
                            'start' => $slot['start'],
                            'end' => $slot['end'],
                            'error' => $e->getMessage(),
                        ]);
                        return [
                            'possible' => [],
                            'selected' => $emergencyTimes[$this->emergencyModalIndex] ?? [],
                        ];
                    }

                    if (!$start || !$end || $end->lessThanOrEqualTo($start)) {
                        Log::warning("Invalid time range", [
                            'start' => $slot['start'],
                            'end' => $slot['end'],
                        ]);
                        return [
                            'possible' => [],
                            'selected' => $emergencyTimes[$this->emergencyModalIndex] ?? [],
                        ];
                    }

                    $maxAppointments = (int) $slot['max_appointments'];


                    $totalMinutes = abs($end->diffInMinutes($start));

                    $slotDuration = $maxAppointments > 0 ? floor($totalMinutes / $maxAppointments) : 0;

                    if ($slotDuration <= 0) {
                        Log::warning("Invalid slot duration calculated", [
                            'slotDuration' => $slotDuration,
                            'totalMinutes' => $totalMinutes,
                            'maxAppointments' => $maxAppointments,
                        ]);
                        return [
                            'possible' => [],
                            'selected' => $emergencyTimes[$this->emergencyModalIndex] ?? [],
                        ];
                    }

                    // تولید زمان‌های ممکن
                    $possibleTimes = [];
                    $current = $start->copy();
                    while ($current->lessThan($end) && count($possibleTimes) < $maxAppointments) {
                        $possibleTimes[] = $current->format('H:i');
                        $current->addMinutes($slotDuration);
                    }

                    Log::info("Emergency times generated", [
                        'possibleTimes' => $possibleTimes,
                        'slotDuration' => $slotDuration,
                        'start' => $slot['start'],
                        'end' => $slot['end'],
                        'maxAppointments' => $maxAppointments,
                    ]);
                } else {
                    Log::warning("Missing or invalid slot data", [
                        'slot' => $slot,
                        'appointmentSettings' => $appointmentSettings,
                        'emergencyModalIndex' => $this->emergencyModalIndex,
                    ]);
                }
            } else {
                Log::warning("No work hours available in workSchedule", [
                    'workSchedule' => $this->workSchedule,
                ]);
            }

            return [
                'possible' => $possibleTimes,
                'selected' => $emergencyTimes[$this->emergencyModalIndex] ?? [],
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
            Log::warning("Processing already in progress, exiting addSlot");
            return;
        }

        $this->isProcessing = true;

        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;

            // بررسی منبع داده‌ها
            $specialSchedule = SpecialDailySchedule::where([
                'doctor_id' => $doctorId,
                'date' => $this->selectedDate,
                'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
            ])->first();

            $workHours = $specialSchedule && $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $isFromDoctorWorkSchedule = empty($workHours) && $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);

            if ($isFromDoctorWorkSchedule) {
                // اگر داده‌ها از DoctorWorkSchedule لود شده باشند، مودال add-slot-modal را باز کن
                $this->showAddSlotModal = true;
                $this->savePreviousRows = true;
                Log::info("addSlot triggered, opening add-slot-modal", [
                    'selectedDate' => $this->selectedDate,
                    'workSchedule' => $this->workSchedule,
                ]);
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
                        Log::warning("Previous slot is incomplete", ['lastSlot' => $lastSlot]);
                        return;
                    }
                }

                // ایجاد یا به‌روزرسانی SpecialDailySchedule
                $specialSchedule = SpecialDailySchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctorId,
                        'date' => $this->selectedDate,
                        'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
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

                // پاک کردن کش
                $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
                Cache::forget($cacheKey);

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
                Log::info("New slot added directly for SpecialDailySchedule", [
                    'newIndex' => $newIndex,
                    'workSchedule' => $this->workSchedule,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error in addSlot: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در افزودن بازه زمانی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function confirmAddSlot($savePrevious = true)
    {
        if ($this->isProcessing) {
            Log::warning("Processing already in progress, exiting confirmAddSlot");
            return;
        }

        $this->isProcessing = true;

        try {
            $this->savePreviousRows = $savePrevious;
            $doctorId = $this->getAuthenticatedDoctor()->id;
            Log::info("Authenticated doctor ID: {$doctorId}");

            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
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
                Log::info("Clearing all rows as savePreviousRows is false");
                $workHours = [];
                $appointmentSettings = [];
                $emergencyTimes = [[]];
            } else {
                Log::info("Saving existing rows");
                $validWorkHours = [];
                $validAppointmentSettings = [];
                $validEmergencyTimes = [];

                foreach ($this->workSchedule['data']['work_hours'] as $index => $slot) {
                    if (!empty($slot['start']) && !empty($slot['end']) && !empty($slot['max_appointments']) && $slot['max_appointments'] > 0) {
                        if ($this->hasTimeOverlap($slot['start'], $slot['end'], array_diff_key($validWorkHours, [$index => $slot]))) {
                            Log::warning("Time overlap detected for slot at index {$index}", ['slot' => $slot]);
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
                            Log::warning("Invalid emergency_times format at index {$index}", ['emergency_times' => $emergencyTime]);
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
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();

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

            $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
            Cache::forget($cacheKey);

            $this->showAddSlotModal = false;
            $this->dispatch('show-toastr', type: 'success', message: $this->savePreviousRows ? 'ردیف جدید اضافه شد.' : 'ردیف‌های قبلی حذف شدند و یک ردیف خالی اضافه شد.');
            $this->dispatch('refresh-timepicker');
            $this->dispatch('refresh');
            Log::info("confirmAddSlot completed", ['workSchedule' => $this->workSchedule]);
        } catch (\Exception $e) {
            Log::error("Exception in confirmAddSlot: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
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

        $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
        Cache::forget($cacheKey);

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
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            $isFromDoctorWorkSchedule = !$specialSchedule;

            if ($isFromDoctorWorkSchedule) {
                // فقط UI رو خالی کن
                $this->workSchedule['data']['work_hours'][$index] = [
                    'start' => '',
                    'end' => '',
                    'max_appointments' => '',
                ];
                $this->dispatch('show-toastr', type: 'success', message: 'بازه زمانی خالی شد. لطفاً مقادیر جدید را وارد کنید.');
                $this->dispatch('refresh-timepicker');
            } else {
                // حذف فیزیکی از SpecialDailySchedule با SweetAlert
                $this->dispatch('confirm-delete-slot', ['index' => $index]);
            }
        } catch (\Exception $e) {
            Log::error("Error in removeSlot: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در حذف بازه زمانی: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function confirmDeleteSlot($index)
    {
        Log::info("confirmDeleteSlot called with index: " . $index);

        if ($this->isProcessing) {
            Log::warning("Processing already in progress, exiting confirmDeleteSlot");
            return;
        }

        $this->isProcessing = true;

        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            if (!$specialSchedule) {
                $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این تاریخ یافت نشد.');
                Log::warning("No SpecialDailySchedule found for date: " . $this->selectedDate);
                return;
            }

            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [];

            if (isset($workHours[$index])) {
                unset($workHours[$index]);
                unset($appointmentSettings[$index]);
                unset($emergencyTimes[$index]);

                $workHours = array_values($workHours);
                $appointmentSettings = array_values($appointmentSettings);
                $emergencyTimes = array_values($emergencyTimes);

                if (empty($workHours)) {
                    // اگر هیچ ردیفی باقی نماند، SpecialDailySchedule را حذف کن
                    $specialSchedule->delete();
                    $this->workSchedule = [
                        'status' => false,
                        'data' => [
                            'day' => strtolower(Carbon::parse($this->selectedDate)->englishDayOfWeek),
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
                } else {
                    // به‌روزرسانی SpecialDailySchedule
                    $specialSchedule->work_hours = json_encode($workHours);
                    $specialSchedule->appointment_settings = json_encode($appointmentSettings);
                    $specialSchedule->emergency_times = json_encode($emergencyTimes);
                    $specialSchedule->save();
                    $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
                }

                $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
                Cache::forget($cacheKey);

                $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
                $this->dispatch('show-toastr', type: 'success', message: 'بازه زمانی حذف شد.');
                $this->dispatch('refresh-timepicker');
                Log::info("Slot deleted from SpecialDailySchedule: " . $index);
            } else {
                $this->dispatch('show-toastr', type: 'error', message: 'بازه زمانی نامعتبر است.');
                Log::warning("Invalid slot index: " . $index);
            }
        } catch (\Exception $e) {
            Log::error("Error in confirmDeleteSlot: " . $e->getMessage());
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
                Log::error("Missing start or end time in calculator", ['start_time' => $startTime, 'end_time' => $endTime]);
                $this->dispatch('show-toastr', type: 'error', message: 'زمان شروع یا پایان مشخص نشده است.');
                return;
            }

            // اعتبارسنجی فرمت زمان
            if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $startTime) ||
                !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $endTime)) {
                Log::error("Invalid time format in calculator", ['start_time' => $startTime, 'end_time' => $endTime]);
                $this->dispatch('show-toastr', type: 'error', message: 'فرمت زمان نامعتبر است.');
                return;
            }

            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);

            // بررسی ترتیب زمانی
            if ($end->lte($start)) {
                Log::error("End time is not after start time", ['start_time' => $startTime, 'end_time' => $endTime]);
                $this->dispatch('show-toastr', type: 'error', message: 'زمان پایان باید بعد از زمان شروع باشد.');
                return;
            }


            $totalMinutes = abs($end->diffInMinutes($start));


            if ($totalMinutes <= 0) {
                Log::error("Invalid time range in calculator", ['total_minutes' => $totalMinutes]);
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
                Log::warning("Invalid input values for calculator", ['values' => $values]);
                return;
            }

            Log::info("Calculator values updated", $this->calculator);
            $this->dispatch('update-calculator-ui', $this->calculator);
        } catch (\Exception $e) {
            Log::error("Error in setCalculatorValues: " . $e->getMessage(), ['values' => $values]);
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
                Log::error("Invalid time format in saveCalculator: " . $e->getMessage());
                $this->dispatch('show-toastr', type: 'error', message: 'فرمت زمان نامعتبر است.');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
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
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();

            // پاک کردن کش
            $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
            Cache::forget($cacheKey);

            // به‌روزرسانی workSchedule
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
            $this->dispatch('show-toastr', type: 'success', message: 'تنظیمات ساعت کاری ذخیره شد.');
            $this->dispatch('close-modal', 'CalculatorModal');
            $this->dispatch('refresh-timepicker');
        } catch (\Exception $e) {
            Log::error("Error in saveCalculator: " . $e->getMessage());
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
            Log::error("Error in hasTimeOverlap: " . $e->getMessage());
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
            $doctorId = $this->getAuthenticatedDoctor()->id;

            // دریافت یا ایجاد ردیف در SpecialDailySchedule
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
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

            // بررسی منبع داده (SpecialDailySchedule یا DoctorWorkSchedule)
            $isFromDoctorWorkSchedule = empty($workHours) && $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);

            if ($isFromDoctorWorkSchedule) {
                // اگر از DoctorWorkSchedule لود شده، داده‌ها را از workSchedule کپی کن
                $workHours = $this->workSchedule['data']['work_hours'];
                $appointmentSettings = [];
                $emergencyTimes = $this->workSchedule['data']['emergency_times'];

                // ایجاد appointment_settings برای تمام ردیف‌های work_hours
                foreach ($workHours as $index => $slot) {
                    $appointmentSettings[$index] = [
                        'start_time' => '00:00',
                        'end_time' => '23:59',
                        'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                        'work_hour_key' => $index,
                    ];
                }
            } else {
                // اگر از SpecialDailySchedule لود شده، فقط appointment_settings ردیف فعلی را به‌روزرسانی کن
                if (!isset($appointmentSettings[$this->emergencyModalIndex])) {
                    $appointmentSettings[$this->emergencyModalIndex] = [
                        'start_time' => '00:00',
                        'end_time' => '23:59',
                        'days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                        'work_hour_key' => $this->emergencyModalIndex,
                    ];
                } else {
                    $appointmentSettings[$this->emergencyModalIndex] = [
                        'start_time' => $appointmentSettings[$this->emergencyModalIndex]['start_time'] ?? '00:00',
                        'end_time' => $appointmentSettings[$this->emergencyModalIndex]['end_time'] ?? '23:59',
                        'days' => $appointmentSettings[$this->emergencyModalIndex]['days'] ?? ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                        'work_hour_key' => $this->emergencyModalIndex,
                    ];
                }
            }

            // اعتبارسنجی زمان‌های اورژانسی
            $selectedTimes = array_keys(array_filter($this->selectedEmergencyTimes, fn ($value) => $value));
            $validEmergencyTimes = array_values(array_filter($selectedTimes, fn ($time) => in_array($time, $this->emergencyTimes['possible'] ?? [])));

            // بررسی تداخل زمانی برای زمان‌های اورژانسی
            $currentSlot = $workHours[$this->emergencyModalIndex] ?? null;
            if ($currentSlot && !empty($currentSlot['start']) && !empty($currentSlot['end'])) {
                $slotStart = Carbon::createFromFormat('H:i', $currentSlot['start']);
                $slotEnd = Carbon::createFromFormat('H:i', $currentSlot['end']);

                foreach ($validEmergencyTimes as $time) {
                    $emergencyTime = Carbon::createFromFormat('H:i', $time);
                    if ($emergencyTime->lessThan($slotStart) || $emergencyTime->greaterThanOrEqualTo($slotEnd)) {
                        $this->dispatch('show-toastr', type: 'error', message: "زمان اورژانسی $time خارج از بازه کاری است.");
                        return;
                    }
                }

                // بررسی تداخل با سایر بازه‌های work_hours
                foreach (array_diff_key($workHours, [$this->emergencyModalIndex => []]) as $otherSlot) {
                    if (!empty($otherSlot['start']) && !empty($otherSlot['end'])) {
                        $otherStart = Carbon::createFromFormat('H:i', $otherSlot['start']);
                        $otherEnd = Carbon::createFromFormat('H:i', $otherSlot['end']);
                        foreach ($validEmergencyTimes as $time) {
                            $emergencyTime = Carbon::createFromFormat('H:i', $time);
                            if ($emergencyTime->between($otherStart, $otherEnd)) {
                                $this->dispatch('show-toastr', type: 'error', message: "زمان اورژانسی $time با بازه کاری دیگر تداخل دارد.");
                                return;
                            }
                        }
                    }
                }
            }

            // ذخیره زمان‌های اورژانسی
            $emergencyTimes[$this->emergencyModalIndex] = $validEmergencyTimes;

            // ذخیره داده‌ها در دیتابیس
            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();

            // پاک کردن کش
            $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
            Cache::forget($cacheKey);

            // به‌روزرسانی workSchedule
            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);

            // ارسال اعلان موفقیت
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
            // اعتبارسنجی ورودی‌ها
            if (empty($startTime) || empty($endTime)) {
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً زمان شروع و پایان را وارد کنید.');
                return;
            }

            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);

            if ($end->lessThanOrEqualTo($start)) {
                $this->dispatch('show-toastr', type: 'error', message: 'زمان پایان باید بعد از زمان شروع باشد.');
                return;
            }

            $selectedDays = array_keys(array_filter($this->selectedScheduleDays, fn ($value) => $value));
            if (empty($selectedDays)) {
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً حداقل یک روز انتخاب کنید.');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::firstOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'date' => $this->selectedDate,
                    'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]
            );

            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];

            // به‌روزرسانی work_hours
            if (!isset($workHours[$this->scheduleModalIndex])) {
                $workHours[$this->scheduleModalIndex] = [
                    'start' => $startTime,
                    'end' => $endTime,
                    'max_appointments' => 0,
                ];
            } else {
                $workHours[$this->scheduleModalIndex]['start'] = $startTime;
                $workHours[$this->scheduleModalIndex]['end'] = $endTime;
            }

            // به‌روزرسانی appointment_settings
            $appointmentSettings[$this->scheduleModalIndex] = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'days' => $selectedDays,
                'work_hour_key' => $this->scheduleModalIndex,
                'max_appointments' => $workHours[$this->scheduleModalIndex]['max_appointments'] ?? 0,
                'appointment_duration' => $appointmentSettings[$this->scheduleModalIndex]['appointment_duration'] ?? 0,
            ];

            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->save();

            $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
            Cache::forget($cacheKey);

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
        Log::info("deleteScheduleSetting called with day: {$day}, index: {$index}");

        if ($this->isProcessing) {
            Log::warning("Processing already in progress, exiting deleteScheduleSetting");
            return;
        }

        $this->isProcessing = true;

        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            if (!$specialSchedule) {
                $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این تاریخ یافت نشد.');
                Log::warning("No SpecialDailySchedule found for date: " . $this->selectedDate);
                return;
            }

            $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
            $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];

            if (isset($appointmentSettings[$index])) {
                unset($appointmentSettings[$index]);
                unset($workHours[$index]); // حذف work_hours مرتبط
                $appointmentSettings = array_values($appointmentSettings);
                $workHours = array_values($workHours);

                // به‌روزرسانی یا حذف SpecialDailySchedule
                if (empty($appointmentSettings) && empty($workHours)) {
                    $specialSchedule->delete();
                    $this->workSchedule = [
                        'status' => false,
                        'data' => [
                            'day' => strtolower(Carbon::parse($this->selectedDate)->englishDayOfWeek),
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
                } else {
                    $specialSchedule->appointment_settings = json_encode($appointmentSettings);
                    $specialSchedule->work_hours = json_encode($workHours);
                    $specialSchedule->emergency_times = json_encode(array_values($specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : []));
                    $specialSchedule->save();
                    $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
                }

                $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
                Cache::forget($cacheKey);

                // به‌روزرسانی چک‌باکس‌ها
                $this->selectedScheduleDays = array_fill_keys(
                    ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    false
                );
                foreach ($appointmentSettings as $setting) {
                    foreach ($setting['days'] as $day) {
                        $this->selectedScheduleDays[$day] = true;
                    }
                }

                $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
                $this->dispatch('show-toastr', type: 'success', message: 'تنظیم زمان‌بندی حذف شد.');
                $this->dispatch('refresh-schedule-settings');
                Log::info("Schedule setting deleted: index {$index}");
            } else {
                $this->dispatch('show-toastr', type: 'error', message: 'تنظیم زمان‌بندی نامعتبر است.');
                Log::warning("Invalid schedule setting index: " . $index);
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
        try {
            // بررسی وجود داده‌های work_hours
            if (!isset($this->workSchedule['data']['work_hours'][$index])) {
                Log::error("Work schedule data not found for index: {$index}", ['workSchedule' => $this->workSchedule]);
                $this->dispatch('show-toastr', type: 'error', message: 'داده‌های برنامه کاری برای این بازه زمانی یافت نشد.');
                return;
            }

            $startTime = $this->workSchedule['data']['work_hours'][$index]['start'] ?? null;
            $endTime = $this->workSchedule['data']['work_hours'][$index]['end'] ?? null;

            // اعتبارسنجی زمان‌های شروع و پایان
            if (empty($startTime) || empty($endTime)) {
                Log::warning("Empty start or end time", ['startTime' => $startTime, 'endTime' => $endTime]);
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً ابتدا زمان شروع و پایان را وارد کنید.');
                return;
            }

            // بررسی فرمت زمان
            try {
                $start = Carbon::createFromFormat('H:i', $startTime);
                $end = Carbon::createFromFormat('H:i', $endTime);
            } catch (\Exception $e) {
                Log::error("Invalid time format", ['startTime' => $startTime, 'endTime' => $endTime, 'error' => $e->getMessage()]);
                $this->dispatch('show-toastr', type: 'error', message: 'فرمت زمان نامعتبر است.');
                return;
            }

            if (!$start || !$end) {
                Log::error("Failed to parse time", ['startTime' => $startTime, 'endTime' => $endTime]);
                $this->dispatch('show-toastr', type: 'error', message: 'زمان شروع یا پایان نامعتبر است.');
                return;
            }

            // محاسبه تفاوت زمانی

            $totalMinutes = abs($end->diffInMinutes($start));


            if ($end->lessThanOrEqualTo($start)) {
                Log::warning("End time is not after start time", ['startTime' => $startTime, 'endTime' => $endTime, 'totalMinutes' => $totalMinutes]);
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

            Log::info("Opening CalculatorModal", ['calculator' => $this->calculator]);

            // باز کردن مودال با شناسه معتبر
            $this->dispatch('open-modal', id: 'CalculatorModal');
            $this->dispatch('initialize-calculator', [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'index' => $index,
                'day' => $day,
            ]);
        } catch (\Exception $e) {
            Log::error("Error in openCalculatorModal: " . $e->getMessage(), ['day' => $day, 'index' => $index]);
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در باز کردن مودال ساعت کاری: ' . $e->getMessage());
        }
    }

    public function openEmergencyModal($day, $index)
    {
        try {
            $this->isEmergencyModalOpen = true;
            $this->emergencyModalDay = $day;
            $this->emergencyModalIndex = $index;
            $emergencyData = $this->getEmergencyTimes();
            $this->emergencyTimes = [
                'possible' => $emergencyData['possible'] ?? [],
                'selected' => $emergencyData['selected'] ?? [],
            ];
            // تنظیم selectedEmergencyTimes برای UI
            $this->selectedEmergencyTimes = array_fill_keys($emergencyData['selected'] ?? [], true);
            $this->dispatch('open-modal', id: 'emergencyModal');
        } catch (\Exception $e) {
            Log::error("Error in openEmergencyModal: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در باز کردن مودال اورژانسی: ' . $e->getMessage());
        }
    }

    public function openScheduleModal($day, $index)
    {
        try {
            $this->scheduleModalDay = $day;
            $this->scheduleModalIndex = $index;

            $settings = $this->workSchedule['data']['appointment_settings'][$index] ?? [];
            $this->selectedScheduleDays = array_fill_keys($settings['days'] ?? [], true);
            $this->selectAllScheduleModal = count($this->selectedScheduleDays) === 7;

            $this->dispatch('open-modal', id: 'scheduleModal');
        } catch (\Exception $e) {
            Log::error("Error in openScheduleModal: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در باز کردن مودال زمان‌بندی: ' . $e->getMessage());
        }
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
        Log::info("Selected emergency times updated", ['emergencyTimes' => $this->emergencyTimes]);
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
                    'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
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

            $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
            Cache::forget($cacheKey);

            $this->dispatch('show-toastr', type: 'success', message: 'تغییرات ساعت کاری ذخیره شد.');
        } catch (\Exception $e) {
            Log::error("Error in updatedWorkScheduleDataWorkHours: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در ذخیره تغییرات: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function initializeCalculator($params)
    {
        Log::info("Initialize calculator called with params", ['params' => $params]);
    }

    public function render()
    {
        return view('livewire.dr.panel.turn.schedule.special-days-appointment');
    }
}
