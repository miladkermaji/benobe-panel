<?php

namespace App\Livewire\Dr\Panel\Turn\Schedule;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use App\Models\SubUser;
use Livewire\Component;
use App\Models\Appointment;
use App\Models\SmsTemplate;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Models\UserBlocking;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use Livewire\Attributes\Validate;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorAppointmentConfig;
use Illuminate\Support\Facades\Validator;

class AppointmentsList extends Component
{
    use WithPagination;
    public $calendarYear;
    public $calendarMonth;
    public $holidaysData = ['status' => true, 'holidays' => []];
    public $appointmentsData = ['status' => true, 'data' => [], 'working_days' => [], 'calendar_days' => 30, 'appointment_settings' => []];
    public $cancelIds = [];
    public $selectedDate;
    public $selectedClinicId = 'default';
    public $searchQuery = '';
    public $filterStatus = '';
    public $attendanceStatus = '';
    public $dateFilter = '';
    public $appointments = [];
    public $clinics = [];
    public $blockedUsers = [];
    public $messages = [];
    public $pagination = [
        'current_page' => 1,
        'last_page' => 1,
        'per_page' => 10,
        'total' => 0,
    ];
    public $isSearchingAllDates = false;
    public $selectedMobiles = []; // اضافه شده برای مسدود کردن گروهی

    // Properties for blocking users
    #[Validate('required', message: 'لطفاً تاریخ شروع مسدودیت را وارد کنید.')]
    #[Validate('regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/', message: 'تاریخ شروع مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.')]
    public $blockedAt;

    #[Validate('required', message: 'لطفاً تاریخ پایان مسدودیت را وارد کنید.')]
    #[Validate('regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/', message: 'تاریخ پایان مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.')]
    #[Validate('after:blockedAt', message: 'تاریخ پایان مسدودیت باید بعد از تاریخ شروع باشد.')]
    public $unblockedAt;

    #[Validate('required|string|max:255', message: 'دلیل مسدودیت نمی‌تواند بیشتر از 255 کاراکتر باشد.')]
    public $blockReason;

    // Properties for sending messages
    #[Validate('required|string|max:255', message: 'عنوان پیام را وارد کنید.')]
    public $messageTitle;

    #[Validate('required|string|max:1000', message: 'متن پیام را وارد کنید.')]
    public $messageContent;

    #[Validate('required|in:all,blocked,specific', message: 'نوع گیرنده را انتخاب کنید.')]
    public $recipientType;

    public $specificRecipient;

    // Properties for rescheduling
    public $rescheduleAppointmentId;
    public $rescheduleNewDate;
    public $rescheduleAppointmentIds = []; // برای جابجایی گروهی
    public $endVisitAppointmentId = null;
    // Properties for ending visit
    public $endVisitDescription;

    // Property for single block user
    public $blockAppointmentId;

    protected $listeners = [
      'updateSelectedDate' => 'updateSelectedDate',
      'searchAllDates' => 'searchAllDates',
      'cancelAppointments' => 'cancelAppointments',
      'blockUser' => 'handleBlockUser',
      'blockMultipleUsers' => 'handleBlockMultipleUsers',
      'confirm-partial-reschedule' => 'confirmPartialReschedule',
      'rescheduleAppointment' => 'handleRescheduleAppointment',
      'setSelectedClinicId' => 'setSelectedClinicId',
      'setCalendarDate' => 'setCalendarDate',
      'confirm-search-all-dates' => 'searchAllDates', // اضافه شده
];
    public function handleRescheduleAppointment($appointmentIds, $newDate)
    {
        Log::info('handleRescheduleAppointment called', [
            'appointmentIds' => $appointmentIds,
            'newDate' => $newDate,
        ]);

        $result = $this->checkRescheduleConditions($newDate, $appointmentIds);

        if (!$result['success']) {
            if (isset($result['partial']) && $result['partial']) {
                // رویداد قبلاً در checkRescheduleConditions ارسال شده
                Log::info('Partial reschedule triggered', $result);
            } else {
                $this->dispatch('show-toastr', [
                    'type' => 'error',
                    'message' => $result['message']
                ]);
            }
            return;
        }

        // ادامه فرآیند جابجایی اگر ظرفیت کافی باشد
        $this->processReschedule($appointmentIds, $newDate, $result['available_slots']);
        $this->dispatch('appointments-rescheduled', [
            'message' => 'نوبت‌ها با موفقیت جابجا شدند.'
        ]);
        $this->loadAppointments();
        $this->dispatch('hideModal');
        $this->reset(['rescheduleAppointmentIds', 'rescheduleAppointmentId']);
    }

    public function initialize()
    {
        $this->dispatch('initialize-tooltips');
    }

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->blockedAt = Jalalian::now()->format('Y/m/d');

        // مقداردهی اولیه calendarYear و calendarMonth
        $this->calendarYear = Jalalian::now()->getYear();
        $this->calendarMonth = Jalalian::now()->getMonth();

        $doctor = $this->getAuthenticatedDoctor();

        // تنظیم clinicId از درخواست یا session
        $this->selectedClinicId = request()->query('selectedClinicId', session('selectedClinicId', '1')); // پیش‌فرض به 1 تغییر کرد

