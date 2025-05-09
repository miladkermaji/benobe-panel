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
use App\Models\DoctorService;
use Livewire\Attributes\Validate;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Auth;
use App\Models\CounselingAppointment;
use App\Models\DoctorCounselingConfig;
use App\Models\CounselingDailySchedule;
use App\Models\DoctorAppointmentConfig;
use App\Models\DoctorCounselingHoliday;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorCounselingWorkSchedule;

class CounselingAppointmentsList extends Component
{
    use WithPagination;
    public $selectedServiceIds = [];
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
    public $discountInputPercentage = 0;
    public $discountInputAmount = 0;
    public $messages = [];
    public $pagination = [
        'current_page' => 1,
        'last_page' => 1,
        'per_page' => 10,
        'total' => 0,
    ];

    public $isSearchingAllDates = false;
    public $selectedMobiles = [];
    public $selectedInsuranceId;
    public $selectedServiceId;
    public $isFree = false;
    public $discountPercentage = 0;
    public $discountAmount = 0;
    public $finalPrice = 0;
    public $insurances = [];
    public $services = [];
    public $redirectBack;
    public $discountInputType = 'percentage';
    public $discountInputValue;
    #[Validate('required', message: 'لطفاً تاریخ شروع مسدودیت را وارد کنید.')]
    #[Validate('regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/', message: 'تاریخ شروع مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.')]
    public $blockedAt;
    #[Validate('required', message: 'لطفاً تاریخ پایان مسدودیت را وارد کنید.')]
    #[Validate('regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/', message: 'تاریخ پایان مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.')]
    #[Validate('after:blockedAt', message: 'تاریخ پایان مسدودیت باید بعد از تاریخ شروع باشد.')]
    public $unblockedAt;
    #[Validate('required|string|max:255', message: 'دلیل مسدودیت نمی‌تواند بیشتر از 255 کاراکتر باشد.')]
    public $blockReason;
    #[Validate('required|string|max:255', message: 'عنوان پیام را وارد کنید.')]
    public $messageTitle;
    #[Validate('required|string|max:1000', message: 'متن پیام را وارد کنید.')]
    public $messageContent;
    #[Validate('required|in:all,blocked,specific', message: 'نوع گیرنده را انتخاب کنید.')]
    public $recipientType;
    public $specificRecipient;
    public $rescheduleAppointmentId;
    public $rescheduleNewDate;
    public $rescheduleAppointmentIds = [];
    public $endVisitAppointmentId = null;
    public $endVisitDescription;
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
        'confirm-search-all-dates' => 'searchAllDates',
        'show-first-available-confirm' => 'showFirstAvailableConfirm',
        'applyDiscount' => 'applyDiscount',
        'get-services' => 'getServices',
    ];
    public $showNoResultsAlert = false;

    public function mount()
    {
        // مقدار پیش‌فرض تاریخ امروز
        $this->selectedDate = Carbon::now()->format('Y-m-d');

        $selectedDateFromUrl = request()->query('selected_date');
        if ($selectedDateFromUrl) {
            $decodedDate = urldecode($selectedDateFromUrl);
            try {
                if (preg_match('/^14\d{2}-\d{2}-\d{2}$/', $decodedDate)) {
                    $this->selectedDate = Jalalian::fromFormat('Y-m-d', $decodedDate)->toCarbon()->format('Y-m-d');
                } elseif (preg_match('/^14\d{2}\/\d{2}\/\d{2}$/', $decodedDate)) {
                    $this->selectedDate = Jalalian::fromFormat('Y/m/d', $decodedDate)->toCarbon()->format('Y-m-d');
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $decodedDate)) {
                    $this->selectedDate = Carbon::parse($decodedDate)->format('Y-m-d');
                } else {
                }
            } catch (\Exception $e) {

            }
        }

        $this->redirectBack = urldecode(request()->query('redirect_back', url()->previous()));
        $this->blockedAt = Jalalian::now()->format('Y-m-d');
        $this->calendarYear = Jalalian::now()->getYear();
        $this->calendarMonth = Jalalian::now()->getMonth();
        $doctor = $this->getAuthenticatedDoctor();
        if ($doctor) {
            $this->loadClinics();
            $this->loadAppointments();
            $this->loadBlockedUsers();
            $this->loadMessages();
            $this->loadCalendarData();
            $this->loadInsurances();
        }
    }

    /**
     * اعتبارسنجی فرمت تاریخ
     */
    private function isValidDate($date)
    {
        try {
            Carbon::parse($date);
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function showNoResultsAlert()
    {
        $this->showNoResultsAlert = true;
    }

    public function hideNoResultsAlert()
    {
        $this->showNoResultsAlert = false;
    }

    public function confirmSearchAllDates()
    {
        // منطق جستجو در همه تاریخ‌ها
        $this->isSearchingAllDates = true;
        $this->showNoResultsAlert = false;
        // به‌روزرسانی لیست نوبت‌ها
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
            $year = $this->calendarYear ?? Jalalian::now()->getYear();
            $month = $this->calendarMonth ?? Jalalian::now()->getMonth();
            $appointmentConfig = DoctorCounselingConfig::where('doctor_id', $doctorId)
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->first();
            $calendarDays = $appointmentConfig ? $appointmentConfig->calendar_days : 30;
            $workSchedules = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
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
            $jalaliDate = Jalalian::fromFormat('Y/m/d', sprintf('%d/%02d/01', $year, $month));
            $startDate = $jalaliDate->toCarbon()->startOfDay();
            $endDate = $jalaliDate->toCarbon()->endOfMonth();
            $jalaliEndDate = Jalalian::fromCarbon($startDate)->addMonths(1)->subDays(1);
            $endDate = $jalaliEndDate->toCarbon()->endOfDay();
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
            $appointmentSettings = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
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

    public function loadAppointments()
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return [];
        }
        $gregorianDate = $this->convertToGregorian($this->selectedDate);
        $query = CounselingAppointment::with(['doctor', 'patient', 'insurance', 'clinic'])
            ->withTrashed()
            ->where('doctor_id', $doctor->id);
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
        $this->dispatch('setAppointments', ['appointments' => $appointmentData]);
        if (empty($this->appointments) && $this->searchQuery) {
            if ($this->isSearchingAllDates) {
                $this->dispatch('no-results-found', ['date' => $jalaliDate->format('Y/m/d'), 'searchAll' => true]);
            } else {
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
        $this->showNoResultsAlert = false;
        $this->loadAppointments();
    }

    private function convertToGregorian($date)
    {
        // دی‌کد کردن تاریخ در صورت URL-encoded بودن
        $decodedDate = urldecode($date);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $decodedDate)) {
            return $decodedDate; // تاریخ میلادی است
        } elseif (preg_match('/^14\d{2}-\d{2}-\d{2}$/', $decodedDate)) {
            try {
                $gregorian = Jalalian::fromFormat('Y-m-d', $decodedDate)->toCarbon()->format('Y-m-d');
                return $gregorian;
            } catch (\Exception $e) {
                Log::error('خطا در تبدیل تاریخ جلالی به میلادی: ' . $e->getMessage() . ' | تاریخ خام: ' . $date . ' | تاریخ دی‌کدشده: ' . $decodedDate);
                $this->dispatch('show-toastr', type: 'error', message: 'خطا در تبدیل تاریخ جلالی به میلادی.');
                return Carbon::now()->format('Y-m-d');
            }
        } elseif (preg_match('/^14\d{2}\/\d{2}\/\d{2}$/', $decodedDate)) {
            try {
                $gregorian = Jalalian::fromFormat('Y/m/d', $decodedDate)->toCarbon()->format('Y-m-d');
                return $gregorian;
            } catch (\Exception $e) {
                Log::error('خطا در تبدیل تاریخ جلالی به میلادی: ' . $e->getMessage() . ' | تاریخ خام: ' . $date . ' | تاریخ دی‌کدشده: ' . $decodedDate);
                $this->dispatch('show-toastr', type: 'error', message: 'خطا در تبدیل تاریخ جلالی به میلادی.');
                return Carbon::now()->format('Y-m-d');
            }
        } else {
            Log::warning('فرمت تاریخ نامعتبر: ' . $decodedDate . ' | تاریخ خام: ' . $date);
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

    public function handleRescheduleAppointment($appointmentIds, $newDate)
    {
        $result = $this->checkRescheduleConditions($newDate, $appointmentIds);
        if (!$result['success']) {
            if (isset($result['partial']) && $result['partial']) {
                // این بخش در confirmPartialReschedule مدیریت می‌شود
            } else {
                $this->dispatch('show-toastr', [
                    'type' => 'error',
                    'message' => $result['message'],
                ]);
            }
            return;
        }
        $this->processReschedule($appointmentIds, $newDate, $result['available_slots']);
        $this->dispatch('show-toastr', [
            'type' => 'success',
            'message' => 'نوبت‌ها با موفقیت جابجا شدند.',
        ]);
        $this->dispatch('appointments-rescheduled', [
            'message' => 'نوبت‌ها با موفقیت جابجا شدند.'
        ]);
        $this->loadAppointments();
        $this->dispatch('close-modal');
        $this->reset(['rescheduleAppointmentIds', 'rescheduleAppointmentId']);

        // فقط اگر selected_date در URL وجود داشته باشد، ریدایرکت انجام شود
        if (request()->query('selected_date') && !empty($this->redirectBack)) {
            return redirect()->to($this->redirectBack);
        }
    }

    private function checkRescheduleConditions($newDate, $appointmentIds)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return ['success' => false, 'message' => 'دکتر معتبر یافت نشد.'];
        }
        $newDateCarbon = Carbon::parse($newDate);
        $today = Carbon::today();
        if ($newDateCarbon->lt($today)) {
            return ['success' => false, 'message' => 'امکان جابجایی به تاریخ گذشته وجود ندارد.'];
        }
        $calendarDays = DoctorCounselingConfig::where('doctor_id', $doctor->id)
            ->value('calendar_days') ?? 30;
        $maxDate = $today->copy()->addDays($calendarDays);
        if ($newDateCarbon->gt($maxDate)) {
            return ['success' => false, 'message' => 'تاریخ مقصد خارج از بازه تقویم مجاز است.'];
        }
        $holidaysQuery = DoctorCounselingHoliday::where('doctor_id', $doctor->id)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);
        if (in_array($newDate, $holidayDates)) {
            return ['success' => false, 'message' => 'تاریخ مقصد تعطیل است.'];
        }
        $dayOfWeek = strtolower($newDateCarbon->format('l'));
        $workScheduleQuery = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $dayOfWeek)
            ->where('is_working', true)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $workSchedule = $workScheduleQuery->first();
        if (!$workSchedule) {
            return ['success' => false, 'message' => 'پزشک در این روز ساعات کاری ندارد.'];
        }
        $specialScheduleQuery = CounselingDailySchedule::where('doctor_id', $doctor->id)
            ->where('date', $newDate)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $specialSchedule = $specialScheduleQuery->first();
        $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : json_decode($workSchedule->work_hours, true);
        $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? ['appointment_duration' => 15];
        $maxAppointments = 0;
        foreach ($workHours as $period) {
            $maxAppointments += $period['max_appointments'] ?? 0;
        }
        $availableSlots = $this->calculateAvailableSlots($workHours, $appointmentSettings, $newDate, $doctor->id);
        $availableSlotsCount = count($availableSlots);
        $requiredSlots = count($appointmentIds);
        if ($availableSlotsCount < $requiredSlots || $maxAppointments < $requiredSlots) {
            $nextAvailableDate = $this->getNextAvailableDateAfter($newDate);
            $remainingAppointments = $requiredSlots - min($availableSlotsCount, $maxAppointments);
            $message = "تعداد نوبت‌های خالی در این روز " . min($availableSlotsCount, $maxAppointments) . " است. آیا مایلید $remainingAppointments نوبت به تاریخ " . Jalalian::fromCarbon(Carbon::parse($nextAvailableDate))->format('Y/m/d') . " منتقل شود؟";
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

    public function calculateAvailableSlots($workHours, $appointmentSettings, $date, $doctorId)
    {
        $slots = [];
        $duration = $appointmentSettings['appointment_duration'] ?? 15;
        $existingAppointments = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->pluck('appointment_time')
            ->map(fn ($time) => $time->format('H:i'))
            ->toArray();
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
                }
                $start->addMinutes($duration);
            }
        }
        return $slots;
    }

    public function confirmPartialReschedule($appointmentIds, $newDate, $nextDate, $availableSlots)
    {
        try {
            if (empty($appointmentIds) || !is_array($appointmentIds)) {
                throw new \Exception('شناسه‌های نوبت نامعتبر است.');
            }
            if (!$newDate || !$nextDate) {
                throw new \Exception('تاریخ‌های انتخاب‌شده نامعتبر هستند.');
            }
            if (empty($availableSlots) || !is_array($availableSlots)) {
                throw new \Exception('زمان‌های کاری خالی نامعتبر هستند.');
            }
            $remainingIds = $this->processReschedule($appointmentIds, $newDate, $availableSlots);
            if (!empty($remainingIds) && $nextDate) {
                $nextDateSlots = $this->getAvailableSlotsForDate($nextDate);
                if (empty($nextDateSlots)) {
                    throw new \Exception('هیچ زمان کاری خالی برای تاریخ بعدی یافت نشد.');
                }
                $remainingRemainingIds = $this->processReschedule($remainingIds, $nextDate, $nextDateSlots);
                if (!empty($remainingRemainingIds)) {
                    $this->dispatch('show-toastr', [
                        'type' => 'warning',
                        'message' => 'برخی نوبت‌ها به دلیل عدم وجود زمان کاری خالی جابجا نشدند.'
                    ]);
                } else {
                    $this->dispatch('show-toastr', [
                        'type' => 'success',
                        'message' => 'نوبت‌های باقی‌مانده با موفقیت به تاریخ ' . Jalalian::fromCarbon(Carbon::parse($nextDate))->format('Y-m-d') . ' منتقل شدند.'
                    ]);
                    $this->dispatch('appointments-rescheduled', [
                        'message' => 'نوبت‌های باقی‌مانده با موفقیت به تاریخ ' . Jalalian::fromCarbon(Carbon::parse($nextDate))->format('Y-m-d') . ' منتقل شدند.'
                    ]);
                }
            }
            $this->dispatch('appointments-rescheduled', [
                'message' => 'نوبت‌ها با موفقیت جابجا شدند.'
            ]);
            $this->loadAppointments();
            $this->dispatch('close-modal');
            $this->reset(['rescheduleAppointmentIds', 'rescheduleAppointmentId']);

            // فقط اگر selected_date در URL وجود داشته باشد، ریدایرکت انجام شود
            if (request()->query('selected_date') && !empty($this->redirectBack)) {
                return redirect()->to($this->redirectBack);
            }
        } catch (\Exception $e) {
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
        $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $dayOfWeek)
            ->where('is_working', true)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->first();
        if (!$workSchedule) {
            return [];
        }
        $specialSchedule = CounselingDailySchedule::where('doctor_id', $doctor->id)
            ->where('date', $date)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->first();
        $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : json_decode($workSchedule->work_hours, true);
        $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? ['appointment_duration' => 15];
        $slots = $this->calculateAvailableSlots($workHours, $appointmentSettings, $date, $doctor->id);
        return $slots;
    }

    private function getNextAvailableDateAfter($startDate)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $today = Carbon::today();
        $calendarDays = DoctorCounselingConfig::where('doctor_id', $doctorId)->value('calendar_days') ?? 30;
        $maxDate = $today->copy()->addDays($calendarDays);
        $holidaysQuery = DoctorCounselingHoliday::where('doctor_id', $doctorId)
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
            $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayOfWeek)
                ->where('is_working', true)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();
            if (!$workSchedule) {
                $currentDate->addDay();
                continue;
            }
            $specialSchedule = CounselingDailySchedule::where('doctor_id', $doctorId)
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
            $appointmentIds = is_array($ids) ? $ids : [$ids];
            if (empty($appointmentIds)) {
                return;
            }
            $newDateCarbon = Carbon::parse($newDate);
            $validator = Validator::make(['newDate' => $newDate], [
                'newDate' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => $validator->errors()->first()]);
                return;
            }
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
            $remainingIds = $this->processReschedule($appointmentIds, $newDate, $conditions['available_slots']);
            if (!empty($remainingIds)) {
                $this->dispatch('show-toastr', [
                    'type' => 'warning',
                    'message' => 'برخی نوبت‌ها به دلیل عدم وجود زمان کاری خالی جابجا نشدند.'
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
            $this->dispatch('close-modal');
            $this->reset(['rescheduleAppointmentIds', 'rescheduleAppointmentId']);
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'خطایی در جابجایی نوبت رخ داد: ' . $e->getMessage()]);
        }
    }

    public function processReschedule($appointmentIds, $newDate, $availableSlots)
    {
        $remainingIds = [];
        $usedSlots = [];
        foreach ($appointmentIds as $appointmentId) {
            $selectedSlot = null;
            foreach ($availableSlots as $slot) {
                if (!in_array($slot, $usedSlots)) {
                    $selectedSlot = $slot;
                    $usedSlots[] = $slot;
                    break;
                }
            }
            if ($selectedSlot) {
                $appointment = CounselingAppointment::find($appointmentId);
                if ($appointment) {
                    $appointment->update([
                        'appointment_date' => $newDate,
                        'appointment_time' => $selectedSlot,
                    ]);
                } else {
                    $remainingIds[] = $appointmentId;
                }
            } else {
                $remainingIds[] = $appointmentId;
            }
        }
        return $remainingIds;
    }

    public function loadInsurances()
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $this->insurances = DoctorService::where('doctor_id', $doctorId)
            ->where('clinic_id', $this->selectedClinicId === 'default' ? null : $this->selectedClinicId)
            ->with('insurance')
            ->distinct('insurance_id')
            ->get()
            ->pluck('insurance')
            ->filter()
            ->values()
            ->toArray();
    }

    public function updatedSelectedInsuranceId()
    {

        $this->loadServices();
        $this->reset(['selectedServiceIds', 'isFree', 'discountPercentage', 'discountAmount', 'finalPrice']);
        $this->dispatch('services-updated');
    }

    public function loadServices()
    {
        if (!$this->selectedInsuranceId) {
            $this->services = [];
            $this->dispatch('services-updated');
            return;
        }
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $query = DoctorService::where('doctor_id', $doctorId)
            ->where('insurance_id', $this->selectedInsuranceId);
        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } else {
            $query->where('clinic_id', $this->selectedClinicId);
        }
        $services = $query->get();

        $this->services = $services->toArray();
        $this->dispatch('services-updated');
    }

    #[On('get-services')]
    public function getServices()
    {
        $this->dispatch('services-received', $this->services);
    }

    public function updatedSelectedServiceIds()
    {
        $this->calculateFinalPrice();
    }

    public function updatedIsFree()
    {
        $this->calculateFinalPrice();
    }

    public function refreshEndVisitModal()
    {
        $this->dispatch('services-updated');
    }

    public function calculateFinalPrice()
    {
        if ($this->isFree) {
            $this->finalPrice = 0;
            $this->discountPercentage = 0;
            $this->discountAmount = 0;
            $this->dispatch('final-price-updated');
            return;
        }

        if (empty($this->selectedServiceIds)) {
            $this->finalPrice = 0;
            $this->discountPercentage = 0;
            $this->discountAmount = 0;
            $this->dispatch('final-price-updated');
            return;
        }

        $basePrice = 0;
        foreach ($this->selectedServiceIds as $serviceId) {
            $service = DoctorService::find($serviceId);
            if ($service) {
                $basePrice += $service->price ?? 0;
            }
        }

        if ($this->discountPercentage > 0) {
            $this->discountAmount = ($basePrice * $this->discountPercentage) / 100;
        } elseif ($this->discountAmount > 0 && $basePrice > 0) {
            $this->discountPercentage = ($this->discountAmount / $basePrice) * 100;
        } else {
            $this->discountPercentage = 0;
            $this->discountAmount = 0;
        }

        $this->finalPrice = max(0, $basePrice - $this->discountAmount);
        $this->dispatch('final-price-updated');
    }

    public function updatedDiscountInputPercentage()
    {
        // تبدیل ورودی به عدد، اگه خالی یا غیرعددی بود 0 می‌ذاریم
        $percentage = is_numeric($this->discountInputPercentage) ? floatval($this->discountInputPercentage) : 0;

        // مطمئن می‌شیم درصد بین 0 تا 100 باشه
        if ($percentage > 100) {
            $percentage = 100;
            $this->discountInputPercentage = 100;
        } elseif ($percentage < 0) {
            $percentage = 0;
            $this->discountInputPercentage = 0;
        }

        $basePrice = $this->getBasePrice();
        $this->discountInputAmount = ($basePrice * $percentage) / 100;
        $this->discountPercentage = $percentage;
        $this->discountAmount = $this->discountInputAmount;
        $this->calculateFinalPrice();
        $this->dispatch('discount-updated');
    }

    public function updatedDiscountInputAmount()
    {
        $basePrice = $this->getBasePrice();
        if ($this->discountInputAmount > $basePrice) {
            $this->discountInputAmount = $basePrice;
        }
        $this->discountInputPercentage = $basePrice > 0 ? ($this->discountInputAmount / $basePrice) * 100 : 0;
        $this->discountPercentage = $this->discountInputPercentage;
        $this->discountAmount = $this->discountInputAmount;
        $this->calculateFinalPrice();
        $this->dispatch('discount-updated');
    }

    private function getBasePrice()
    {
        $basePrice = 0;
        foreach ($this->selectedServiceIds as $serviceId) {
            $service = DoctorService::find($serviceId);
            if ($service) {
                $basePrice += $service->price ?? 0;
            }
        }
        return $basePrice;
    }

    public function applyDiscount()
    {
        $this->calculateFinalPrice();
        $this->dispatch('discount-applied');
        // فقط مودال تخفیف بسته می‌شه
        $this->dispatch('close-modal', ['id' => 'discount-modal']);
    }

    public function endVisit($appointmentId = null)
    {
        $appointmentId = $appointmentId ?? $this->endVisitAppointmentId;

        if (!$appointmentId) {

            $this->dispatch('show-toastr', [
                'type' => 'error',
                'message' => 'شناسه نوبت نامعتبر است.'
            ]);
            return;
        }

        try {
            $this->validate([
                'selectedInsuranceId' => 'required|exists:insurances,id',
                'selectedServiceIds' => 'required|array|min:1',
            ], [
                'selectedInsuranceId.required' => 'لطفاً یک بیمه انتخاب کنید.',
                'selectedInsuranceId.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
                'selectedServiceIds.required' => 'لطفاً حداقل یک خدمت انتخاب کنید.',
                'selectedServiceIds.array' => 'خدمات انتخاب‌شده باید به‌صورت آرایه باشد.',
                'selectedServiceIds.min' => 'لطفاً حداقل یک خدمت انتخاب کنید.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            $this->dispatch('show-toastr', [
                'type' => 'error',
                'message' => $firstError
            ]);
            return;
        }

        $appointment = CounselingAppointment::findOrFail($appointmentId);

        $appointment->update([
            'insurance_id' => $this->selectedInsuranceId,
            'service_ids' => json_encode($this->selectedServiceIds),
            'final_price' => $this->finalPrice,
            'discount_percentage' => $this->discountPercentage,
            'discount_amount' => $this->discountAmount,
            'status' => 'attended',
            'description' => $this->endVisitDescription,
            'payment_status' => $appointment->payment_status === 'pending' ? 'paid' : $appointment->payment_status,
        ]);

        $this->dispatch('close-modal', ['id' => 'end-visit-modal']);
        $this->dispatch('show-toastr', [
            'type' => 'success',
            'message' => 'ویزیت با موفقیت ثبت شد.'
        ]);

        $this->dispatch('visited', [
            'type' => 'success',
            'message' => 'ویزیت با موفقیت ثبت شد.'
        ]);

        $this->loadAppointments();
        $this->reset([
            'selectedInsuranceId',
            'selectedServiceIds',
            'isFree',
            'discountPercentage',
            'discountAmount',
            'finalPrice',
            'endVisitDescription',
            'endVisitAppointmentId'
        ]);
    }

    public function updatedDiscountPercentage()
    {
        if ($this->discountPercentage > 100) {
            $this->discountPercentage = 100;
        }
        $this->calculateFinalPrice();
    }

    public function updatedDiscountAmount()
    {
        $basePrice = 0;
        foreach ($this->selectedServiceIds as $serviceId) {
            $service = DoctorService::find($serviceId);
            if ($service) {
                $basePrice += $service->price ?? 0;
            }
        }
        if ($this->discountAmount > $basePrice) {
            $this->discountAmount = $basePrice;
        }
        $this->calculateFinalPrice();
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
            $appointments = CounselingAppointment::whereIn('id', $normalizedIds)
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
                $normalizedDate = str_replace('/', '-', $date); // تبدیل 1404/02/05 به 1404-02-05
                return Jalalian::fromFormat('Y-m-d', $normalizedDate)->toCarbon();
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

            if ($this->blockAppointmentId && empty($this->selectedMobiles)) {
                $appointment = CounselingAppointment::with('patient')->find($this->blockAppointmentId);


                if (!$appointment) {
                    $this->dispatch('show-toastr', [
                        'type' => 'error',
                        'message' => 'نوبت با شناسه موردنظر یافت نشد.',
                    ]);
                    $this->dispatch('showModal', 'block-user-modal');
                    return;
                }

                if (!$appointment->patient || !$appointment->patient->mobile) {
                    $this->dispatch('show-toastr', [
                        'type' => 'error',
                        'message' => 'کاربر یا شماره موبایل مرتبط با این نوبت یافت نشد.',
                    ]);
                    $this->dispatch('showModal', 'block-user-modal');
                    return;
                }

                $this->selectedMobiles = [$appointment->patient->mobile];
            }

            if (empty($this->selectedMobiles)) {
                $this->dispatch('show-toastr', [
                    'type' => 'error',
                    'message' => 'کاربری برای مسدود کردن انتخاب نشده است.',
                ]);
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
                $this->dispatch('show-toastr', [
                    'type' => 'error',
                    'message' => 'کاربر(ان) انتخاب‌شده قبلاً مسدود شده‌اند.',
                ]);
                $this->dispatch('showModal', 'block-user-modal');
                return;
            }
            if (empty($blockedUsers)) {
                $this->dispatch('show-toastr', [
                    'type' => 'error',
                    'message' => 'کاربری برای مسدود کردن پیدا نشد.',
                ]);
                $this->dispatch('showModal', 'block-user-modal');
                return;
            }

            $this->dispatch('show-toastr', [
                'type' => 'success',
                'message' => 'کاربر(ان) با موفقیت مسدود شدند.',
            ]);
            $this->dispatch('close-modal', ['id' => 'block-user-modal']);
            $this->loadBlockedUsers();
            $this->reset(['blockedAt', 'unblockedAt', 'blockReason', 'blockAppointmentId', 'selectedMobiles']);
        } catch (\Exception $e) {

            $this->dispatch('show-toastr', [
                'type' => 'error',
                'message' => 'خطایی رخ داد: ' . $e->getMessage(),
            ]);
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
        $holidaysQuery = DoctorCounselingHoliday::where('doctor_id', $doctorId)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);
        $today = Carbon::today();
        $calendarDays = DoctorCounselingConfig::where('doctor_id', $doctorId)->value('calendar_days') ?? 30;
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
            $workSchedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
                ->where('day', $dayOfWeek)
                ->where('is_working', true)
                ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                ->first();
            if (!$workSchedule) {
                return false;
            }
            $specialSchedule = CounselingDailySchedule::where('doctor_id', $doctorId)
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
        try {
            $appointmentIds = $this->rescheduleAppointmentIds ?? [];
            if (empty($appointmentIds)) {
                throw new \Exception('هیچ نوبت انتخاب‌شده‌ای یافت نشد.');
            }
            $requiredSlots = count($appointmentIds);
            $doctor = $this->getAuthenticatedDoctor();
            if (!$doctor) {
                throw new \Exception('دکتر معتبر یافت نشد.');
            }
            $today = Carbon::today();
            $calendarDays = DoctorCounselingConfig::where('doctor_id', $doctor->id)
                ->value('calendar_days') ?? 30;
            $maxDate = $today->copy()->addDays($calendarDays);
            $currentDate = $today->copy();
            $firstAvailableDate = null;
            $availableSlots = [];
            $nextAvailableDate = null;
            while ($currentDate <= $maxDate) {
                $result = $this->checkRescheduleConditions($currentDate->toDateString(), $appointmentIds);
                if ($result['success']) {
                    $firstAvailableDate = $currentDate->toDateString();
                    $availableSlots = $result['available_slots'];
                    break;
                } elseif (isset($result['partial']) && $result['partial']) {
                    if (!$firstAvailableDate) {
                        $firstAvailableDate = $currentDate->toDateString();
                        $availableSlots = $result['available_slots'];
                        $nextAvailableDate = $result['next_available_date'];
                    }
                }
                $currentDate->addDay();
            }
            if (!$firstAvailableDate) {
                throw new \Exception('هیچ تاریخ خالی در بازه مجاز یافت نشد.');
            }
            $jalaliDate = Jalalian::fromCarbon(Carbon::parse($firstAvailableDate))->format('Y/m/d');
            $availableSlotsCount = count($availableSlots);
            if ($availableSlotsCount >= $requiredSlots) {
                $message = "اولین تاریخ خالی در {$jalaliDate} با {$availableSlotsCount} زمان کاری خالی یافت شد. آیا می‌خواهید نوبت‌ها به این تاریخ منتقل شوند؟";
                $this->dispatch('show-first-available-confirm', [
                    'message' => $message,
                    'appointmentIds' => $appointmentIds,
                    'newDate' => $firstAvailableDate,
                    'availableSlots' => $availableSlots,
                    'isFullCapacity' => true,
                ]);
            } else {
                $remainingSlots = $requiredSlots - $availableSlotsCount;
                $jalaliNextDate = Jalalian::fromCarbon(Carbon::parse($nextAvailableDate))->format('Y/m/d');
                $message = "اولین تاریخ خالی در {$jalaliDate} فقط {$availableSlotsCount} زمان کاری خالی دارد. آیا می‌خواهید {$availableSlotsCount} نوبت به این تاریخ و {$remainingSlots} نوبت به {$jalaliNextDate} منتقل شوند؟ یا همه نوبت‌ها به اولین تاریخ با ظرفیت کامل منتقل شوند؟";
                $this->dispatch('show-first-available-confirm', [
                    'message' => $message,
                    'appointmentIds' => $appointmentIds,
                    'newDate' => $firstAvailableDate,
                    'nextDate' => $nextAvailableDate,
                    'availableSlots' => $availableSlots,
                    'isFullCapacity' => false,
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', [
                'type' => 'error',
                'message' => 'خطایی رخ داد: ' . $e->getMessage(),
            ]);
        }
    }

    public function getAppointmentsByDateSpecial($date)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $appointments = CounselingAppointment::where('doctor_id', $doctorId)
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
        $appointments = CounselingAppointment::where('doctor_id', $doctorId)
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
                    'count' => CounselingAppointment::where('appointment_date', $appointment->appointment_date)
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
            $holidaysQuery = DoctorCounselingHoliday::where('doctor_id', $doctorId)
                ->where('status', 'active');
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
                return is_array($dates) ? $dates : (is_string($dates) ? [$dates] : []);
            })->flatten()->filter()->map(function ($date) {
                try {
                    if (preg_match('/^14\d{2}[-\/]\d{2}[-\/]\d{2}$/', $date)) {
                        $normalizedDate = str_replace('/', '-', $date); // تبدیل 1404/02/05 به 1404-02-05
                        return Jalalian::fromFormat('Y-m-d', $normalizedDate)->toCarbon()->format('Y-m-d');
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
        $holidayRecordQuery = DoctorCounselingHoliday::where('doctor_id', $doctorId);
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
        $specialScheduleQuery = CounselingDailySchedule::where('date', $date);
        if ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
            $specialScheduleQuery->where('clinic_id', $this->selectedClinicId);
        }
        $specialSchedule = $specialScheduleQuery->first();
        if ($specialSchedule) {
            return json_decode($specialSchedule->work_hours, true);
        }
        $workScheduleQuery = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayOfWeek);
        if ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
            $workScheduleQuery->where('clinic_id', $this->selectedClinicId);
        }
        $workSchedule = $workScheduleQuery->first();
        return $workSchedule ? json_decode($workSchedule->work_hours, true) ?? [] : [];
    }

    public function render()
    {
        return view('livewire.dr.panel.turn.schedule.counseling-appointments-list');
    }
}
