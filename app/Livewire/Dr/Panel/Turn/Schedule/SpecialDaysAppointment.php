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
        $this->selectedDate = $date;
        $this->showModal = true;
        $this->workSchedule = $this->getWorkScheduleForDate($date);
        $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
        $this->dispatch('open-modal', ['id' => 'holiday-modal']);
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
            $this->selectedDate = $gregorianDate;
            $this->showModal = true;
            $this->holidaysData = [
                'status' => true,
                'holidays' => $this->getHolidays(),
            ];
            $this->workSchedule = $this->getWorkScheduleForDate($gregorianDate);
            $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
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

    public function getWorkScheduleForDate($gregorianDate)
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $date = Carbon::parse($gregorianDate);
            $dayOfWeek = strtolower($date->format('l'));
            $cacheKey = "work_schedule_{$doctorId}_{$gregorianDate}_{$this->selectedClinicId}";

            $cachedSchedule = Cache::remember($cacheKey, now()->addHours(24), function () use ($doctorId, $gregorianDate, $dayOfWeek) {
                Log::info("Fetching work schedule from DB for date: {$gregorianDate}, day: {$dayOfWeek}, doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");

                $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                    ->where('date', $gregorianDate)
                    ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                    ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                    ->first();

                if ($specialSchedule) {
                    $workHours = $specialSchedule->work_hours ? json_decode($specialSchedule->work_hours, true) : [];
                    $appointmentSettings = $specialSchedule->appointment_settings ? json_decode($specialSchedule->appointment_settings, true) : [];
                    $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [];
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
                            'emergency_times' => $emergencyTimes,
                        ],
                    ];
                }

                $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                    ->where('day', $dayOfWeek)
                    ->where('is_working', true)
                    ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                    ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                    ->first();

                if (!$schedule) {
                    Log::info("No work schedule found for {$dayOfWeek} with doctor_id: {$doctorId}, clinic_id: {$this->selectedClinicId}");
                    return ['status' => false, 'data' => []];
                }

                $workHours = $schedule->work_hours ? json_decode($schedule->work_hours, true) : [];
                $appointmentSettings = $schedule->appointment_settings ? json_decode($schedule->appointment_settings, true) : [];
                $emergencyTimes = $schedule->emergency_times ? json_decode($schedule->emergency_times, true) : [];
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
                        'emergency_times' => $emergencyTimes,
                    ],
                ];
            });

            return $cachedSchedule;

        } catch (\Exception $e) {
            Log::error("Error in getWorkScheduleForDate: " . $e->getMessage());
            return ['status' => false, 'data' => []];
        }
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
            Log::info("Selected date updated in SpecialDaysAppointment: {$this->selectedDate}", ['workSchedule' => $this->workSchedule]);
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
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $this->selectedDate)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            $emergencyTimes = $specialSchedule && $specialSchedule->emergency_times
                ? json_decode($specialSchedule->emergency_times, true) ?? []
                : [];

            if ($this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours'])) {
                $slot = $this->workSchedule['data']['work_hours'][$this->emergencyModalIndex] ?? null;
                $appointmentSettings = $this->workSchedule['data']['appointment_settings'][$this->emergencyModalIndex] ?? null;

                if ($slot && $appointmentSettings && !empty($slot['start']) && !empty($slot['end']) && isset($slot['max_appointments']) && $slot['max_appointments'] > 0) {
                    $start = Carbon::createFromFormat('H:i', $slot['start']);
                    $end = Carbon::createFromFormat('H:i', $slot['end']);
                    $maxAppointments = (int) $slot['max_appointments'];
                    $totalMinutes = $end->diffInMinutes($start);
                    $slotDuration = $maxAppointments > 0 ? floor($totalMinutes / $maxAppointments) : 0;

                    if ($slotDuration <= 0) {
                        return [
                            'possible' => [],
                            'selected' => $emergencyTimes[$this->emergencyModalIndex] ?? [],
                        ];
                    }

                    $possibleTimes = [];
                    $current = $start->copy();

                    while ($current->lessThan($end) && count($possibleTimes) < $maxAppointments) {
                        $possibleTimes[] = $current->format('H:i');
                        $current->addMinutes($slotDuration);
                    }

                    return [
                        'possible' => $possibleTimes,
                        'selected' => $emergencyTimes[$this->emergencyModalIndex] ?? [],
                    ];
                }
            }

            return [
                'possible' => [],
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
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
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
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            if (!$specialSchedule) {
                $this->dispatch('show-toastr', type: 'error', message: 'برنامه کاری برای این تاریخ یافت نشد.');
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

                $specialSchedule->work_hours = json_encode($workHours);
                $specialSchedule->appointment_settings = json_encode($appointmentSettings);
                $specialSchedule->emergency_times = json_encode($emergencyTimes);
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

            if (empty($day) || $index === null) {
                Log::error("Invalid day or index in calculator data", $this->calculator);
                $this->dispatch('show-toastr', type: 'error', message: 'روز یا بازه زمانی نامعتبر است.');
                return;
            }

            $startTime = $this->calculator['start_time'] ?? $this->workSchedule['data']['work_hours'][$index]['start'] ?? null;
            $endTime = $this->calculator['end_time'] ?? $this->workSchedule['data']['work_hours'][$index]['end'] ?? null;

            if (empty($startTime) || empty($endTime)) {
                Log::error("Missing start or end time", ['start' => $startTime, 'end' => $endTime]);
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً زمان شروع و پایان را وارد کنید.');
                return;
            }

            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
            $totalMinutes = $end->diffInMinutes($start);

            if ($totalMinutes <= 0) {
                Log::error("Invalid time range: end time is not after start time", ['start' => $startTime, 'end' => $endTime]);
                $this->dispatch('show-toastr', type: 'error', message: 'زمان پایان باید بعد از زمان شروع باشد.');
                return;
            }

            if ($calculationMode === 'count') {
                if (!is_numeric($appointmentCount) || $appointmentCount <= 0) {
                    Log::error("Invalid appointment count", ['appointment_count' => $appointmentCount]);
                    $this->dispatch('show-toastr', type: 'error', message: 'لطفاً تعداد نوبت‌ها را به‌درستی وارد کنید.');
                    return;
                }
                $timePerAppointment = $appointmentCount > 0 ? floor($totalMinutes / $appointmentCount) : 0;
            } else {
                if (!is_numeric($timePerAppointment) || $timePerAppointment <= 0) {
                    Log::error("Invalid time per appointment", ['time_per_appointment' => $timePerAppointment]);
                    $this->dispatch('show-toastr', type: 'error', message: 'لطفاً زمان هر نوبت را به‌درستی وارد کنید.');
                    return;
                }
                $appointmentCount = $timePerAppointment > 0 ? floor($totalMinutes / $timePerAppointment) : 0;
            }

            if ($appointmentCount <= 0) {
                Log::error("Calculated appointment count is invalid", ['appointment_count' => $appointmentCount]);
                $this->dispatch('show-toastr', type: 'error', message: 'تعداد نوبت‌های محاسبه‌شده نامعتبر است.');
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
            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [];

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
                'appointment_duration' => $timePerAppointment,
            ];

            $emergencyTimes[$index] = $emergencyTimes[$index] ?? [];

            $specialSchedule->work_hours = json_encode($workHours);
            $specialSchedule->appointment_settings = json_encode($appointmentSettings);
            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();

            $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
            Cache::forget($cacheKey);

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
                    'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
                ],
                [
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                    'emergency_times' => json_encode([]),
                ]
            );

            $emergencyTimes = $specialSchedule->emergency_times ? json_decode($specialSchedule->emergency_times, true) : [];
            $emergencyTimes[$this->emergencyModalIndex] = array_values(array_filter($this->emergencyTimes, fn ($time) => is_string($time)));

            $specialSchedule->emergency_times = json_encode($emergencyTimes);
            $specialSchedule->save();

            $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
            Cache::forget($cacheKey);

            $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
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
                    'clinic_id' => $this->selectedClinicId === 'default' ? null : $this->selectedClinicId,
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

            $existingSettings = $appointmentSettings[$this->scheduleModalIndex] ?? [];
            $maxAppointments = $existingSettings['max_appointments'] ?? $this->workSchedule['data']['work_hours'][$this->scheduleModalIndex]['max_appointments'] ?? 0;
            $appointmentDuration = $existingSettings['appointment_duration'] ?? 0;

            $appointmentSettings[$this->scheduleModalIndex] = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'days' => $selectedDays,
                'work_hour_key' => $this->scheduleModalIndex,
                'max_appointments' => $maxAppointments,
                'appointment_duration' => $appointmentDuration,
            ];

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

                $cacheKey = "work_schedule_{$doctorId}_{$this->selectedDate}_{$this->selectedClinicId}";
                Cache::forget($cacheKey);

                $this->workSchedule = $this->getWorkScheduleForDate($this->selectedDate);
                $this->hasWorkHoursMessage = $this->workSchedule['status'] && !empty($this->workSchedule['data']['work_hours']);
                $this->dispatch('show-toastr', type: 'success', message: 'تنظیم زمان‌بندی حذف شد.');
                $this->dispatch('refresh-schedule-settings');
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
        try {
            $startTime = $this->workSchedule['data']['work_hours'][$index]['start'] ?? '00:00';
            $endTime = $this->workSchedule['data']['work_hours'][$index]['end'] ?? '23:59';

            if (empty($startTime) || empty($endTime)) {
                $this->dispatch('show-toastr', type: 'error', message: 'لطفاً ابتدا زمان شروع و پایان را وارد کنید.');
                return;
            }

            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
            $totalMinutes = $end->diffInMinutes($start);

            if ($totalMinutes <= 0) {
                $this->dispatch('show-toastr', type: 'error', message: 'زمان پایان باید بعد از زمان شروع باشد.');
                return;
            }

            $this->calculator = [
                'day' => $day,
                'index' => $index,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'appointment_count' => $this->workSchedule['data']['work_hours'][$index]['max_appointments'] ?? null,
                'time_per_appointment' => $this->workSchedule['data']['appointment_settings'][$index]['appointment_duration'] ?? null,
                'calculation_mode' => $this->workSchedule['data']['appointment_settings'][$index]['appointment_duration'] ? 'time' : 'count',
            ];

            $this->dispatch('open-modal', id: 'CalculatorModal');
            $this->dispatch('initialize-calculator', [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'index' => $index,
                'day' => $day,
            ]);
        } catch (\Exception $e) {
            Log::error("Error in openCalculatorModal: " . $e->getMessage());
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در باز کردن مودال محاسبه‌گر: ' . $e->getMessage());
        }
    }

    public function openEmergencyModal($day, $index)
    {
        try {
            $this->isEmergencyModalOpen = true;
            $this->emergencyModalDay = $day;
            $this->emergencyModalIndex = $index;
            $this->emergencyTimes = $this->getEmergencyTimes()['possible'] ?? [];
            $this->selectedEmergencyTimes = $this->getEmergencyTimes()['selected'] ?? [];
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