        if ($doctor) {
            $this->loadClinics();
            $this->loadAppointments();
            $this->loadBlockedUsers();
            $this->loadMessages();
            $this->loadCalendarData();
        }


    }
    public function loadCalendarData()
    {
        $this->holidaysData = [
            'status' => true,
            'holidays' => $this->getHolidays(),
        ];
        $this->appointmentsData = $this->getAppointmentsCountData();


    }
    public function getAppointmentsCountData()
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $selectedClinicId = $this->selectedClinicId;

            // استفاده از مقادیر پیش‌فرض اگر calendarYear یا calendarMonth مقدار نداشته باشند
            $year = $this->calendarYear ?? Jalalian::now()->getYear();
            $month = $this->calendarMonth ?? Jalalian::now()->getMonth();



            // دریافت تنظیمات تقویم
            $appointmentConfig = DoctorAppointmentConfig::where('doctor_id', $doctorId)
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();

            $calendarDays = $appointmentConfig ? $appointmentConfig->calendar_days : 30;

            // دریافت روزهای کاری
            $workSchedules = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('is_working', true)
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->pluck('day')
                ->toArray();


            // تبدیل سال و ماه جلالی به میلادی
            $jalaliDate = Jalalian::fromFormat('Y/m/d', sprintf('%d/%02d/01', $year, $month));
            $startDate = $jalaliDate->toCarbon()->startOfDay();
            $endDate = $jalaliDate->toCarbon()->endOfMonth(); // استفاده از endOfMonth برای ماه میلادی معادل

            // تنظیم دقیق برای ماه جلالی
            $jalaliEndDate = Jalalian::fromCarbon($startDate)->addMonths(1)->subDays(1);
            $endDate = $jalaliEndDate->toCarbon()->endOfDay();




            // دریافت تعداد نوبت‌ها
            $appointmentsQuery = DB::table('appointments')
                ->select(DB::raw('appointment_date, COUNT(*) as appointment_count'))
                ->where('doctor_id', $doctorId)
                ->where('status', '!=', 'cancelled')
                ->whereNull('deleted_at');

            if ($selectedClinicId === 'default') {
                $appointmentsQuery->whereNull('clinic_id');
            } elseif ($selectedClinicId && $selectedClinicId !== 'default') {
                $appointmentsQuery->where('clinic_id', $selectedClinicId);
            }

            $appointments = $appointmentsQuery
                ->whereBetween('appointment_date', [$startDate, $endDate])
                ->groupBy('appointment_date')
                ->get();

            $data = $appointments->map(function ($item) {
                return [
                    'appointment_date' => Carbon::parse($item->appointment_date)->format('Y-m-d'),
                    'appointment_count' => (int) $item->appointment_count,
                ];
            })->toArray();

            // دریافت تنظیمات نوبت‌دهی
            $appointmentSettings = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('is_working', true)
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->select('day', 'appointment_settings')
                ->get()
                ->map(function ($schedule) {
                    return [
                        'day' => $schedule->day,
                        'settings' => $schedule->appointment_settings ? json_decode($schedule->appointment_settings, true) : [],
                    ];
                })
                ->toArray();


            return [
                'status' => true,
                'data' => $data,
                'working_days' => $workSchedules,
                'calendar_days' => $calendarDays,
                'appointment_settings' => $appointmentSettings,
            ];
        } catch (\Exception $e) {

            return [
                'status' => false,
                'message' => 'خطا در دریافت داده‌ها',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function handleBlockUser($data)
    {
        $this->blockAppointmentId = $data['appointmentId'];
        $this->blockedAt = $data['blockedAt'];
        $this->unblockedAt = $data['unblockedAt'];
        $this->blockReason = $data['blockReason'];
        $this->blockUser();
    }

    public function handleBlockMultipleUsers($data)
    {
        $this->blockedAt = $data['blockedAt'];
        $this->unblockedAt = $data['unblockedAt'];
        $this->blockReason = $data['blockReason'];
        $this->blockMultipleUsers();
    }

    public function updateSelectedDate($date)
    {
        $selectedDate = is_array($date) && isset($date['date']) ? $date['date'] : $date;
        $this->selectedDate = $selectedDate;
        $this->isSearchingAllDates = false;
        $this->filterStatus = '';
        $this->dateFilter = '';
        $this->resetPage();
        $this->appointments = [];
        $this->loadAppointments();
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

    private function loadClinics()
    {
        $doctor = $this->getAuthenticatedDoctor();
        if ($doctor) {
            $this->clinics = $doctor->clinics()->where('is_active', 0)->get()->toArray();
        }
    }

    public $showNoResultsAlert = false;
    public function loadAppointments()
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return [];
        }

        $gregorianDate = $this->convertToGregorian($this->selectedDate);
        $query = Appointment::with(['doctor', 'patient', 'insurance', 'clinic'])
            ->withTrashed()
            ->where('doctor_id', $doctor->id);

        // اعمال فیلترهای موجود
        if ($this->dateFilter) {
            $today = Carbon::today();
            if ($this->dateFilter === 'current_week') {
                $startOfWeek = $today->copy()->startOfWeek(Carbon::SATURDAY);
                $endOfWeek = $today->copy()->endOfWeek(Carbon::FRIDAY);
                $query->whereBetween('appointment_date', [$startOfWeek, $endOfWeek]);
            } elseif ($this->dateFilter === 'current_month') {
                $startOfMonth = $today->copy()->startOfMonth();
                $endOfMonth = $today->copy()->endOfMonth();
                $query->whereBetween('appointment_date', [$startOfMonth, $endOfMonth]);
            } elseif ($this->dateFilter === 'current_year') {
                $startOfYear = $today->copy()->startOfYear();
                $endOfYear = $today->copy()->endOfYear();
                $query->whereBetween('appointment_date', [$startOfYear, $endOfYear]);
            }
        } elseif ($this->filterStatus !== 'all' && !$this->isSearchingAllDates) {
            $query->whereDate('appointment_date', $gregorianDate);
        }

        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } elseif ($this->selectedClinicId) {
            $query->where('clinic_id', $this->selectedClinicId);
        }

        if ($this->filterStatus && $this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->attendanceStatus) {
            $query->where('attendance_status', $this->attendanceStatus);
        }

        if ($this->searchQuery) {
            $query->whereHas('patient', function ($q) {
                $q->where('first_name', 'like', "%{$this->searchQuery}%")
                    ->orWhere('last_name', 'like', "%{$this->searchQuery}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$this->searchQuery}%"])
                    ->orWhere('mobile', 'like', "%{$this->searchQuery}%")
                    ->orWhere('national_code', 'like', "%{$this->searchQuery}%");
            });
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->paginate($this->pagination['per_page']);

        // استفاده از Jalalian برای تاریخ جلالی
        $jalaliDate = Jalalian::fromCarbon(Carbon::parse($gregorianDate));
        $appointmentData = $this->getAppointmentsInMonth(
            $jalaliDate->getYear(),
            $jalaliDate->getMonth()
        );

        $this->appointments = $appointments->items();
        $this->pagination = [
            'current_page' => $appointments->currentPage(),
            'last_page' => $appointments->lastPage(),
            'per_page' => $appointments->perPage(),
            'total' => $appointments->total(),
        ];

        // تنظیم متغیر جهانی برای جاوااسکریپت
        $this->dispatch('setAppointments', ['appointments' => $appointmentData]);

        // بررسی خالی بودن نتایج و نمایش آلرت یا پیام در جدول
        if (empty($this->appointments) && $this->searchQuery) {
            if ($this->isSearchingAllDates) {
                // در جستجوی کلی، پیام در جدول نمایش داده می‌شود
                $this->dispatch('no-results-found', ['date' => $jalaliDate->format('Y/m/d'), 'searchAll' => true]);
            } else {
                // در جستجوی عادی، آلرت نمایش داده می‌شود
                $this->showNoResultsAlert = true;
                $this->dispatch('show-no-results-alert', ['date' => $jalaliDate->format('Y/m/d')]);
            }
        } else {
            $this->showNoResultsAlert = false;
            $this->dispatch('hide-no-results-alert');
        }

        return $appointmentData;
    }

    public function searchAllDates()
    {
        $this->isSearchingAllDates = true;
        $this->dateFilter = '';
        $this->filterStatus = '';
        $this->resetPage();
        $this->appointments = [];
        $this->showNoResultsAlert = false; // مخفی کردن آلرت
        $this->loadAppointments();
    }

    private function convertToGregorian($date)
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        } elseif (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date)) {
            try {
                $gregorian = Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
                return $gregorian;
            } catch (\Exception $e) {
                $this->dispatch('show-toastr', type: 'error', message: 'خطا در تبدیل تاریخ جلالی به میلادی.');
                return Carbon::now()->format('Y-m-d');
            }
        } else {
            $this->dispatch('show-toastr', type: 'error', message: 'فرمت تاریخ نامعتبر است.');
            return Carbon::now()->format('Y-m-d');
        }
    }

    public function updatedSelectedDate()
    {
        $this->isSearchingAllDates = false;
        $this->filterStatus = '';
        $this->dateFilter = '';
        $this->resetPage();
        $this->appointments = [];
        $this->loadAppointments();
    }

    public function updatedSelectedClinicId()
    {
        $this->isSearchingAllDates = false;
        $this->loadAppointments();
        $this->loadBlockedUsers();
    }

    public function updatedSearchQuery()
    {
        $this->isSearchingAllDates = false;
        $this->loadAppointments();
    }

    public function updatedFilterStatus()
    {
        $this->isSearchingAllDates = false;
        $this->dateFilter = '';
        $this->resetPage();
        $this->appointments = [];
        $this->loadAppointments();
    }

    public function updatedAttendanceStatus()
    {
        $this->isSearchingAllDates = false;
        $this->loadAppointments();
    }

    public function updatedDateFilter()
    {
        $this->isSearchingAllDates = false;
        $this->filterStatus = '';
        $this->resetPage();
        $this->appointments = [];
        $this->loadAppointments();
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
        $this->loadAppointments();
    }

    public function previousPage()
    {
        $this->setPage($this->pagination['current_page'] - 1);
        $this->loadAppointments();
    }

    public function nextPage()
    {
        $this->setPage($this->pagination['current_page'] + 1);
        $this->loadAppointments();
    }

    private function checkRescheduleConditions($newDate, $appointmentIds)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return ['success' => false, 'message' => 'دکتر معتبر یافت نشد.'];
        }

        $newDateCarbon = Carbon::parse($newDate);
        $today = Carbon::today();

        // بررسی تاریخ گذشته
        if ($newDateCarbon->lt($today)) {
            return ['success' => false, 'message' => 'امکان جابجایی به تاریخ گذشته وجود ندارد.'];
        }

        // بررسی بازه تقویم
        $calendarDays = DoctorAppointmentConfig::where('doctor_id', $doctor->id)
            ->value('calendar_days') ?? 30;
        $maxDate = $today->copy()->addDays($calendarDays);
        if ($newDateCarbon->gt($maxDate)) {
            return ['success' => false, 'message' => 'تاریخ مقصد خارج از بازه تقویم مجاز است.'];
        }

        // بررسی تعطیلات
        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctor->id)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);
        if (in_array($newDate, $holidayDates)) {
            return ['success' => false, 'message' => 'تاریخ مقصد تعطیل است.'];
        }

        // دریافت برنامه کاری
        $dayOfWeek = strtolower($newDateCarbon->format('l'));
        $workScheduleQuery = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $dayOfWeek)
            ->where('is_working', true)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $workSchedule = $workScheduleQuery->first();

        if (!$workSchedule) {
            return ['success' => false, 'message' => 'پزشک در این روز ساعات کاری ندارد.'];
        }

        // بررسی برنامه ویژه
        $specialScheduleQuery = SpecialDailySchedule::where('doctor_id', $doctor->id)
            ->where('date', $newDate)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $specialSchedule = $specialScheduleQuery->first();

        $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : json_decode($workSchedule->work_hours, true);
        $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? ['appointment_duration' => 15];

        // محاسبه ظرفیت کل بر اساس max_appointments
        $maxAppointments = 0;
        foreach ($workHours as $period) {
            $maxAppointments += $period['max_appointments'] ?? 0;
        }

        // محاسبه اسلات‌های خالی
        $availableSlots = $this->calculateAvailableSlots($workHours, $appointmentSettings, $newDate, $doctor->id);
        $availableSlotsCount = count($availableSlots);

        // مقایسه ظرفیت با تعداد نوبت‌های درخواستی
        $requiredSlots = count($appointmentIds);
        if ($availableSlotsCount < $requiredSlots || $maxAppointments < $requiredSlots) {
            $nextAvailableDate = $this->getNextAvailableDateAfter($newDate);
            $remainingAppointments = $requiredSlots - min($availableSlotsCount, $maxAppointments);
            $message = "تعداد نوبت‌های خالی در این روز " . min($availableSlotsCount, $maxAppointments) . " است. آیا مایلید $remainingAppointments نوبت به تاریخ " . Jalalian::fromCarbon(Carbon::parse($nextAvailableDate))->format('Y/m/d') . " منتقل شود؟";

            // لاگ‌گیری برای دیباگ
            Log::info('Dispatching show-partial-reschedule-confirm', [
                'message' => $message,
                'appointmentIds' => $appointmentIds,
                'newDate' => $newDate,
                'nextDate' => $nextAvailableDate,
                'availableSlots' => $availableSlots,
            ]);

            // ارسال رویداد به کلاینت
            $this->dispatch('show-partial-reschedule-confirm', [
                'message' => $message,
                'appointmentIds' => $appointmentIds,
                'newDate' => $newDate,
                'nextDate' => $nextAvailableDate,
                'availableSlots' => $availableSlots,
            ]);

            return [
                'success' => false,
                'message' => $message,
                'available_slots' => $availableSlots,
                'next_available_date' => $nextAvailableDate,
                'partial' => true,
            ];
        }

        return [
            'success' => true,
            'available_slots' => $availableSlots,
        ];
    }

    private function calculateAvailableSlots($workHours, $appointmentSettings, $date, $doctorId)
    {
        $slots = [];
        $duration = $appointmentSettings['appointment_duration'] ?? 15;

        // دریافت نوبت‌های موجود برای تاریخ و دکتر
        $existingAppointments = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->pluck('appointment_time')
            ->map(fn ($time) => $time->format('H:i'))
            ->toArray();

        Log::info('Existing appointments for date', [
            'date' => $date,
            'existingAppointments' => $existingAppointments,
        ]);

        $totalSlots = 0;
        foreach ($workHours as $period) {
            $start = Carbon::parse($period['start']);
            $end = Carbon::parse($period['end']);
            $maxAppointments = $period['max_appointments'] ?? PHP_INT_MAX;

            while ($start->lt($end) && $totalSlots < $maxAppointments) {
                $slotTime = $start->format('H:i');
                if (!in_array($slotTime, $existingAppointments)) {
                    $slots[] = $slotTime;
                    $totalSlots++;
                } else {
                    Log::info('Slot already taken', ['slotTime' => $slotTime, 'date' => $date]);
                }
                $start->addMinutes($duration);
            }
        }

        Log::info('Calculated available slots', ['date' => $date, 'slots' => $slots]);
        return $slots;
    }
    public function confirmPartialReschedule($appointmentIds, $newDate, $nextDate, $availableSlots)
    {
        try {
            Log::info('confirmPartialReschedule called', [
                'appointmentIds' => $appointmentIds,
                'newDate' => $newDate,
                'nextDate' => $nextDate,
                'availableSlots' => $availableSlots,
            ]);

            // اعتبارسنجی ورودی‌ها
            if (empty($appointmentIds) || !is_array($appointmentIds)) {
                throw new \Exception('شناسه‌های نوبت نامعتبر است.');
            }
            if (!$newDate || !$nextDate) {
                throw new \Exception('تاریخ‌های انتخاب‌شده نامعتبر هستند.');
            }
            if (empty($availableSlots) || !is_array($availableSlots)) {
                throw new \Exception('اسلات‌های خالی نامعتبر هستند.');
            }

            // جابجایی نوبت‌های ممکن به تاریخ انتخاب‌شده
            $remainingIds = $this->processReschedule($appointmentIds, $newDate, $availableSlots);

            // جابجایی نوبت‌های باقی‌مانده به تاریخ بعدی
            if (!empty($remainingIds) && $nextDate) {
                $nextDateSlots = $this->getAvailableSlotsForDate($nextDate);
                Log::info('Next date slots', ['nextDate' => $nextDate, 'slots' => $nextDateSlots]);

                if (empty($nextDateSlots)) {
                    throw new \Exception('هیچ اسلات خالی برای تاریخ بعدی یافت نشد.');
                }

                $remainingRemainingIds = $this->processReschedule($remainingIds, $nextDate, $nextDateSlots);

                if (!empty($remainingRemainingIds)) {
                    $this->dispatch('show-toastr', [
                        'type' => 'warning',
                        'message' => 'برخی نوبت‌ها به دلیل عدم وجود اسلات خالی جابجا نشدند.'
                    ]);
                } else {
                    $this->dispatch('show-toastr', [
                        'type' => 'success',
                        'message' => 'نوبت‌های باقی‌مانده با موفقیت به تاریخ ' . Jalalian::fromCarbon(Carbon::parse($nextDate))->format('Y/m/d') . ' منتقل شدند.'
                    ]);
                }
            }

            $this->dispatch('appointments-rescheduled', [
                'message' => 'نوبت‌ها با موفقیت جابجا شدند.'
            ]);
            $this->loadAppointments();
            $this->dispatch('hideModal');
            $this->reset(['rescheduleAppointmentIds', 'rescheduleAppointmentId']);
        } catch (\Exception $e) {
            Log::error('Error in confirmPartialReschedule', ['error' => $e->getMessage()]);
            $this->dispatch('show-toastr', [
                'type' => 'error',
                'message' => 'خطایی در جابجایی نوبت رخ داد: ' . $e->getMessage()
            ]);
            $this->dispatch('showModal', 'reschedule-modal');
        }
    }

    private function getAvailableSlotsForDate($date)
    {
        $doctor = $this->getAuthenticatedDoctor();
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $dayOfWeek)
            ->where('is_working', true)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->first();

        if (!$workSchedule) {
            Log::warning('No work schedule found', ['date' => $date, 'dayOfWeek' => $dayOfWeek]);
            return [];
        }

        $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctor->id)
            ->where('date', $date)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->first();

        $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : json_decode($workSchedule->work_hours, true);
        $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? ['appointment_duration' => 15];

        $slots = $this->calculateAvailableSlots($workHours, $appointmentSettings, $date, $doctor->id);
        Log::info('Available slots for date', ['date' => $date, 'slots' => $slots]);

        return $slots;
    }

    private function getNextAvailableDateAfter($startDate)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $today = Carbon::today();
        $calendarDays = DoctorAppointmentConfig::where('doctor_id', $doctorId)->value('calendar_days') ?? 30;
        $maxDate = $today->copy()->addDays($calendarDays);

        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);

        $currentDate = Carbon::parse($startDate)->addDay();
        while ($currentDate->lte($maxDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            if (in_array($dateStr, $holidayDates)) {
                $currentDate->addDay();
                continue;
            }

            $dayOfWeek = strtolower($currentDate->format('l'));
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayOfWeek)
                ->where('is_working', true)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            if (!$workSchedule) {
                $currentDate->addDay();
                continue;
            }

            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $dateStr)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : json_decode($workSchedule->work_hours, true);
            $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? ['max_appointments' => 10, 'appointment_duration' => 15];

            $availableSlots = $this->calculateAvailableSlots($workHours, $appointmentSettings, $dateStr, $doctorId);
            if (!empty($availableSlots)) {
                return $dateStr;
            }

            $currentDate->addDay();
        }

        return null;
    }

    public function updateAppointmentDate($ids, $newDate)
    {
        try {

            // چک کردن ورودی‌ها
            $appointmentIds = is_array($ids) ? $ids : [$ids];
            if (empty($appointmentIds)) {

                return;
            }

            $newDateCarbon = Carbon::parse($newDate);

            // اعتبارسنجی تاریخ
            $validator = Validator::make(['newDate' => $newDate], [
                'newDate' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => $validator->errors()->first()]);
                return;
            }

            // چک کردن شرایط جابجایی
            $conditions = $this->checkRescheduleConditions($newDate, $appointmentIds);

            if (!$conditions['success']) {
                if (isset($conditions['partial']) && $conditions['partial']) {
                    $nextDate = $conditions['next_available_date'];
                    $nextDateJalali = Jalalian::fromCarbon(Carbon::parse($nextDate))->format('Y/m/d');
                    $message = $conditions['message'];



                    $this->dispatch('show-partial-reschedule-confirm', [
                        'message' => $message,
                        'appointmentIds' => $appointmentIds,
                        'newDate' => $newDate,
                        'nextDate' => $nextDate,
                        'availableSlots' => $conditions['available_slots'],
                    ]);
                    $this->dispatch('showModal', 'reschedule-modal');
                    return;
                }

                $this->dispatch('show-toastr', ['type' => 'error', 'message' => $conditions['message']]);
                $this->dispatch('showModal', 'reschedule-modal');
                return;
            }

            // جابجایی نوبت‌ها
            $remainingIds = $this->processReschedule($appointmentIds, $newDate, $conditions['available_slots']);

            if (!empty($remainingIds)) {
                $this->dispatch('show-toastr', [
                    'type' => 'warning',
                    'message' => 'برخی نوبت‌ها به دلیل عدم وجود اسلات خالی جابجا نشدند.'
                ]);
            } else {
                $this->dispatch('show-toastr', [
                    'type' => 'success',
                    'message' => count($appointmentIds) > 1 ? 'نوبت‌ها با موفقیت جابجا شدند.' : 'نوبت با موفقیت جابجا شد.'
                ]);

                $this->dispatch('appointments-rescheduled', [
                              'message' => count($appointmentIds) > 1 ? 'نوبت‌ها با موفقیت جابجا شدند.' : 'نوبت با موفقیت جابجا شد.'
                          ]);

            }

            $this->loadAppointments();
            $this->dispatch('hideModal');
            $this->reset(['rescheduleAppointmentIds', 'rescheduleAppointmentId']);
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'خطایی در جابجایی نوبت رخ داد: ' . $e->getMessage()]);
        }
    }

    public function processReschedule($appointmentIds, $newDate, $availableSlots)
    {
        $remainingIds = [];
        $usedSlots = [];

        Log::info('processReschedule started', [
            'appointmentIds' => $appointmentIds,
            'newDate' => $newDate,
            'availableSlots' => $availableSlots,
        ]);

        foreach ($appointmentIds as $appointmentId) {
            // پیدا کردن اولین اسلات خالی که هنوز استفاده نشده
            $selectedSlot = null;
            foreach ($availableSlots as $slot) {
                if (!in_array($slot, $usedSlots)) {
                    $selectedSlot = $slot;
                    $usedSlots[] = $slot;
                    break;
                }
            }

            if ($selectedSlot) {
                // آپدیت نوبت در جدول appointments
                $appointment = Appointment::find($appointmentId);
                if ($appointment) {
                    $appointment->update([
                        'appointment_date' => $newDate,
                        'appointment_time' => $selectedSlot,
                    ]);

                    Log::info('Appointment rescheduled', [
                        'appointmentId' => $appointmentId,
                        'newDate' => $newDate,
                        'newTime' => $selectedSlot,
                    ]);
                } else {
                    Log::warning('Appointment not found', ['appointmentId' => $appointmentId]);
                    $remainingIds[] = $appointmentId;
                }
            } else {
                // اگر اسلات خالی پیدا نشد، نوبت به لیست باقی‌مانده اضافه می‌شه
                Log::warning('No available slot found for appointment', [
                    'appointmentId' => $appointmentId,
                    'newDate' => $newDate,
                ]);
                $remainingIds[] = $appointmentId;
            }
        }

        Log::info('processReschedule completed', [
            'remainingIds' => $remainingIds,
            'usedSlots' => $usedSlots,
        ]);

        return $remainingIds;
    }

    public function endVisit($id)
    {
        $this->validate([
            'endVisitDescription' => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::where('id', $id)
            ->where('doctor_id', $this->doctor_id)
            ->whereNotIn('status', ['cancelled', 'attended'])
            ->first();

        if ($appointment) {
            $appointment->status = 'attended';
            $appointment->visit_description = $this->endVisitDescription;
            $appointment->save();

            $this->endVisitDescription = '';
            $this->endVisitAppointmentId = null; // ریست کردن
            $this->loadAppointments();
            $this->dispatch('hideModal');
            $this->dispatch('toastr', type: 'success', message: 'ویزیت با موفقیت ثبت شد.');
        }
    }

    public function cancelSingleAppointment($id)
    {
        $this->dispatch('confirm-cancel-single', id: $id);
    }

    public function triggerCancelAppointments()
    {
        $this->cancelAppointments($this->cancelIds);
        $this->cancelIds = [];
    }

    public function cancelAppointments($ids = [])
    {
        $requestData = request()->all();
        $normalizedIds = [];
        if (is_array($ids) && !empty($ids)) {
            if (array_is_list($ids)) {
                $normalizedIds = $ids;
            } else {
                $normalizedIds = array_values($ids);
            }
        } elseif (is_scalar($ids) && !empty($ids)) {
            $normalizedIds = [$ids];
        }

        if (empty($normalizedIds)) {
            $this->dispatch('show-toastr', type: 'error', message: 'هیچ نوبت انتخاب‌شده‌ای یافت نشد.');
            return;
        }

        try {
            $doctor = $this->getAuthenticatedDoctor();
            if (!$doctor) {
                throw new \Exception('دکتر معتبر یافت نشد.');
            }

            $appointments = Appointment::whereIn('id', $normalizedIds)
                ->where('doctor_id', $doctor->id)
                ->whereNotIn('status', ['cancelled', 'attended'])
                ->get();

            if ($appointments->isEmpty()) {
                $this->dispatch('show-toastr', type: 'error', message: 'هیچ نوبت معتبری برای لغو یافت نشد.');
                return;
            }

            foreach ($appointments as $appointment) {
                $appointment->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);

                if ($appointment->patient && $appointment->patient->mobile) {
                    $dateJalali = Jalalian::fromDateTime($appointment->appointment_date)->format('Y/m/d');
                    $message = "کاربر گرامی، نوبت شما در تاریخ {$dateJalali} لغو شد.";
                    SendSmsNotificationJob::dispatch(
                        $message,
                        [$appointment->patient->mobile],
                        null,
                        []
                    )->delay(now()->addSeconds(5));
                }
            }

            $this->dispatch('appointments-cancelled', [
                'message' => count($normalizedIds) > 1 ? 'نوبت‌ها با موفقیت لغو شدند.' : 'نوبت با موفقیت لغو شد.'
            ]);
            $this->loadAppointments();
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطایی در لغو نوبت رخ داد: ' . $e->getMessage());
        }
    }

    private function loadBlockedUsers()
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
        $this->blockedUsers = UserBlocking::with('user')
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->get()->toArray();
    }

    private function loadMessages()
    {
        $this->messages = SmsTemplate::with('user')->latest()->get()->toArray();
    }

    private function processDate($date, $fieldName)
    {
        if (empty($date)) {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date);
            } catch (\Exception $e) {
                throw new \Exception("فرمت تاریخ $fieldName معتبر نیست: $date");
            }
        }
        if (preg_match('/^14\d{2}[-\/]\d{2}[-\/]\d{2}$/', $date)) {
            try {
                $date = str_replace('/', '-', $date);
                return Jalalian::fromFormat('Y-m-d', $date)->toCarbon();
            } catch (\Exception $e) {
                throw new \Exception("فرمت تاریخ جلالی $fieldName معتبر نیست: $date");
            }
        }
        throw new \Exception("فرمت تاریخ $fieldName ناشناخته است: $date");
    }
    public function setSelectedClinicId($clinicId)
    {
        $this->selectedClinicId = $clinicId;
        session(['selectedClinicId' => $clinicId]);
        $this->loadCalendarData();
    }
    public function setCalendarDate($year, $month)
    {
        $this->calendarYear = (int) $year;
        $this->calendarMonth = (int) $month;
        $this->loadCalendarData();
        $this->dispatch('calendarDataUpdated');
    }
    public function blockUser()
    {
        try {
            $this->validate([
                'blockedAt' => [
                    'required',
                    'regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/',
                ],
                'unblockedAt' => [
                    'nullable',
                    'regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/',
                    'after:blockedAt',
                ],
                'blockReason' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ], [
                'blockedAt.required' => 'لطفاً تاریخ شروع مسدودیت را وارد کنید.',
                'blockedAt.regex' => 'فرمت تاریخ شروع مسدودیت معتبر نیست (مثال: 1403/06/15 یا 2024-06-15).',
                'unblockedAt.regex' => 'فرمت تاریخ پایان مسدودیت معتبر نیست (مثال: 1403/06/15 یا 2024-06-15).',
                'unblockedAt.after' => 'تاریخ پایان مسدودیت باید بعد از تاریخ شروع مسدودیت باشد.',
                'blockReason.required' => 'لطفاً دلیل مسدود کردن را وارد کنید.',
                'blockReason.string' => 'دلیل مسدود کردن باید متن باشد.',
                'blockReason.max' => 'دلیل مسدود کردن نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            ]);

            if (empty($this->selectedMobiles)) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'کاربری برای مسدود کردن انتخاب نشده است.']);
                $this->dispatch('showModal', 'block-user-modal');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
            $blockedAt = $this->processDate($this->blockedAt, 'شروع مسدودیت');
            $unblockedAt = $this->unblockedAt ? $this->processDate($this->unblockedAt, 'پایان مسدودیت') : null;

            $blockedUsers = [];
            $alreadyBlocked = [];

            foreach ($this->selectedMobiles as $mobile) {
                $user = User::where('mobile', $mobile)->first();
                if (!$user) {
                    continue;
                }

                $isBlocked = UserBlocking::where('user_id', $user->id)
                    ->where('doctor_id', $doctorId)
                    ->where('clinic_id', $clinicId)
                    ->where('status', 1)
                    ->exists();

                if ($isBlocked) {
                    $alreadyBlocked[] = $mobile;
                    continue;
                }

                $blockingUser = UserBlocking::create([
                    'user_id' => $user->id,
                    'doctor_id' => $doctorId,
                    'clinic_id' => $clinicId,
                    'blocked_at' => $blockedAt,
                    'unblocked_at' => $unblockedAt,
                    'reason' => $this->blockReason,
                    'status' => 1,
                ]);

                $blockedUsers[] = $blockingUser;
            }

            if (empty($blockedUsers) && !empty($alreadyBlocked)) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'کاربر انتخاب‌شده قبلاً مسدود شده است.']);
                $this->dispatch('showModal', 'block-user-modal');
                return;
            }

            if (empty($blockedUsers)) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'کاربری برای مسدود کردن پیدا نشد.']);
                $this->dispatch('showModal', 'block-user-modal');
                return;
            }

            $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'کاربر با موفقیت مسدود شد.']);
            $this->dispatch('hideModal');
            $this->loadBlockedUsers();
            $this->reset(['blockedAt', 'unblockedAt', 'blockReason', 'blockAppointmentId', 'selectedMobiles']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => $firstError]);
            $this->dispatch('showModal', 'block-user-modal');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'خطایی رخ داد: ' . $e->getMessage()]);
            $this->dispatch('showModal', 'block-user-modal');
        }
    }

    public function blockMultipleUsers()
    {
        try {
            $this->validate([
                'blockedAt' => [
                    'required',
                    'regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/',
                ],
                'unblockedAt' => [
                    'nullable',
                    'regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/',
                    'after:blockedAt',
                ],
                'blockReason' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ], [
                'blockedAt.required' => 'لطفاً تاریخ شروع مسدودیت را وارد کنید.',
                'blockedAt.regex' => 'فرمت تاریخ شروع مسدودیت معتبر نیست (مثال: 1403/06/15 یا 2024-06-15).',
                'unblockedAt.regex' => 'فرمت تاریخ پایان مسدودیت معتبر نیست (مثال: 1403/06/15 یا 2024-06-15).',
                'unblockedAt.after' => 'تاریخ پایان مسدودیت باید بعد از تاریخ شروع مسدودیت باشد.',
                'blockReason.required' => 'لطفاً دلیل مسدود کردن را وارد کنید.',
                'blockReason.string' => 'دلیل مسدود کردن باید متن باشد.',
                'blockReason.max' => 'دلیل مسدود کردن نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            ]);

            if (empty($this->selectedMobiles)) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'هیچ کاربری برای مسدود کردن انتخاب نشده است.']);
                $this->dispatch('showModal', 'block-user-modal');
                return;
            }

            $doctorId = $this->getAuthenticatedDoctor()->id;
            $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
            $blockedAt = $this->processDate($this->blockedAt, 'شروع مسدودیت');
            $unblockedAt = $this->unblockedAt ? $this->processDate($this->unblockedAt, 'پایان مسدودیت') : null;

            $blockedUsers = [];
            $alreadyBlocked = [];

            foreach ($this->selectedMobiles as $mobile) {
                $user = User::where('mobile', $mobile)->first();
                if (!$user) {
                    continue;
                }

                $isBlocked = UserBlocking::where('user_id', $user->id)
                    ->where('doctor_id', $doctorId)
                    ->where('clinic_id', $clinicId)
                    ->where('status', 1)
                    ->exists();

                if ($isBlocked) {
                    $alreadyBlocked[] = $mobile;
                    continue;
                }

                $blockingUser = UserBlocking::create([
                    'user_id' => $user->id,
                    'doctor_id' => $doctorId,
                    'clinic_id' => $clinicId,
                    'blocked_at' => $blockedAt,
                    'unblocked_at' => $unblockedAt,
                    'reason' => $this->blockReason,
                    'status' => 1,
                ]);

                $blockedUsers[] = $blockingUser;
            }

            if (empty($blockedUsers) && !empty($alreadyBlocked)) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'کاربران انتخاب‌شده قبلاً مسدود شده‌اند.']);
                $this->dispatch('showModal', 'block-user-modal');
                return;
            }

            if (empty($blockedUsers)) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'هیچ کاربری برای مسدود کردن پیدا نشد.']);
                $this->dispatch('showModal', 'block-user-modal');
                return;
            }

            $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'کاربران با موفقیت مسدود شدند.']);
            $this->dispatch('hideModal');
            $this->loadBlockedUsers();
            $this->reset(['blockedAt', 'unblockedAt', 'blockReason', 'selectedMobiles']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => $firstError]);
            $this->dispatch('showModal', 'block-user-modal');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'خطایی رخ داد: ' . $e->getMessage()]);
            $this->dispatch('showModal', 'block-user-modal');
        }
    }

    public function updateBlockStatus($id, $status)
    {
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
        $userBlocking = UserBlocking::where('id', $id)
            ->where('clinic_id', $clinicId)
            ->firstOrFail();
        $userBlocking->status = $status;
        $userBlocking->save();

        $this->dispatch('show-toastr', type: 'success', message: 'وضعیت با موفقیت به‌روزرسانی شد.');
        $this->loadBlockedUsers();
    }

    public function sendMessage()
    {
        $this->validate();

        $doctorId = $this->getAuthenticatedDoctor()->id;
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
        $recipients = [];
        $userId = null;

        if ($this->recipientType === 'all') {
            $recipients = DB::table('appointments')
                ->where('doctor_id', $doctorId)
                ->join('users', 'appointments.patient_id', '=', 'users.id')
                ->distinct()
                ->pluck('users.mobile')
                ->toArray();

            if (empty($recipients)) {
                $this->dispatch('show-toastr', type: 'error', message: 'هیچ کاربری با شما نوبت ثبت نکرده است.');
                return;
            }
        } elseif ($this->recipientType === 'blocked') {
            $recipients = UserBlocking::where('user_blockings.doctor_id', $doctorId)
                ->where('user_blockings.clinic_id', $clinicId)
                ->where('user_blockings.status', 1)
                ->join('users', 'user_blockings.user_id', '=', 'users.id')
                ->pluck('users.mobile')
                ->toArray();

            if (empty($recipients)) {
                $this->dispatch('show-toastr', type: 'error', message: 'هیچ کاربر مسدودی یافت نشد.');
                return;
            }
        } elseif ($this->recipientType === 'specific') {
            $this->validate([
                'specificRecipient' => 'required|exists:users,mobile',
            ], [
                'specificRecipient.required' => 'شماره موبایل گیرنده را وارد کنید.',
                'specificRecipient.exists' => 'شماره موبایل واردشده در سیستم ثبت نشده یا نوبت نگرفته است.',
            ]);

            $user = User::where('mobile', $this->specificRecipient)->first();
            $recipients[] = $this->specificRecipient;
            $userId = $user->id;
        }

        $smsTemplate = SmsTemplate::create([
            'doctor_id' => $doctorId,
            'user_id' => $userId,
            'title' => $this->messageTitle,
            'content' => $this->messageContent,
            'type' => 'manual',
            'recipient_type' => $this->recipientType,
            'identifier' => uniqid(),
        ]);

        $doctor = Doctor::find($doctorId);
        $doctorName = $doctor->first_name . ' ' . $doctor->last_name;

        SendSmsNotificationJob::dispatch(
            $this->messageContent,
            $recipients,
            null,
            [$doctorName]
        )->delay(now()->addSeconds(5));

        $this->dispatch('show-toastr', type: 'success', message: 'پیام با موفقیت در صف ارسال قرار گرفت.');
        $this->loadMessages();
        $this->reset(['messageTitle', 'messageContent', 'recipientType', 'specificRecipient']);
    }

    public function deleteMessage($id)
    {
        $message = SmsTemplate::findOrFail($id);
        $message->delete();

        $this->dispatch('show-toastr', type: 'success', message: 'پیام با موفقیت حذف شد.');
        $this->loadMessages();
    }

    public function unblockUser($id)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
        $userBlocking = UserBlocking::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->firstOrFail();
        $userBlocking->delete();

        $this->dispatch('show-toastr', type: 'success', message: 'کاربر با موفقیت از لیست مسدودی حذف شد.');
        $this->loadBlockedUsers();
    }

    public function getNextAvailableDate()
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);

        $today = Carbon::today();
        $calendarDays = DoctorAppointmentConfig::where('doctor_id', $doctorId)->value('calendar_days') ?? 30;
        $datesToCheck = collect();

        for ($i = 0; $i <= $calendarDays; $i++) {
            $date = $today->copy()->addDays($i)->format('Y-m-d');
            $datesToCheck->push($date);
        }

        $nextAvailableDate = $datesToCheck->first(function ($date) use ($doctorId, $holidayDates) {
            if (in_array($date, $holidayDates)) {
                return false;
            }

            $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
            $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayOfWeek)
                ->where('is_working', true)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            if (!$workSchedule) {
                return false;
            }

            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $date)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();

            $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : json_decode($workSchedule->work_hours, true);
            $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? ['max_appointments' => 10, 'appointment_duration' => 15];

            $availableSlots = $this->calculateAvailableSlots($workHours, $appointmentSettings, $date, $doctorId);
            return !empty($availableSlots);
        });

        return $nextAvailableDate ?? null;
    }

    public function goToFirstAvailableDate()
    {
        $nextAvailableDate = $this->getNextAvailableDate();
        if ($nextAvailableDate) {
            $this->selectedDate = $nextAvailableDate;
            $this->rescheduleNewDate = $nextAvailableDate;
            $this->isSearchingAllDates = false;
            $this->loadAppointments();
            $this->dispatch('update-reschedule-calendar', date: $nextAvailableDate);
        } else {
            $this->dispatch('show-toastr', type: 'error', message: 'هیچ نوبت خالی یافت نشد.');
        }
    }
    public function getAppointmentsByDateSpecial($date)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->select('id', 'appointment_date')
            ->get();

        return [
            'date' => $date,
            'count' => $appointments->count(),
        ];
    }
    public function getAppointmentsInMonth($year, $month)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->select('appointment_date')
            ->groupBy('appointment_date')
            ->get()
            ->map(function ($appointment) {
                return [
                    'date' => Carbon::parse($appointment->appointment_date)->format('Y-m-d'),
                    'count' => Appointment::where('appointment_date', $appointment->appointment_date)
                        ->where('status', '!=', 'cancelled')
                        ->whereNull('deleted_at')
                        ->count(),
                ];
            });

        return $appointments->toArray();
    }

    public function getHolidays()
    {
        try {
            $doctorId = $this->getAuthenticatedDoctor()->id;
            $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)
                ->where('status', 'active'); // فقط تعطیلات فعال

            if ($this->selectedClinicId === 'default') {
                $holidaysQuery->whereNull('clinic_id');
            } elseif ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
                $holidaysQuery->where('clinic_id', $this->selectedClinicId);
            }

            $holidays = $holidaysQuery->get()->pluck('holiday_dates')->map(function ($holiday) {
                // اطمینان از decode صحیح JSON
                $dates = is_string($holiday) ? json_decode($holiday, true) : $holiday;
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return [];
                }
                return is_array($dates) ? $dates : (is_string($dates) ? [$dates] : []);
            })->flatten()->filter()->map(function ($date) {
                try {
                    // مدیریت تاریخ‌های جلالی و میلادی
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

    public function toggleHoliday($date)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $holidayRecordQuery = DoctorHoliday::where('doctor_id', $doctorId);
        if ($this->selectedClinicId === 'default') {
            $holidayRecordQuery->whereNull('clinic_id');
        } elseif ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
            $holidayRecordQuery->where('clinic_id', $this->selectedClinicId);
        }

        $holidayRecord = $holidayRecordQuery->firstOrCreate([
            'doctor_id' => $doctorId,
            'clinic_id' => ($this->selectedClinicId !== 'default' ? $this->selectedClinicId : null),
        ], [
            'holiday_dates' => json_encode([])
        ]);

        $holidayDates = json_decode($holidayRecord->holiday_dates, true) ?? [];
        if (in_array($date, $holidayDates)) {
            $holidayDates = array_diff($holidayDates, [$date]);
            $message = 'این تاریخ از حالت تعطیلی خارج شد.';
            $isHoliday = false;
        } else {
            $holidayDates[] = $date;
            $message = 'این تاریخ تعطیل شد.';
            $isHoliday = true;
        }

        $holidayRecord->update([
            'holiday_dates' => json_encode(array_values($holidayDates)),
        ]);

        $this->dispatch('show-toastr', type: 'success', message: $message);
        return ['is_holiday' => $isHoliday, 'holiday_dates' => $holidayDates];
    }

    public function getDefaultSchedule($date)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $selectedDate = Carbon::parse($date);
        $dayOfWeek = strtolower($selectedDate->format('l'));

        $specialScheduleQuery = SpecialDailySchedule::where('date', $date);
        if ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
            $specialScheduleQuery->where('clinic_id', $this->selectedClinicId);
        }
        $specialSchedule = $specialScheduleQuery->first();

        if ($specialSchedule) {
            return json_decode($specialSchedule->work_hours, true);
        }

        $workScheduleQuery = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayOfWeek);
        if ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
            $workScheduleQuery->where('clinic_id', $this->selectedClinicId);
        }
        $workSchedule = $workScheduleQuery->first();

        return $workSchedule ? json_decode($workSchedule->work_hours, true) ?? [] : [];
    }

    public function render()
    {
        return view('livewire.dr.panel.turn.schedule.appointments-list');
    }
}
