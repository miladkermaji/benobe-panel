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
use Illuminate\Support\Facades\Cache;
use App\Models\DoctorAppointmentConfig;
use Illuminate\Support\Facades\Validator;
use Exception;

class AppointmentsList extends Component
{
    use WithPagination;
    public $isLoadingServices = false;
    public $isLoadingFinalPrice = false;
    public $isLoadingDiscount = false;
    public $isSaving = false;
    public $isLoading = false;
    public $selectedServiceIds = [];
    public $calendarYear;
    public $paymentMethod = 'online';
    public $calendarMonth;
    public $holidaysData = ['status' => true, 'holidays' => []];
    public $appointmentsData = ['status' => true, 'data' => [], 'working_days' => [], 'calendar_days' => 30, 'appointment_settings' => []];
    public $cancelIds = [];
    public $selectedDate;
    public $selectedClinicId = 'default';
    public $searchQuery = '';
    public $manualSearchQuery = '';
    public $filterStatus = '';
    public $attendanceStatus = '';
    public $dateFilter = '';
    public $appointmentTypeFilter = '';
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
        'getAvailableTimesForDate' => 'getAvailableTimesForDate',
        'getAppointmentDetails' => 'getAppointmentDetails',
    ];
    public $showNoResultsAlert = false;
    public $searchResults = [];
    public $isSearching = false;
    public $firstName = '';
    public $lastName = '';
    public $mobile = '';
    public $nationalCode = '';
    public $appointmentDate = '';
    public $appointmentTime = '';
    public $newUser = [
        'firstName' => '',
        'lastName' => '',
        'mobile' => '',
        'nationalCode' => ''
    ];
    public $selectedTime = null;
    public $availableTimes = [];
    public $isTimeSelectionModalOpen = false;
    public $selectedUserId = null;
    public $startDate;
    public $endDate;
    public function mount()
    {
        $this->isLoading = true;
        $this->dispatch('toggle-loading', isLoading: true);
        // مقداردهی اولیه pagination با مقادیر پیش‌فرض
        $this->pagination = [
            'current_page' => 1,
            'per_page' => 100, // مقدار پیش‌فرض مناسب برای تعداد آیتم‌ها در هر صفحه
            'last_page' => 1,
            'total' => 0,
        ];
        // مقدار پیش‌فرض تاریخ امروز
        $this->selectedDate = Carbon::now()->setTimezone('Asia/Tehran')->format('Y-m-d');
        // خواندن selected_date از URL و دی‌کد کردن آن
        $selectedDateFromUrl = request()->query('selected_date');
        if ($selectedDateFromUrl) {
            $decodedDate = urldecode($selectedDateFromUrl); // دی‌کد کردن 1404%2F02%2F05 به 1404/02/05 یا 1404-02-05
            try {
                // بررسی فرمت جلالی با خط تیره (مثل 1404-02-05)
                if (preg_match('/^14\d{2}-\d{2}-\d{2}$/', $decodedDate)) {
                    $this->selectedDate = Jalalian::fromFormat('Y-m-d', $decodedDate)->toCarbon()->format('Y-m-d');
                }
                // بررسی فرمت جلالی با اسلش (مثل 1404/02/05)
                elseif (preg_match('/^14\d{2}\/\d{2}\/\d{2}$/', $decodedDate)) {
                    $this->selectedDate = Jalalian::fromFormat('Y/m/d', $decodedDate)->toCarbon()->format('Y-m-d');
                }
                // بررسی فرمت میلادی (مثل 2025-05-13)
                elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $decodedDate)) {
                    $this->selectedDate = Carbon::parse($decodedDate)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // در صورت خطا، تاریخ امروز استفاده می‌شود
                $this->dispatch('show-toastr', type: 'error', message: 'فرمت تاریخ انتخاب‌شده نامعتبر است.');
            }
        }
        // ذخیره URL بازگشت
        $this->redirectBack = urldecode(request()->query('redirect_back', url()->previous()));
        // تنظیم تاریخ‌های پیش‌فرض برای مسدودیت
        $this->blockedAt = Jalalian::now()->format('Y-m-d');
        $this->calendarYear = Jalalian::now()->getYear();
        $this->calendarMonth = Jalalian::now()->getMonth();
        // لود داده‌های اولیه
        $doctor = $this->getAuthenticatedDoctor();
        $this->selectedClinicId = request()->query('selectedClinicId', session('selectedClinicId', '1'));
        if ($doctor) {
            $cacheKeyPattern = "appointments_doctor_{$doctor->id}_*";
            Cache::forget($cacheKeyPattern);
            $this->loadClinics();
            $this->loadAppointments();
            $this->loadBlockedUsers();
            $this->loadMessages();
            $this->loadCalendarData();
            $this->loadInsurances();
        }
        $this->isLoading = false;
        $this->dispatch('toggle-loading', isLoading: false);
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
            $jalaliDate = Jalalian::fromFormat('Y/m/d', sprintf('%d/%02d/01', $year, $month));
            $startDate = $jalaliDate->toCarbon()->startOfDay();
            $endDate = $jalaliDate->toCarbon()->endOfMonth();
            $jalaliEndDate = Jalalian::fromCarbon($startDate)->addMonths(1)->subDays(1);
            $endDate = $jalaliEndDate->toCarbon()->endOfDay();
            // دریافت روزهای خاص
            $specialSchedules = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where(function ($query) use ($selectedClinicId) {
                    if ($selectedClinicId !== 'default') {
                        $query->where('clinic_id', $selectedClinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->pluck('date')
                ->map(function ($date) {
                    return Carbon::parse($date)->format('Y-m-d');
                })
                ->toArray();
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
                'special_days' => $specialSchedules, // اضافه کردن روزهای خاص
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
        // Only update if the date has actually changed
        if ($this->selectedDate !== $selectedDate) {
            $this->selectedDate = $selectedDate;
            $this->isSearchingAllDates = false;
            $this->filterStatus = '';
            $this->dateFilter = '';
            $this->resetPage();
            $this->appointments = [];
            $this->loadAppointments();
        }
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
    /**
     * لود نوبت‌ها با Lazy Loading
     */
    /**
     * لود نوبت‌ها با Lazy Loading
     */
    public function loadAppointments()
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return;
        }
        $query = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                return $query->where('clinic_id', $this->selectedClinicId);
            });

        if ($this->filterStatus === 'manual') {
            $query->where('appointment_type', 'manual');
        } else {
            if ($this->dateFilter) {
                $today = Carbon::today();
                switch ($this->dateFilter) {
                    case 'all':
                        // Don't apply any date filter
                        break;
                    case 'current_week':
                        $startOfWeek = $today->copy()->startOfWeek(Carbon::SATURDAY);
                        $endOfWeek = $today->copy()->endOfWeek(Carbon::FRIDAY);
                        $query->whereBetween('appointment_date', [$startOfWeek, $endOfWeek]);
                        break;
                    case 'current_month':
                        $startOfMonth = $today->copy()->startOfMonth();
                        $endOfMonth = $today->copy()->endOfMonth();
                        $query->whereBetween('appointment_date', [$startOfMonth, $endOfMonth]);
                        break;
                    case 'current_year':
                        $startOfYear = $today->copy()->startOfYear();
                        $endOfYear = $today->copy()->endOfYear();
                        $query->whereBetween('appointment_date', [$startOfYear, $endOfYear]);
                        break;
                }
            } elseif (!$this->isSearchingAllDates && $this->filterStatus !== 'all') {
                $query->when($this->selectedDate, function ($query) {
                    return $query->whereDate('appointment_date', $this->selectedDate);
                });
            }
        }

        $query->when($this->filterStatus && $this->filterStatus !== 'manual' && $this->filterStatus !== 'all', function ($query) {
            return $query->where('status', $this->filterStatus);
        });

        $query->when($this->searchQuery, function ($query) {
            $query->whereHas('patient', function ($q) {
                $q->where('first_name', 'like', "%{$this->searchQuery}%")
                    ->orWhere('last_name', 'like', "%{$this->searchQuery}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$this->searchQuery}%"])
                    ->orWhere('mobile', 'like', "%{$this->searchQuery}%")
                    ->orWhere('national_code', 'like', "%{$this->searchQuery}%");
            });
        });

        try {
            $this->appointments = [];
            foreach ($query->orderBy('appointment_date', 'desc')
                          ->orderBy('appointment_time', 'desc')
                          ->cursor() as $appointment) {
                $this->appointments[] = $appointment;
            }
        } catch (\Exception $e) {
            $this->appointments = [];
        }

        $this->dispatch('setAppointments', ['appointments' => $this->appointments]);

        if (empty($this->appointments) && $this->searchQuery) {
            $jalaliDate = Jalalian::fromCarbon(Carbon::parse($this->selectedDate));
            if ($this->isSearchingAllDates) {
                $this->dispatch('no-results-found', ['date' => $jalaliDate->format('Y/m/d'), 'searchAll' => true]);
            } else {
                $this->showNoResultsAlert = true;
                $this->dispatch('show-no-results-alert', [
                    'date' => $jalaliDate->format('Y/m/d'),
                    'message' => "نتیجه‌ای برای جستجوی شما در تاریخ {$jalaliDate->format('Y/m/d')} یافت نشد. آیا می‌خواهید در همه سوابق و نوبت‌ها جستجو کنید؟"
                ]);
            }
        } else {
            $this->showNoResultsAlert = false;
            $this->dispatch('hide-no-results-alert');
        }

        return $this->appointments;
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
                $this->dispatch('show-toastr', type: 'error', message: 'خطا در تبدیل تاریخ جلالی به میلادی.');
                return Carbon::now()->format('Y-m-d');
            }
        } elseif (preg_match('/^14\d{2}\/\d{2}\/\d{2}$/', $decodedDate)) {
            try {
                $gregorian = Jalalian::fromFormat('Y/m/d', $decodedDate)->toCarbon()->format('Y-m-d');
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
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            $this->loadAppointments();
            return;
        }

        $this->isSearching = true;
        $this->searchResults = User::where(function ($query) {
            $query->where('first_name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('mobile', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('national_code', 'like', '%' . $this->searchQuery . '%');
        })->limit(10)->get();

        // اعمال جستجو روی لیست اصلی نوبت‌ها
        $this->loadAppointments();
        $this->isSearching = false;
    }
    public function updatedFilterStatus()
    {
        $this->isSearchingAllDates = false;
        $this->dateFilter = '';
        $this->resetPage();
        $this->appointments = [];
        $this->searchQuery = '';
        $this->loadAppointments();
    }
    public function updatedAttendanceStatus()
    {
        $this->isSearchingAllDates = false;
        $this->loadAppointments();
    }
    public function updatedDateFilter()
    {
        $now = Carbon::now();
        // ریست کردن فیلتر نوبت‌های دستی
        $this->filterStatus = '';
        $this->searchQuery = '';
        switch ($this->dateFilter) {
            case 'all':
                $this->selectedDate = null;
                $this->isSearchingAllDates = true;
                break;
            case 'current_year':
                $this->selectedDate = null;
                $this->isSearchingAllDates = true;
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = $now->endOfYear()->format('Y-m-d');
                break;
            case 'current_month':
                $this->selectedDate = null;
                $this->isSearchingAllDates = true;
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'current_week':
                $this->selectedDate = null;
                $this->isSearchingAllDates = true;
                $this->startDate = $now->startOfWeek()->format('Y-m-d');
                $this->endDate = $now->endOfWeek()->format('Y-m-d');
                break;
            default:
                $this->selectedDate = $now->format('Y-m-d');
                $this->isSearchingAllDates = false;
                $this->startDate = null;
                $this->endDate = null;
                break;
        }
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
        try {
            // بررسی اعتبار تاریخ
            $validator = Validator::make(['newDate' => $newDate], [
                'newDate' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }
            // تبدیل تاریخ به میلادی
            $gregorianDate = Carbon::parse($newDate);
            if (!$gregorianDate) {
                throw new \Exception('Invalid date format');
            }
            // بررسی اعتبار تاریخ جدید - فقط اگر تاریخ گذشته باشد خطا بده
            if ($gregorianDate->startOfDay()->lt(Carbon::today())) {
                throw new \Exception('Cannot reschedule to a past date');
            }
            // بررسی برنامه کاری پزشک
            $workSchedule = $this->getWorkSchedule($gregorianDate);
            if (!$workSchedule) {
                throw new \Exception('No work schedule found for the selected date');
            }
            // دریافت نوبت‌های رزرو شده
            $reservedAppointments = $this->getReservedAppointments($gregorianDate);
            // تولید زمان‌های خالی
            $availableTimes = $this->generateAvailableTimes($workSchedule, $reservedAppointments);
            if (empty($availableTimes)) {
                throw new \Exception('هیچ زمان خالی برای جابجایی نوبت وجود ندارد.');
            }
            // استفاده از اولین زمان خالی
            $selectedTime = $availableTimes[0];
            // بررسی شرایط جابجایی
            $conditions = $this->checkRescheduleConditions($newDate, $appointmentIds);
            if (!$conditions['success']) {
                if (isset($conditions['partial']) && $conditions['partial']) {
                    $this->dispatch('show-partial-reschedule-confirm', [
                        'message' => $conditions['message'],
                        'appointmentIds' => $appointmentIds,
                        'newDate' => $newDate,
                        'nextDate' => $conditions['next_available_date'],
                        'availableSlots' => $conditions['available_slots'],
                    ]);
                    return;
                }
                throw new \Exception($conditions['message']);
            }
            // جابجایی نوبت‌ها
            $remainingIds = $this->processReschedule($appointmentIds, $newDate, $availableTimes, $selectedTime);
            if (!empty($remainingIds)) {
                $this->dispatch('show-toastr', [
                    'type' => 'warning',
                    'message' => 'برخی نوبت‌ها به دلیل عدم وجود زمان کاری خالی جابجا نشدند.'
                ]);
            }
            if (count($appointmentIds) - count($remainingIds) > 0) {
                // پاک کردن کش به صورت مستقیم
                $doctor = $this->getAuthenticatedDoctor();
                if ($doctor) {
                    $cacheKeyPattern = "appointments_doctor_{$doctor->id}_*";
                    Cache::forget($cacheKeyPattern);
                }
                $message = "نوبت ها با موفقیت جابجا شدند";
                // ارسال رویداد موفقیت
                $this->dispatch('appointments-rescheduled', [
                    'message' => $message
                ]);
                $this->dispatch('close-modal', ['name' => 'reschedule-modal']);
                $this->dispatch('show-toastr', [
                    'type' => 'success',
                    'message' => $message
                ]);
                // بارگذاری مجدد لیست نوبت‌ها
                $this->loadAppointments();
            } else {
                throw new \Exception('No appointments were updated');
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', [
                'type' => 'error',
                'message' => 'خطا در جابجایی نوبت: ' . $e->getMessage()
            ]);
        }
    }
    private function checkWorkHoursAndSlots($date, $requiredSlots)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $currentTime = Carbon::now();
        $targetDate = Carbon::parse($date);
        // اگه تاریخ مقصد امروز نیست، نیازی به بررسی زمان نیست
        if (!$targetDate->isToday()) {
            return [
                'success' => true,
                'message' => 'تاریخ مقصد معتبر است.',
            ];
        }
        // دریافت برنامه کاری یا برنامه خاص
        $dayOfWeek = strtolower($targetDate->format('l'));
        $workScheduleQuery = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('day', $dayOfWeek)
            ->where('is_working', true)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $workSchedule = $workScheduleQuery->first();
        $specialScheduleQuery = SpecialDailySchedule::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $specialSchedule = $specialScheduleQuery->first();
        if (!$workSchedule && !$specialSchedule) {
            return [
                'success' => false,
                'message' => 'پزشک در این روز ساعات کاری ندارد.',
            ];
        }
        // دریافت work_hours
        $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : ($workSchedule ? json_decode($workSchedule->work_hours, true) : []);
        if (empty($workHours)) {
            return [
                'success' => false,
                'message' => 'هیچ ساعات کاری برای این روز تعریف نشده است.',
            ];
        }
        // محاسبه آخرین زمان پایان
        $latestEndTime = null;
        $totalMaxAppointments = 0;
        foreach ($workHours as $period) {
            $endTime = Carbon::parse($period['end']);
            if (!$latestEndTime || $endTime->gt($latestEndTime)) {
                $latestEndTime = $endTime;
            }
            $totalMaxAppointments += $period['max_appointments'] ?? PHP_INT_MAX;
        }
        // تنظیم تاریخ برای مقایسه صحیح
        $latestEndTime->setDate($targetDate->year, $targetDate->month, $targetDate->day);
        // بررسی اینکه زمان پایان از زمان فعلی بیشتر باشد
        if ($latestEndTime->lte($currentTime)) {
            return [
                'success' => false,
                'message' => 'زمان پایان ساعات کاری این روز گذشته است.',
            ];
        }
        // بررسی تعداد نوبت‌های مجاز
        if ($totalMaxAppointments < $requiredSlots) {
            return [
                'success' => false,
                'message' => "تعداد نوبت‌های مجاز ($totalMaxAppointments) کمتر از تعداد درخواستی ($requiredSlots) است.",
            ];
        }
        return [
            'success' => true,
            'message' => 'ساعات کاری و تعداد نوبت‌ها معتبر است.',
        ];
    }
    private function checkRescheduleConditions($newDate, $appointmentIds)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return ['success' => false, 'canReschedule' => false, 'message' => 'دکتر معتبر یافت نشد.'];
        }
        // بررسی زمان پایان و تعداد نوبت‌ها
        $workHoursCheck = $this->checkWorkHoursAndSlots($newDate, count($appointmentIds));
        if (!$workHoursCheck['success']) {
            return ['success' => false, 'canReschedule' => false, 'message' => $workHoursCheck['message']];
        }
        $newDateCarbon = Carbon::parse($newDate);
        $today = Carbon::today();
        if ($newDateCarbon->lt($today)) {
            return ['success' => false, 'canReschedule' => false, 'message' => 'امکان جابجایی به تاریخ گذشته وجود ندارد.'];
        }
        $calendarDays = DoctorAppointmentConfig::where('doctor_id', $doctor->id)
            ->value('calendar_days') ?? 30;
        $maxDate = $today->copy()->addDays($calendarDays);
        if ($newDateCarbon->gt($maxDate)) {
            return ['success' => false, 'canReschedule' => false, 'message' => 'تاریخ مقصد خارج از بازه تقویم مجاز است.'];
        }
        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctor->id)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);
        if (in_array($newDate, $holidayDates)) {
            return ['success' => false, 'canReschedule' => false, 'message' => 'تاریخ مقصد تعطیل است.'];
        }
        $dayOfWeek = strtolower($newDateCarbon->format('l'));
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $dayOfWeek)
            ->where('is_working', true)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->first();
        if (!$workSchedule) {
            return ['success' => false, 'canReschedule' => false, 'message' => 'برنامه کاری برای این روز تعریف نشده است.'];
        }
        $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctor->id)
            ->where('date', $newDate)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->first();
        $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : json_decode($workSchedule->work_hours, true);
        $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? ['max_appointments' => 10, 'appointment_duration' => 15];
        $availableSlots = $this->calculateAvailableSlots($workHours, $appointmentSettings, $newDate, $doctor->id);
        $availableSlotsCount = count($availableSlots);
        $requiredSlots = count($appointmentIds);
        $maxAppointments = $appointmentSettings['max_appointments'] ?? PHP_INT_MAX;
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
                'canReschedule' => false,
                'message' => $message,
                'available_slots' => $availableSlots,
                'next_available_date' => $nextAvailableDate,
                'partial' => true,
            ];
        }
        return [
            'success' => true,
            'canReschedule' => true,
            'available_slots' => $availableSlots,
        ];
    }
    public function calculateAvailableSlots($workHours, $appointmentSettings, $date, $doctorId)
    {
        $slots = [];
        $existingAppointments = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->pluck('appointment_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();
        $currentTime = Carbon::now('Asia/Tehran');
        $isToday = Carbon::parse($date)->isSameDay($currentTime);
        foreach ($workHours as $period) {
            $start = Carbon::parse($period['start']);
            $end = Carbon::parse($period['end']);
            $maxAppointments = $period['max_appointments'] ?? PHP_INT_MAX;
            // محاسبه فاصله زمانی بین اسلات‌ها
            $totalMinutes = $start->diffInMinutes($end);
            $slotInterval = $maxAppointments > 0 ? max(1, floor($totalMinutes / $maxAppointments)) : 15; // حداقل فاصله 1 دقیقه
            $currentSlot = $start->copy();
            $slotsGenerated = 0;
            while ($currentSlot->lt($end) && $slotsGenerated < $maxAppointments) {
                $slotTime = $currentSlot->format('H:i');
                // رد کردن زمان‌های گذشته برای امروز
                if ($isToday && $slotTime <= $currentTime->format('H:i')) {
                    $currentSlot->addMinutes($slotInterval);
                    continue;
                }
                // بررسی عدم تداخل با نوبت‌های موجود
                if (!in_array($slotTime, $existingAppointments)) {
                    $slots[] = $slotTime;
                    $slotsGenerated++;
                }
                $currentSlot->addMinutes($slotInterval);
            }
        }
        return array_values($slots); // بازگشت آرایه ایندکس‌شده
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
            // بررسی زمان کاری برای تاریخ جدید
            $workHoursCheck = $this->checkWorkHoursAndSlots($newDate, count($appointmentIds));
            if (!$workHoursCheck['success']) {
                throw new \Exception($workHoursCheck['message']);
            }
            // جابجایی نوبت‌های ممکن به تاریخ جدید
            $remainingIds = $this->processReschedule($appointmentIds, $newDate, $availableSlots);
            // جابجایی نوبت‌های باقی‌مانده به تاریخ بعدی
            if (!empty($remainingIds) && $nextDate) {
                $workHoursCheckNext = $this->checkWorkHoursAndSlots($nextDate, count($remainingIds));
                if (!$workHoursCheckNext['success']) {
                    throw new \Exception($workHoursCheckNext['message']);
                }
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
                        'message' => 'نوبت‌های باقی‌مانده با موفقیت به تاریخ ' . Jalalian::fromCarbon(Carbon::parse($nextDate))->format('Y/m/d') . ' منتقل شدند.'
                    ]);
                    $this->dispatch('appointments-rescheduled', [
                        'message' => 'نوبت‌های باقی‌مانده با موفقیت به تاریخ ' . Jalalian::fromCarbon(Carbon::parse($nextDate))->format('Y/m/d') . ' منتقل شدند.'
                    ]);
                }
            }
            // بارگذاری مجدد نوبت‌ها
            $this->loadAppointments();
            $this->dispatch('close-modal');
            $this->reset(['rescheduleAppointmentIds', 'rescheduleAppointmentId']);
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
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctor->id)
            ->where('day', $dayOfWeek)
            ->where('is_working', true)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
            ->first();
        if (!$workSchedule) {
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
            // بررسی زمان پایان و تعداد نوبت‌ها
            $workHoursCheck = $this->checkWorkHoursAndSlots($dateStr, 1); // حداقل یه نوبت نیازه
            if (!$workHoursCheck['success']) {
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
            $appointmentSettings = json_decode($workSchedule->appointment_settings, true) ?? ['appointment_duration' => 15];
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
            $validator = Validator::make(['newDate' => $newDate], [
                'newDate' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => $validator->errors()->first()]);
                return;
            }
            // بررسی زمان پایان و تعداد نوبت‌ها
            $workHoursCheck = $this->checkWorkHoursAndSlots($newDate, count($appointmentIds));
            if (!$workHoursCheck['success']) {
                $this->dispatch('show-toastr', ['type' => 'error', 'message' => $workHoursCheck['message']]);
                $this->dispatch('showModal', 'reschedule-modal');
                return;
            }
            $conditions = $this->checkRescheduleConditions($newDate, $appointmentIds);
            if (!$conditions['success'] || !$conditions['canReschedule']) {
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
            // پاک کردن کش مرتبط با نوبت‌ها
            $doctor = $this->getAuthenticatedDoctor();
            if ($doctor) {
                $cacheKey = "appointments_doctor_{$doctor->id}_clinic_{$this->selectedClinicId}_date_{$this->selectedDate}_datefilter_{$this->dateFilter}_status_{$this->filterStatus}_search_{$this->searchQuery}_page_{$this->pagination['current_page']}";
                Cache::forget($cacheKey);
            }
            $this->loadAppointments();
            $this->dispatch('close-modal');
            $this->reset(['rescheduleAppointmentIds', 'rescheduleAppointmentId']);
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'خطایی در جابجایی نوبت رخ داد: ' . $e->getMessage()]);
        }
    }
    private function processReschedule($appointmentIds, $newDate, $availableSlots, $selectedTime = null)
    {
        try {
            $doctor = $this->getAuthenticatedDoctor();
            if (!$doctor) {
                throw new Exception('دکتر یافت نشد.');
            }
            $remainingIds = [];
            $appointments = Appointment::whereIn('id', $appointmentIds)->get();
            // If we have selected times for multiple appointments
            if (is_array($selectedTime)) {
                foreach ($appointments as $appointment) {
                    $newTime = $selectedTime[$appointment->id] ?? null;
                    if (!$newTime || !in_array($newTime, $availableSlots)) {
                        $remainingIds[] = $appointment->id;
                        continue;
                    }
                    $appointment->appointment_date = $newDate;
                    $appointment->appointment_time = $newTime;
                    $appointment->save();
                }
            } else {
                // Single appointment or no specific times provided
                foreach ($appointments as $appointment) {
                    // If a specific time was selected, use it
                    if ($selectedTime && in_array($selectedTime, $availableSlots)) {
                        $newTime = $selectedTime;
                    } else {
                        // Otherwise, use the first available slot
                        $newTime = $availableSlots[0] ?? null;
                    }
                    if (!$newTime) {
                        $remainingIds[] = $appointment->id;
                        continue;
                    }
                    $appointment->appointment_date = $newDate;
                    $appointment->appointment_time = $newTime;
                    $appointment->save();
                }
            }
            return $remainingIds;
        } catch (Exception $e) {
            throw new Exception('خطا در جابجایی نوبت: ' . $e->getMessage());
        }
    }
    public function updatedSelectedInsuranceId()
    {
        $this->loadServices();
        $this->reset(['selectedServiceIds', 'isFree', 'discountPercentage', 'discountAmount', 'finalPrice']);
        $this->dispatch('services-updated');
    }
    public function loadInsurances()
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $cacheKey = "insurances_doctor_{$doctorId}_clinic_{$this->selectedClinicId}";
        $this->insurances = Cache::remember($cacheKey, now()->addMinutes(20), function () use ($doctorId) {
            return DoctorService::select('insurance_id')
                ->where('doctor_id', $doctorId)
                ->where('clinic_id', $this->selectedClinicId === 'default' ? null : $this->selectedClinicId)
                ->distinct()
                ->with('insurance')
                ->cursor() // استفاده از Lazy Loading با cursor
                ->pluck('insurance')
                ->filter()
                ->unique('id')
                ->values()
                ->toArray();
        });
        $this->dispatch('insurances-updated');
    }
    /**
     * لود خدمات با Lazy Loading
     */
    public function loadServices()
    {
        $this->isLoadingServices = true;
        if (!$this->selectedInsuranceId) {
            $this->services = [];
            $this->isLoadingServices = false;
            $this->dispatch('services-updated');
            return;
        }
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $cacheKey = "services_doctor_{$doctorId}_clinic_{$this->selectedClinicId}_insurance_{$this->selectedInsuranceId}";
        $this->services = Cache::remember($cacheKey, now()->addMinutes(20), function () use ($doctorId) {
            $query = DoctorService::where('doctor_id', $doctorId)
                ->where('insurance_id', $this->selectedInsuranceId);
            if ($this->selectedClinicId === 'default') {
                $query->whereNull('clinic_id');
            } else {
                $query->where('clinic_id', $this->selectedClinicId);
            }
            return $query->cursor()->toArray(); // استفاده از Lazy Loading با cursor
        });
        $this->isLoadingServices = false;
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
        if ($this->isFree) {
            $this->discountPercentage = 0;
            $this->discountAmount = 0;
            $this->finalPrice = 0;
        } else {
            $this->calculateFinalPrice();
        }
        $this->dispatch('final-price-updated');
    }
    public function refreshEndVisitModal()
    {
        $this->dispatch('services-updated');
    }
    public function calculateFinalPrice()
    {
        $this->isLoadingFinalPrice = true; // فعال کردن لودینگ قیمت نهایی
        if ($this->isFree) {
            $this->finalPrice = 0;
            $this->discountPercentage = 0;
            $this->discountAmount = 0;
        } else {
            $basePrice = $this->getBasePrice();
            if (empty($this->selectedServiceIds)) {
                $this->finalPrice = 0;
                $this->discountPercentage = 0;
                $this->discountAmount = 0;
            } else {
                $discountPercentage = is_numeric($this->discountPercentage) ? floatval($this->discountPercentage) : floatval(str_replace('%', '', $this->discountPercentage));
                if ($discountPercentage > 0) {
                    $this->discountAmount = round(($basePrice * $discountPercentage) / 100, 2);
                    $this->discountPercentage = round($discountPercentage, 2);
                } elseif ($this->discountAmount > 0 && $basePrice > 0) {
                    $this->discountPercentage = round(($this->discountAmount / $basePrice) * 100, 2);
                    $this->discountAmount = round($this->discountAmount, 2);
                } else {
                    $this->discountPercentage = 0;
                    $this->discountAmount = 0;
                }
                $this->finalPrice = round(max(0, $basePrice - $this->discountAmount), 0);
            }
        }
        $this->isLoadingFinalPrice = false; // غیرفعال کردن لودینگ قیمت نهایی
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
    public function getBasePrice()
    {
        if (empty($this->selectedServiceIds)) {
            return 0;
        }
        $cacheKey = 'base_price_' . md5(json_encode($this->selectedServiceIds));
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return collect($this->services)
                ->whereIn('id', $this->selectedServiceIds)
                ->sum('price');
        });
    }
    public function applyDiscount()
    {
        $this->isLoadingDiscount = true; // فعال کردن لودینگ تخفیف
        if (!$this->isFree) {
            $this->discountPercentage = is_numeric($this->discountInputPercentage) ? round(floatval($this->discountInputPercentage), 2) : round(floatval(str_replace('%', '', $this->discountInputPercentage)), 2);
            $this->discountAmount = round(floatval($this->discountInputAmount), 2);
            $this->calculateFinalPrice();
        } else {
            $this->finalPrice = 0;
            $this->discountPercentage = 0;
            $this->discountAmount = 0;
        }
        $this->isLoadingDiscount = false; // غیرفعال کردن لودینگ تخفیف
        $this->dispatch('discount-applied', ['percentage' => $this->discountPercentage]);
        $this->dispatch('final-price-updated');
        $this->dispatch('close-modal', ['id' => 'discount-modal']);
    }
    public function endVisit($appointmentId = null)
    {
        $this->isSaving = true; // فعال کردن لودینگ ذخیره
        $appointmentId = $appointmentId ?? $this->endVisitAppointmentId;
        if (!$appointmentId) {
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => 'شناسه نوبت نامعتبر است.']);
            $this->isSaving = false;
            return;
        }
        try {
            $this->validate([
                'selectedInsuranceId' => 'required|exists:insurances,id',
                'selectedServiceIds' => 'required|array|min:1',
                'paymentMethod' => 'required|in:online,cash,card_to_card,pos',
            ], [
                'selectedInsuranceId.required' => 'لطفاً یک بیمه انتخاب کنید.',
                'selectedInsuranceId.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
                'selectedServiceIds.required' => 'لطفاً حداقل یک خدمت انتخاب کنید.',
                'selectedServiceIds.array' => 'خدمات انتخاب‌شده باید به‌صورت آرایه باشد.',
                'selectedServiceIds.min' => 'لطفاً حداقل یک خدمت انتخاب کنید.',
                'paymentMethod.required' => 'لطفاً نوع پرداخت را انتخاب کنید.',
                'paymentMethod.in' => 'نوع پرداخت انتخاب‌شده معتبر نیست.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => $firstError]);
            $this->isSaving = false;
            return;
        }
        $appointment = Appointment::findOrFail($appointmentId);
        $appointment->update([
            'insurance_id' => $this->selectedInsuranceId,
            'service_ids' => json_encode($this->selectedServiceIds),
            'final_price' => $this->finalPrice,
            'discount_percentage' => $this->discountPercentage,
            'discount_amount' => $this->discountAmount,
            'status' => 'attended',
            'description' => $this->endVisitDescription,
            'payment_status' => 'paid',
            'payment_method' => $this->paymentMethod,
        ]);
        $doctor = $this->getAuthenticatedDoctor();
        $cacheKey = "appointments_doctor_{$doctor->id}_clinic_{$this->selectedClinicId}_datefilter_{$this->dateFilter}_status_{$this->filterStatus}_search_{$this->searchQuery}_page_{$this->pagination['current_page']}";
        Cache::forget($cacheKey);
        $cacheKeyPattern = "appointments_doctor_{$doctor->id}_*"; // حذف تمام کش‌های مرتبط با پزشک
        Cache::forget($cacheKeyPattern);
        $this->dispatch('close-modal', ['name' => 'end-visit-modal']);
        $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'ویزیت با موفقیت ثبت شد.']);
        $this->dispatch('visited', ['type' => 'success', 'message' => 'ویزیت با موفقیت ثبت شد.']);
        $this->loadAppointments();
        $this->reset([
            'selectedInsuranceId',
            'selectedServiceIds',
            'isFree',
            'discountPercentage',
            'discountAmount',
            'finalPrice',
            'endVisitDescription',
            'endVisitAppointmentId',
            'paymentMethod',
        ]);
        $this->isSaving = false; // غیرفعال کردن لودینگ ذخیره
        Cache::forget($cacheKey);
        $this->dispatch('refresh-appointments-list');
    }
    public function updatedDiscountPercentage($value)
    {
        // حذف علامت درصد و تبدیل به عدد
        $discountPercentage = is_numeric($value)
            ? floatval($value)
            : floatval(str_replace('%', '', $value));
        // محدود کردن و رند کردن به 2 رقم اعشار
        if ($discountPercentage > 100) {
            $discountPercentage = 100;
        } elseif ($discountPercentage < 0) {
            $discountPercentage = 0;
        } else {
            $discountPercentage = round($discountPercentage, 2);
        }
        $this->discountPercentage = $discountPercentage;
        $this->discountInputPercentage = $discountPercentage;
        $this->calculateFinalPrice();
    }
    public function updatedDiscountAmount($value)
    {
        // تبدیل مقدار ورودی به عدد و رند کردن
        $discountAmount = is_numeric($value) ? floatval($value) : 0;
        $basePrice = $this->getBasePrice();
        if ($discountAmount > $basePrice) {
            $discountAmount = $basePrice;
        } elseif ($discountAmount < 0) {
            $discountAmount = 0;
        }
        $this->discountAmount = round($discountAmount, 2); // رند مبلغ تخفیف
        $this->discountInputAmount = $this->discountAmount;
        // محاسبه و رند کردن درصد
        if ($basePrice > 0) {
            $this->discountPercentage = round(($this->discountAmount / $basePrice) * 100, 2); // رند به 2 رقم اعشار
            $this->discountInputPercentage = $this->discountPercentage;
        } else {
            $this->discountPercentage = 0;
            $this->discountInputPercentage = 0;
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
                $appointment = Appointment::with('patient')->find($this->blockAppointmentId);
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
            $this->dispatch('close-modal', ['name' => 'block-user-modal']);
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
        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)
            ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);
        $today = Carbon::today();
        $currentTime = Carbon::now('Asia/Tehran')->format('H:i');
        $calendarDays = DoctorAppointmentConfig::where('doctor_id', $doctorId)->value('calendar_days') ?? 30;
        $datesToCheck = collect();
        for ($i = 0; $i <= $calendarDays; $i++) {
            $date = $today->copy()->addDays($i)->format('Y-m-d');
            $datesToCheck->push($date);
        }
        $nextAvailableDate = $datesToCheck->first(function ($date) use ($doctorId, $holidayDates, $today, $currentTime) {
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
            $currentTime = Carbon::now('Asia/Tehran');
            $calendarDays = DoctorAppointmentConfig::where('doctor_id', $doctor->id)
                ->value('calendar_days') ?? 30;
            $maxDate = $today->copy()->addDays($calendarDays);
            $currentDate = $today->copy();
            $firstAvailableDate = null;
            $availableSlots = [];
            $nextAvailableDate = null;
            while ($currentDate <= $maxDate) {
                $dateStr = $currentDate->toDateString();
                $isToday = $currentDate->isSameDay($today);
                // بررسی زمان کاری و اسلات‌ها
                $workHoursCheck = $this->checkWorkHoursAndSlots($dateStr, $requiredSlots);
                if (!$workHoursCheck['success']) {
                    $currentDate->addDay();
                    continue;
                }
                $result = $this->checkRescheduleConditions($dateStr, $appointmentIds);
                if ($result['success'] && $result['canReschedule']) {
                    // فیلتر کردن اسلات‌ها برای امروز
                    $availableSlots = $this->filterSlotsForToday($result['available_slots'], $isToday, $currentTime);
                    if (!empty($availableSlots)) {
                        $firstAvailableDate = $dateStr;
                        break;
                    }
                } elseif (isset($result['partial']) && $result['partial']) {
                    if (!$firstAvailableDate) {
                        $availableSlots = $this->filterSlotsForToday($result['available_slots'], $isToday, $currentTime);
                        if (!empty($availableSlots)) {
                            $firstAvailableDate = $dateStr;
                            $nextAvailableDate = $result['next_available_date'];
                        }
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
                $firstAvailableTime = $availableSlots[0] ?? null;
                $timeStr = $firstAvailableTime ? " ساعت {$firstAvailableTime}" : "";
                $message = "اولین تاریخ خالی در {$jalaliDate}{$timeStr} با {$availableSlotsCount} زمان کاری خالی یافت شد. آیا می‌خواهید نوبت‌ها به این تاریخ منتقل شوند؟";
                $this->dispatch('show-first-available-confirm', [
                    'message' => $message,
                    'appointmentIds' => $appointmentIds,
                    'newDate' => $firstAvailableDate,
                    'availableSlots' => $availableSlots,
                    'isFullCapacity' => true,
                    'selectedTime' => $firstAvailableTime
                ]);
            } else {
                $remainingSlots = $requiredSlots - $availableSlotsCount;
                $jalaliNextDate = Jalalian::fromCarbon(Carbon::parse($nextAvailableDate))->format('Y/m/d');
                $firstAvailableTime = $availableSlots[0] ?? null;
                $timeStr = $firstAvailableTime ? " ساعت {$firstAvailableTime}" : "";
                $message = "اولین تاریخ خالی در {$jalaliDate}{$timeStr} فقط {$availableSlotsCount} زمان کاری خالی دارد. آیا می‌خواهید {$availableSlotsCount} نوبت به این تاریخ و {$remainingSlots} نوبت به {$jalaliNextDate} منتقل شوند؟ یا همه نوبت‌ها به اولین تاریخ با ظرفیت کامل منتقل شوند؟";
                $this->dispatch('show-first-available-confirm', [
                    'message' => $message,
                    'appointmentIds' => $appointmentIds,
                    'newDate' => $firstAvailableDate,
                    'nextDate' => $nextAvailableDate,
                    'availableSlots' => $availableSlots,
                    'isFullCapacity' => false,
                    'selectedTime' => $firstAvailableTime
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', [
                'type' => 'error',
                'message' => 'خطایی رخ داد: ' . $e->getMessage(),
            ]);
        }
    }
    /**
     * فیلتر کردن اسلات‌ها برای حذف زمان‌های گذشته در صورت امروز بودن
     */
    private function filterSlotsForToday($slots, $isToday, $currentTime)
    {
        if (!$isToday) {
            return $slots;
        }
        return array_filter($slots, function ($slot) use ($currentTime) {
            return Carbon::parse($slot)->format('H:i') > $currentTime->format('H:i');
        });
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
    public function selectUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $this->firstName = $user->first_name;
            $this->lastName = $user->last_name;
            $this->mobile = $user->mobile;
            $this->nationalCode = $user->national_code;
            $this->manualSearchQuery = '';
            $this->searchResults = [];
            $this->selectedUserId = $userId;
        }
    }
    public function storeNewUser()
    {
        $this->validate([
            'newUser.firstName' => 'required|string|max:255',
            'newUser.lastName' => 'required|string|max:255',
            'newUser.mobile' => 'required|string|size:11|unique:users,mobile',
            'newUser.nationalCode' => 'required|string|size:10|unique:users,national_code',
        ]);
        $user = User::create([
            'first_name' => $this->newUser['firstName'],
            'last_name' => $this->newUser['lastName'],
            'mobile' => $this->newUser['mobile'],
            'national_code' => $this->newUser['nationalCode'],
        ]);
        $this->selectUser($user->id);
        $this->dispatch('close-modal', ['name' => 'add-new-patient-modal']);
        $this->dispatch('show-toast', ['message' => 'بیمار با موفقیت ثبت شد']);
    }
    public function storeWithUser()
    {
        $this->validate([
            'firstName' => 'required',
            'lastName' => 'required',
            'mobile' => 'required',
            'nationalCode' => 'required',
            'appointmentDate' => 'required',
            'appointmentTime' => 'required',
        ]);

        // Check if user already has an appointment on this date
        $existingAppointment = \App\Models\Appointment::whereHas('patient', function ($query) {
            $query->where('national_code', $this->nationalCode);
        })
        ->whereDate('appointment_date', Carbon::parse($this->appointmentDate))
        ->whereNotIn('status', ['cancelled', 'missed', 'attended'])  // Exclude all inactive appointments
        ->first();

        if ($existingAppointment) {
            $this->addError('appointmentDate', 'این بیمار قبلاً برای این تاریخ نوبت ثبت کرده است.');
            return;
        }

        // Continue with the rest of the appointment creation logic
        try {
            // Convert Jalali date to Gregorian
            $gregorianDate = null;
            if (preg_match('/^14\d{2}[-\/]\d{2}[-\/]\d{2}$/', $this->appointmentDate)) {
                try {
                    $normalizedDate = str_replace('/', '-', $this->appointmentDate);
                    $gregorianDate = Jalalian::fromFormat('Y-m-d', $normalizedDate)->toCarbon()->format('Y-m-d');
                } catch (\Exception $e) {
                    $this->dispatch('show-toastr', ['message' => 'فرمت تاریخ نامعتبر است', 'type' => 'error']);
                    return;
                }
            } else {
                $gregorianDate = $this->appointmentDate;
            }
            // Check if user exists
            $user = User::where('mobile', $this->mobile)
                       ->orWhere('national_code', $this->nationalCode)
                       ->first();
            if (!$user) {
                $user = User::create([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'mobile' => $this->mobile,
                    'national_code' => $this->nationalCode,
                ]);
            }
            $doctor = $this->getAuthenticatedDoctor();
            if (!$doctor) {
                throw new \Exception('دکتر معتبر یافت نشد.');
            }
            $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
            // Create the appointment
            $appointment = Appointment::create([
                'patient_id' => $user->id,
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinicId,
                'appointment_date' => $gregorianDate,
                'appointment_time' => $this->appointmentTime,
                'status' => 'scheduled',
                'payment_status' => 'unpaid',
                'appointment_type' => 'manual'
            ]);
            // Clear cache
            $cacheKey = "appointments_doctor_{$doctor->id}_clinic_{$clinicId}_date_{$gregorianDate}";
            Cache::forget($cacheKey);
            // Reset form fields
            $this->reset([
                'firstName',
                'lastName',
                'mobile',
                'nationalCode',
                'appointmentDate',
                'appointmentTime',
                'selectedTime',
                'selectedUserId',
                'searchQuery',
                'searchResults'
            ]);
            // Close modal and show success message
            $this->dispatch('close-modal', ['name' => 'add-sick-modal']);
            $this->dispatch('show-toastr', ['message' => 'نوبت با موفقیت ثبت شد', 'type' => 'success']);
            $this->dispatch('appointment-registered', ['message' => 'نوبت با موفقیت ثبت شد']);
            // Reload appointments list
            $this->loadAppointments();
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', ['message' => 'خطا در ثبت نوبت: ' . $e->getMessage(), 'type' => 'error']);
        }
    }
    public function openTimeSelectionModal()
    {
        if (!$this->appointmentDate) {
            $this->dispatch('show-toastr', ['message' => 'لطفاً ابتدا تاریخ را انتخاب کنید', 'type' => 'error']);
            return;
        }
        $this->isTimeSelectionModalOpen = true;
        $this->loadAvailableTimes();
    }
    private function loadAvailableTimes()
    {
        if (!$this->appointmentDate) {
            $this->availableTimes = [];
            $this->dispatch('available-times-loaded', ['times' => $this->availableTimes]);
            return;
        }
        // Convert Jalali date to Gregorian
        $gregorianDate = $this->convertToGregorian($this->appointmentDate);
        if (!$gregorianDate) {
            $this->availableTimes = [];
            $this->dispatch('available-times-loaded', ['times' => $this->availableTimes]);
            return;
        }
        // Get current time with proper timezone
        $now = Carbon::now('Asia/Tehran');
        $currentTimeStr = $now->format('H:i');
        $selectedDate = Carbon::parse($gregorianDate, 'Asia/Tehran')->startOfDay();
        if ($selectedDate->lt($now->startOfDay())) {
            $this->dispatch('show-toastr', ['message' => 'امکان ثبت نوبت برای تاریخ گذشته وجود ندارد', 'type' => 'error']);
            $this->availableTimes = [];
            $this->dispatch('available-times-loaded', ['times' => $this->availableTimes]);
            return;
        }
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
        // Get doctor's work schedule for this day
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where(function ($query) use ($clinicId) {
                if ($clinicId === null) {
                    $query->whereNull('clinic_id');
                } else {
                    $query->where('clinic_id', $clinicId);
                }
            })
            ->where('day', strtolower(date('l', strtotime($gregorianDate))))
            ->where('is_working', true)
            ->first();
        // Check for special schedule
        $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
            ->where('date', $gregorianDate)
            ->when($clinicId === null, fn ($q) => $q->whereNull('clinic_id'))
            ->when($clinicId !== null, fn ($q) => $q->where('clinic_id', $clinicId))
            ->first();
        $workHours = $specialSchedule ? json_decode($specialSchedule->work_hours, true) : ($workSchedule ? json_decode($workSchedule->work_hours, true) : []);
        $appointmentSettings = $specialSchedule ? (json_decode($specialSchedule->appointment_settings, true) ?? ['appointment_duration' => 15]) : ($workSchedule ? (json_decode($workSchedule->appointment_settings, true) ?? ['appointment_duration' => 15]) : ['appointment_duration' => 15]);
        if (empty($workHours)) {
            $this->availableTimes = [];
            $this->dispatch('available-times-loaded', ['times' => $this->availableTimes]);
            return;
        }
        // Calculate available slots
        $slots = [];
        $existingAppointments = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $gregorianDate)
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->when($clinicId === null, fn ($q) => $q->whereNull('clinic_id'))
            ->when($clinicId !== null, fn ($q) => $q->where('clinic_id', $clinicId))
            ->pluck('appointment_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();
        $currentTime = Carbon::now('Asia/Tehran');
        $isToday = Carbon::parse($gregorianDate)->isSameDay($currentTime);
        foreach ($workHours as $period) {
            $start = Carbon::parse($period['start']);
            $end = Carbon::parse($period['end']);
            $maxAppointments = $period['max_appointments'] ?? PHP_INT_MAX;
            // Calculate slot interval
            $totalMinutes = $start->diffInMinutes($end);
            $slotInterval = $maxAppointments > 0 ? max(1, floor($totalMinutes / $maxAppointments)) : 15;
            $currentSlot = $start->copy();
            $slotsGenerated = 0;
            while ($currentSlot->lt($end) && $slotsGenerated < $maxAppointments) {
                $slotTime = $currentSlot->format('H:i');
                // Skip past times for today only
                if ($isToday && Carbon::parse($slotTime, 'Asia/Tehran')->lte($currentTime)) {
                    $currentSlot->addMinutes($slotInterval);
                    continue;
                }
                // Check for existing appointments
                if (!in_array($slotTime, $existingAppointments)) {
                    $slots[] = $slotTime;
                    $slotsGenerated++;
                }
                $currentSlot->addMinutes($slotInterval);
            }
        }
        $this->availableTimes = array_values($slots);
        $this->dispatch('available-times-loaded', ['times' => $this->availableTimes]);
    }
    public function selectTime($time = null)
    {
        if ($time) {
            $this->selectedTime = $time;
            $this->appointmentTime = $time;
            $this->dispatch('close-modal', ['name' => 'time-selection-modal']);
            $this->isTimeSelectionModalOpen = false;
            return;
        }
        if (!$this->selectedTime) {
            $this->dispatch('show-toastr', ['message' => 'لطفاً یک ساعت را انتخاب کنید', 'type' => 'error']);
            return;
        }
        $this->appointmentTime = $this->selectedTime;
        $this->dispatch('close-modal', ['name' => 'time-selection-modal']);
        $this->isTimeSelectionModalOpen = false;
        $this->selectedTime = null;
    }
    public function updatedAppointmentDate()
    {
        $this->appointmentTime = null;
        $this->selectedTime = null;
    }
    public function updatedAppointmentTypeFilter()
    {
        $this->isSearchingAllDates = false;
        $this->filterStatus = '';
        $this->dateFilter = '';
        $this->resetPage();
        $this->appointments = [];
        $this->loadAppointments();
    }
    #[On('getAvailableTimesForDate')]
    public function getAvailableTimesForDate($date)
    {
        try {
            $workSchedule = $this->getWorkSchedule($date);
            if (!$workSchedule) {
                $this->dispatch('available-times-updated', ['times' => []]);
                return;
            }
            $reservedAppointments = $this->getReservedAppointments($date);
            $availableTimes = $this->generateAvailableTimes($workSchedule, $reservedAppointments);
            // Dispatch the event with the flat array of times
            $this->dispatch('available-times-updated', ['times' => $availableTimes]);
            return $availableTimes;
        } catch (\Exception $e) {
            $this->dispatch('available-times-updated', ['times' => []]);
            return [];
        }
    }
    #[On('rescheduleAppointment')]
    public function rescheduleAppointment($appointmentIds, $newDate, $selectedTime = null, $isMultiple = false)
    {
        try {
            $doctor = $this->getAuthenticatedDoctor();
            if (!$doctor) {
                throw new Exception('دکتر یافت نشد.');
            }
            $conditions = $this->checkRescheduleConditions($newDate, $appointmentIds);
            if (!$conditions['success'] || !$conditions['canReschedule']) {
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
            $availableSlots = $conditions['available_slots'];
            $remainingIds = $this->processReschedule($appointmentIds, $newDate, $availableSlots, $selectedTime);
            if (!empty($remainingIds)) {
                $this->dispatch('show-toastr', [
                    'type' => 'warning',
                    'message' => 'برخی نوبت‌ها به دلیل عدم وجود زمان کاری خالی جابجا نشدند.'
                ]);
            }
            $message = "نوبت ها با موفقیت جابجا شدند";
            $this->dispatch('show-toastr', ['type' => 'success', 'message' => $message]);
            $this->dispatch('close-modal', ['name' => 'reschedule-modal']);
            $this->dispatch('appointment-rescheduled', ['message' => $message]);
            $this->dispatch('refresh-appointments-list');
        } catch (Exception $e) {
            $this->dispatch('show-toastr', ['type' => 'error', 'message' => $e->getMessage()]);
            $this->dispatch('showModal', 'reschedule-modal');
        }
    }
    private function getWorkSchedule($date)
    {
        $doctor = $this->getAuthenticatedDoctor();
        $clinicId = $this->selectedClinicId;
        // First check for special schedule
        $specialSchedule = DB::table('special_daily_schedules')
            ->where('doctor_id', $doctor->id)
            ->where('clinic_id', $clinicId)
            ->where('date', $date)
            ->first();
        if ($specialSchedule) {
            // Fix double-encoded JSON
            $workHours = is_string($specialSchedule->work_hours) ?
                json_decode(json_decode($specialSchedule->work_hours, true), true) :
                json_decode($specialSchedule->work_hours, true);
            return [
                'work_hours' => $workHours,
                'is_special' => true,
                'schedule_date' => $date
            ];
        }
        // If no special schedule, use regular schedule
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l'));
        $regularSchedule = DB::table('doctor_work_schedules')
            ->where('doctor_id', $doctor->id)
            ->where('clinic_id', $clinicId)
            ->where('day', $dayOfWeek)
            ->first();
        if ($regularSchedule) {
            // Fix double-encoded JSON
            $workHours = is_string($regularSchedule->work_hours) ?
                json_decode(json_decode($regularSchedule->work_hours, true), true) :
                json_decode($regularSchedule->work_hours, true);
            return [
                'work_hours' => $workHours,
                'is_special' => false,
                'schedule_date' => $date
            ];
        }
        return null;
    }
    private function getReservedAppointments($date)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return [];
        }
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
        return Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->when($clinicId === null, fn ($q) => $q->whereNull('clinic_id'))
            ->when($clinicId !== null, fn ($q) => $q->where('clinic_id', $clinicId))
            ->get()
            ->map(function ($appointment) {
                return [
                    'appointment_time' => Carbon::parse($appointment->appointment_time)->format('H:i')
                ];
            })
            ->toArray();
    }
    private function generateAvailableTimes($workSchedule, $reservedAppointments)
    {
        $availableTimes = [];
        $now = Carbon::now('Asia/Tehran');
        $currentTime = $now->format('H:i');
        $isToday = $now->format('Y-m-d') === ($workSchedule['schedule_date'] ?? now()->format('Y-m-d'));
        $isSpecial = $workSchedule['is_special'] ?? false;
        // تبدیل زمان‌های رزرو شده به آرایه ساده برای مقایسه راحت‌تر
        $reservedTimes = array_map(function ($appointment) {
            return $appointment['appointment_time'];
        }, $reservedAppointments);
        foreach ($workSchedule['work_hours'] as $period) {
            $start = Carbon::parse($period['start']);
            $end = Carbon::parse($period['end']);
            $maxAppointments = $period['max_appointments'] ?? PHP_INT_MAX;
            // برای برنامه‌های خاص، فاصله 16 دقیقه‌ای استفاده می‌شود
            $interval = $isSpecial ? 16 : 15;
            $current = $start->copy();
            $slotsGenerated = 0;
            while ($current->copy()->addMinutes($interval)->lte($end) && $slotsGenerated < $maxAppointments) {
                $timeSlot = $current->format('H:i');
                // رد کردن زمان‌های گذشته برای امروز
                if ($isToday && Carbon::parse($timeSlot, 'Asia/Tehran')->lte($now)) {
                    $current->addMinutes($interval);
                    continue;
                }
                // اگر این زمان قبلاً رزرو شده است، آن را رد کن
                if (in_array($timeSlot, $reservedTimes)) {
                    $current->addMinutes($interval);
                    continue;
                }
                $availableTimes[] = $timeSlot;
                $slotsGenerated++;
                $current->addMinutes($interval);
            }
        }
        return array_values($availableTimes);
    }
    public function render()
    {
        return view('livewire.dr.panel.turn.schedule.appointments-list');
    }
    #[On('getAppointmentDetails')]
    public function getAppointmentDetails($appointmentId = null, $appointmentIds = null)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return;
        }
        $query = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'deleted')
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId));
        if ($appointmentId) {
            $query->where('id', $appointmentId);
        } elseif ($appointmentIds) {
            $query->whereIn('id', $appointmentIds);
        }
        $appointments = $query->get(['id', 'appointment_date', 'appointment_time'])->map(function ($appointment) {
            // Convert UTC time to local time and format it
            $localTime = Carbon::parse($appointment->appointment_time)->format('H:i');
            $localDate = Carbon::parse($appointment->appointment_date)->format('Y-m-d');
            return [
                'id' => $appointment->id,
                'appointment_date' => $localDate,
                'appointment_time' => $localTime,
            ];
        });
        $this->dispatch('appointment-details-received', $appointments->toArray());
    }
    public function getAppointmentsCount($doctorId, $date)
    {
        try {
            $count = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->where('status', '!=', 'cancelled')
                ->count();
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت تعداد نوبت‌ها'
            ], 500);
        }
    }
    public function getAppointmentsCountWithCache($date)
    {
        try {
            $doctor = $this->getAuthenticatedDoctor();
            if (!$doctor) {
                return 0;
            }
            $cacheKey = "appointments_count_{$doctor->id}_{$date}";
            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($doctor, $date) {
                return Appointment::where('doctor_id', $doctor->id)
                    ->where('appointment_date', $date)
                    ->where('status', '!=', 'cancelled')
                    ->whereNull('deleted_at')
                    ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                    ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                    ->count();
            });
        } catch (\Exception $e) {
            return 0;
        }
    }
    public function getAppointmentsCountForMonth($year, $month)
    {
        try {
            $doctor = $this->getAuthenticatedDoctor();
            if (!$doctor) {
                return [];
            }
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $cacheKey = "appointments_count_month_{$doctor->id}_{$year}_{$month}_{$this->selectedClinicId}";
            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($doctor, $startDate, $endDate) {
                return DB::table('appointments')
                    ->select('appointment_date', DB::raw('count(*) as count'))
                    ->where('doctor_id', $doctor->id)
                    ->whereBetween('appointment_date', [$startDate, $endDate])
                    ->where('status', '!=', 'cancelled')
                    ->whereNull('deleted_at')
                    ->when($this->selectedClinicId === 'default', fn ($q) => $q->whereNull('clinic_id'))
                    ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', fn ($q) => $q->where('clinic_id', $this->selectedClinicId))
                    ->groupBy('appointment_date')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'date' => Carbon::parse($item->appointment_date)->format('Y-m-d'),
                            'count' => (int) $item->count
                        ];
                    })
                    ->toArray();
            });
        } catch (\Exception $e) {
            return [];
        }
    }
    public function updatedManualSearchQuery()
    {
        if (strlen($this->manualSearchQuery) < 2) {
            $this->searchResults = [];
            return;
        }
        $this->isSearching = true;
        $this->searchResults = User::where(function ($query) {
            $query->where('first_name', 'like', '%' . $this->manualSearchQuery . '%')
                  ->orWhere('last_name', 'like', '%' . $this->manualSearchQuery . '%')
                  ->orWhere('mobile', 'like', '%' . $this->manualSearchQuery . '%')
                  ->orWhere('national_code', 'like', '%' . $this->manualSearchQuery . '%');
        })->limit(10)->get();
        $this->isSearching = false;
    }
}
