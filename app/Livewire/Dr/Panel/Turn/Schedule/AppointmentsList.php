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
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class AppointmentsList extends Component
{
    use WithPagination;

    public $selectedDate;
    public $selectedClinicId = 'default';
    public $searchQuery = '';
    public $filterStatus = '';
    public $attendanceStatus = '';
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

    // Properties for blocking users
    #[Validate('required', message: 'لطفاً شماره موبایل را وارد کنید.')]
    #[Validate('exists:users,mobile', message: 'شماره موبایل واردشده در سیستم ثبت نشده است.')]
    public $blockMobile;

    #[Validate('required', message: 'لطفاً تاریخ شروع مسدودیت را وارد کنید.')]
    #[Validate('regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/', message: 'تاریخ شروع مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.')]
    public $blockedAt;

    #[Validate('nullable')]
    #[Validate('regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/', message: 'تاریخ پایان مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.')]
    #[Validate('after:blockedAt', message: 'تاریخ پایان مسدودیت باید بعد از تاریخ شروع باشد.')]
    public $unblockedAt;

    #[Validate('nullable|string|max:255', message: 'دلیل مسدودیت نمی‌تواند بیشتر از 255 کاراکتر باشد.')]
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

    // Properties for ending visit
    public $endVisitAppointmentId;
    public $endVisitDescription;
    protected $listeners = ['updateSelectedDate' => 'updateSelectedDate'];

    public function mount()
    {
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $doctor = $this->getAuthenticatedDoctor();
        if ($doctor) {
            $defaultClinic = $doctor->clinics()->where('is_active', 0)->first();
            $this->selectedClinicId = $defaultClinic ? $defaultClinic->id : null;
        }
        $this->loadClinics();
        $this->loadAppointments();
        $this->loadBlockedUsers();
        $this->loadMessages();
    }

    public function updateSelectedDate($date)
    {
        Log::info('updateSelectedDate called with date:', ['date' => $date]);

        // اگه $date آرایه‌ست، مقدار date رو بگیر
        $selectedDate = is_array($date) && isset($date['date']) ? $date['date'] : $date;
        $this->selectedDate = $selectedDate;
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
            Log::warning('No authenticated doctor found');
            return;
        }

        $gregorianDate = $this->convertToGregorian($this->selectedDate);
        Log::info('loadAppointments called', [
            'selectedDate' => $this->selectedDate,
            'gregorianDate' => $gregorianDate,
            'clinicId' => $this->selectedClinicId,
            'filterStatus' => $this->filterStatus,
            'attendanceStatus' => $this->attendanceStatus,
            'searchQuery' => $this->searchQuery,
        ]);

        $query = Appointment::with(['doctor', 'patient', 'insurance', 'clinic'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $gregorianDate);

        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } elseif ($this->selectedClinicId) {
            $query->where('clinic_id', $this->selectedClinicId);
        }

        if ($this->filterStatus) {
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

        $appointments = $query->paginate(10);
        $this->appointments = $appointments->items();
        $this->pagination = [
            'current_page' => $appointments->currentPage(),
            'last_page' => $appointments->lastPage(),
            'per_page' => $appointments->perPage(),
            'total' => $appointments->total(),
        ];

        Log::info('Appointments loaded', [
            'count' => count($this->appointments),
            'pagination' => $this->pagination,
        ]);
    }

    private function convertToGregorian($date)
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        } elseif (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date)) {
            try {
                $gregorian = Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
                Log::info('Converted Jalali to Gregorian', ['jalali' => $date, 'gregorian' => $gregorian]);
                return $gregorian;
            } catch (\Exception $e) {
                Log::error('Error converting Jalali to Gregorian', ['date' => $date, 'error' => $e->getMessage()]);
                $this->dispatch('alert', type: 'error', message: 'خطا در تبدیل تاریخ جلالی به میلادی.');
                return Carbon::now()->format('Y-m-d');
            }
        } else {
            Log::error('Invalid date format', ['date' => $date]);
            $this->dispatch('alert', type: 'error', message: 'فرمت تاریخ نامعتبر است.');
            return Carbon::now()->format('Y-m-d');
        }
    }

    public function updatedSelectedDate()
    {
        Log::info('updatedSelectedDate triggered', ['selectedDate' => $this->selectedDate]);
        $this->loadAppointments();
        
    }

    public function updatedSelectedClinicId()
    {
        $this->loadAppointments();
        $this->loadBlockedUsers();
    }

    public function updatedSearchQuery()
    {
        $this->loadAppointments();
    }

    public function updatedFilterStatus()
    {
        $this->loadAppointments();
    }

    public function updatedAttendanceStatus()
    {
        $this->loadAppointments();
    }

    public function updateAppointmentDate($id)
    {
        $this->validate([
            'rescheduleNewDate' => 'required|date_format:Y-m-d',
        ]);

        $appointment = Appointment::findOrFail($id);

        if ($appointment->status === 'attended' || $appointment->status === 'cancelled') {
            $this->dispatch('alert', type: 'error', message: 'نمی‌توانید نوبت ویزیت‌شده یا لغو شده را جابجا کنید.');
            return;
        }

        $newDate = Carbon::parse($this->rescheduleNewDate);
        if ($newDate->lt(Carbon::today())) {
            $this->dispatch('alert', type: 'error', message: 'امکان جابجایی به تاریخ گذشته وجود ندارد.');
            return;
        }

        $oldDate = $appointment->appointment_date;
        $appointment->appointment_date = $newDate;
        $appointment->save();

        $oldDateJalali = Jalalian::fromDateTime($oldDate)->format('Y/m/d');
        $newDateJalali = Jalalian::fromDateTime($newDate)->format('Y/m/d');

        if ($appointment->patient && $appointment->patient->mobile) {
            $message = "کاربر گرامی، نوبت شما از تاریخ {$oldDateJalali} به {$newDateJalali} تغییر یافت.";
            SendSmsNotificationJob::dispatch(
                $message,
                [$appointment->patient->mobile],
                null,
                []
            )->delay(now()->addSeconds(5));
        }

        $this->dispatch('alert', type: 'success', message: 'نوبت با موفقیت جابجا شد.');
        $this->loadAppointments();
        $this->dispatch('close-modal', id: 'rescheduleModal');
    }

    public function endVisit($id)
    {
        $this->validate([
            'endVisitDescription' => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->description = $this->endVisitDescription;
        $appointment->status = 'attended';
        $appointment->attendance_status = 'attended';
        $appointment->save();

        $this->dispatch('alert', type: 'success', message: 'ویزیت با موفقیت ثبت شد.');
        $this->loadAppointments();
        $this->dispatch('close-modal', id: 'endVisitModalCenter');
    }

    public function cancelAppointments($ids)
    {
        $appointments = Appointment::whereIn('id', $ids)->get();
        $recipients = [];

        foreach ($appointments as $appointment) {
            if ($appointment->status !== 'cancelled') {
                $appointment->status = 'cancelled';
                $appointment->save();
                if ($appointment->patient && $appointment->patient->mobile) {
                    $recipients[] = $appointment->patient->mobile;
                }
            }
        }

        if (!empty($recipients)) {
            $message = "کاربر گرامی، نوبت شما لغو شده است. برای اطلاعات بیشتر تماس بگیرید.";
            SendSmsNotificationJob::dispatch(
                $message,
                $recipients,
                null,
                []
            )->delay(now()->addSeconds(5));
        }

        $this->dispatch('alert', type: 'success', message: 'نوبت‌ها با موفقیت لغو شدند.');
        $this->loadAppointments();
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

    public function blockUser()
    {
        $this->validate();

        $doctorId = $this->getAuthenticatedDoctor()->id;
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;
        $user = User::where('mobile', $this->blockMobile)->first();

        $blockedAt = $this->processDate($this->blockedAt, 'شروع مسدودیت');
        $unblockedAt = $this->processDate($this->unblockedAt, 'پایان مسدودیت');

        $isBlocked = UserBlocking::where('user_id', $user->id)
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->where('status', 1)
            ->exists();

        if ($isBlocked) {
            $this->dispatch('alert', type: 'error', message: 'این کاربر قبلاً در این کلینیک مسدود شده است.');
            return;
        }

        $blockingUser = UserBlocking::create([
            'user_id' => $user->id,
            'doctor_id' => $doctorId,
            'clinic_id' => $clinicId,
            'blocked_at' => $blockedAt,
            'unblocked_at' => $unblockedAt,
            'reason' => $this->blockReason ?? null,
            'status' => 1,
        ]);

        $doctor = Doctor::find($doctorId);
        $doctorName = $doctor->first_name . ' ' . $doctor->last_name;
        $message = "کاربر گرامی، شما توسط پزشک {$doctorName} در کلینیک انتخابی مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";

        $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
        $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
        $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

        SendSmsNotificationJob::dispatch(
            $message,
            [$user->mobile],
            $templateId,
            [$doctorName]
        )->delay(now()->addSeconds(5));

        $this->dispatch('alert', type: 'success', message: 'کاربر با موفقیت مسدود شد.');
        $this->loadBlockedUsers();
        $this->reset(['blockMobile', 'blockedAt', 'unblockedAt', 'blockReason']);
    }

    public function blockMultipleUsers($mobiles)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;

        $blockedAt = $this->processDate($this->blockedAt, 'شروع مسدودیت');
        $unblockedAt = $this->processDate($this->unblockedAt, 'پایان مسدودیت');

        $blockedUsers = [];
        $alreadyBlocked = [];
        $recipients = [];

        foreach ($mobiles as $mobile) {
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
                'reason' => $this->blockReason ?? null,
                'status' => 1,
            ]);

            $blockedUsers[] = $blockingUser;
            $recipients[] = $mobile;
        }

        if (empty($blockedUsers) && !empty($alreadyBlocked)) {
            $this->dispatch('alert', type: 'error', message: 'کاربران انتخاب‌شده قبلاً مسدود شده‌اند.');
            return;
        }

        if (empty($blockedUsers)) {
            $this->dispatch('alert', type: 'error', message: 'هیچ کاربری برای مسدود کردن پیدا نشد.');
            return;
        }

        if (!empty($recipients)) {
            $doctor = Doctor::find($doctorId);
            $doctorName = $doctor->first_name . ' ' . $doctor->last_name;
            $message = "کاربر گرامی، شما توسط پزشک {$doctorName} در کلینیک انتخابی مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";

            $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
            $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

            SendSmsNotificationJob::dispatch(
                $message,
                $recipients,
                $templateId,
                [$doctorName]
            )->delay(now()->addSeconds(5));
        }

        $this->dispatch('alert', type: 'success', message: 'کاربران با موفقیت مسدود شدند.');
        $this->loadBlockedUsers();
        $this->reset(['blockMobile', 'blockedAt', 'unblockedAt', 'blockReason']);
    }

    public function updateBlockStatus($id, $status)
    {
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;

        $userBlocking = UserBlocking::where('id', $id)
            ->where('clinic_id', $clinicId)
            ->firstOrFail();

        $userBlocking->status = $status;
        $userBlocking->save();

        $user = $userBlocking->user;
        $doctor = $userBlocking->doctor;
        $doctorName = $doctor->first_name . ' ' . $doctor->last_name;

        if ($status == 1) {
            $message = "کاربر گرامی، شما توسط پزشک {$doctorName} در کلینیک انتخابی مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
            $defaultTemplateId = 100254;
        } else {
            $message = "کاربر گرامی، شما توسط پزشک {$doctorName} از حالت مسدودی خارج شدید. اکنون دسترسی شما فعال است.";
            $defaultTemplateId = 100255;
        }

        $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
        $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
        $templateId = ($gatewayName === 'pishgamrayan') ? $defaultTemplateId : null;

        SendSmsNotificationJob::dispatch(
            $message,
            [$user->mobile],
            $templateId,
            [$doctorName]
        )->delay(now()->addSeconds(5));

        SmsTemplate::create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinicId,
            'user_id' => $user->id,
            'identifier' => Str::random(11),
            'title' => $status == 1 ? 'مسدودی کاربر' : 'رفع مسدودی',
            'content' => $message,
        ]);

        $this->dispatch('alert', type: 'success', message: 'وضعیت با موفقیت به‌روزرسانی شد.');
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
                $this->dispatch('alert', type: 'error', message: 'هیچ کاربری با شما نوبت ثبت نکرده است.');
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
                $this->dispatch('alert', type: 'error', message: 'هیچ کاربر مسدودی یافت نشد.');
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

        $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
        $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
        $templateId = ($gatewayName === 'pishgamrayan') ? null : null;

        SendSmsNotificationJob::dispatch(
            $this->messageContent,
            $recipients,
            $templateId,
            [$doctorName]
        )->delay(now()->addSeconds(5));

        $this->dispatch('alert', type: 'success', message: 'پیام با موفقیت در صف ارسال قرار گرفت.');
        $this->loadMessages();
        $this->reset(['messageTitle', 'messageContent', 'recipientType', 'specificRecipient']);
    }

    public function deleteMessage($id)
    {
        $message = SmsTemplate::findOrFail($id);
        $message->delete();

        $this->dispatch('alert', type: 'success', message: 'پیام با موفقیت حذف شد.');
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

        $this->dispatch('alert', type: 'success', message: 'کاربر با موفقیت از لیست مسدودی حذف شد.');
        $this->loadBlockedUsers();
    }

    public function getNextAvailableDate()
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;
        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId)
            ->when($this->selectedClinicId === 'default', function ($query) use ($doctorId) {
                $query->whereNull('clinic_id');
            })
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                $query->where('clinic_id', $this->selectedClinicId);
            });

        $holidays = $holidaysQuery->first();
        $holidayDates = json_decode($holidays->holiday_dates ?? '[]', true);

        $today = Carbon::now()->startOfDay();
        $daysToCheck = DoctorAppointmentConfig::where('doctor_id', $doctorId)->value('calendar_days') ?? 30;

        $datesToCheck = collect();
        for ($i = 1; $i <= $daysToCheck; $i++) {
            $date = $today->copy()->addDays($i)->format('Y-m-d');
            $datesToCheck->push($date);
        }

        $nextAvailableDate = $datesToCheck->first(function ($date) use ($doctorId, $holidayDates) {
            if (in_array($date, $holidayDates)) {
                return false;
            }

            $appointmentQuery = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->when($this->selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                    $query->where('clinic_id', $this->selectedClinicId);
                });

            return !$appointmentQuery->exists();
        });

        return $nextAvailableDate ?? null;
    }

    public function goToFirstAvailableDate()
    {
        $nextAvailableDate = $this->getNextAvailableDate();
        if ($nextAvailableDate) {
            $this->selectedDate = $nextAvailableDate;
            $this->loadAppointments();
            $this->dispatch('update-reschedule-calendar', date: $nextAvailableDate);
        } else {
            $this->dispatch('alert', type: 'error', message: 'هیچ نوبت خالی یافت نشد.');
        }
    }

    public function getAppointmentsByDateSpecial($date)
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->when($this->selectedClinicId === 'default', function ($query) {
                $query->whereNull('clinic_id');
            })
            ->when($this->selectedClinicId && $this->selectedClinicId !== 'default', function ($query) {
                $query->where('clinic_id', $this->selectedClinicId);
            })
            ->get();

        return $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'appointment_date' => $appointment->appointment_date,
                'status' => $appointment->status,
            ];
        })->toArray();
    }

    public function getHolidays()
    {
        $doctorId = $this->getAuthenticatedDoctor()->id;

        $holidaysQuery = DoctorHoliday::where('doctor_id', $doctorId);

        if ($this->selectedClinicId === 'default') {
            $holidaysQuery->whereNull('clinic_id');
        } elseif ($this->selectedClinicId && $this->selectedClinicId !== 'default') {
            $holidaysQuery->where('clinic_id', $this->selectedClinicId);
        }

        $holidays = $holidaysQuery->get()->pluck('holiday_dates')->flatten()->toArray();
        return $holidays;
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

        $this->dispatch('alert', type: 'success', message: $message);
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
