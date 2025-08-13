<?php

namespace App\Livewire\Mc\Panel;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use Livewire\Component;
use App\Models\Appointment;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Traits\HasSelectedDoctor;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use App\Models\SpecialDailySchedule;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorAppointmentConfig;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Validator;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use Livewire\Attributes\On;

class WorkHours extends Component
{
    use HasSelectedDoctor;
    public $showSaveButton = false;
    public $calculationMode = 'count'; // حالت پیش‌فرض: تعداد نوبت‌ها
    public $selectedMedicalCenterId = 'default';
    public $medicalCenterId; // برای صفحه جدید (مثل activation/workhours/{medicalCenter})
    public $activeMedicalCenterId; // پراپرتی مشترک برای کوئری‌ها
    public $appointmentConfig;
    public $workSchedules;
    public $selectedDay;
    public $startTime;
    public $endTime;
    public $slots;
    public $maxAppointments;
    public $isWorking = [];
    public $autoScheduling = false;
    public $calendarDays;
    public $onlineConsultation = false;
    public $holidayAvailability = false;
    public $sourceDay;
    public $targetDays = [];
    public $override = false;
    public $holidayDate;
    public $oldDate;
    public $newDate;
    public $appointmentIds = [];
    public $modalOpen = false;
    public $selectedDays = [];
    public $modalType = '';
    public $modalMessage = '';
    public $holidays = [];
    public $nextAvailableDate;
    public $scheduleModalDay;
    public $scheduleModalIndex;
    public $scheduleSettings = [];
    public $calculator = [
        'day' => null,
        'index' => null,
        'appointment_count' => null,
        'time_per_appointment' => null,
    ];
    public $copySource = [
        'day' => null,
        'index' => null,
    ];
    public $selectedCopyDays = [
        'saturday' => false,
        'sunday' => false,
        'monday' => false,
        'tuesday' => false,
        'wednesday' => false,
        'thursday' => false,
        'friday' => false,
    ];
    public $selectedScheduleDays = [];
    public $selectAllCopyModal = false;
    public $emergencyTimes = [];
    public $emergencyModalDay;
    public $emergencyModalIndex;
    public $isEmergencyModalOpen = false;
    public $selectAllScheduleModal = false;
    public $doctorId;
    public $doctor;
    public $medicalCenter;
    public $selectedDoctorId;
    public $isActivationPage = false;
    public $isEditingSchedule = false;
    public $editingSettingIndex = null;
    public $editingSetting = null;
    public $copySourceDay;
    public $copySourceIndex;
    public $storedCopySource = [];
    public $storedSelectedDays = [];
    protected $rules = [
        'selectedDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        'startTime' => 'required|date_format:H:i',
        'endTime' => 'required|date_format:H:i|after:startTime',
        'maxAppointments' => 'required|integer|min:1',
        'sourceDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        'targetDays' => 'required|array|min:1',
        'override' => 'boolean',
        'holidayDate' => 'required|date',
        'oldDate' => 'required',
        'newDate' => 'required|date_format:Y-m-d',
        'appointmentIds' => 'array',
        'scheduleModalDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
        'scheduleModalIndex' => 'required|integer',
        'selectedScheduleDays' => 'required|array|min:1',
    ];
    protected $messages = [
        'selectedDay.required' => 'لطفاً یک روز را انتخاب کنید.',
        'startTime.required' => 'لطفاً زمان شروع را وارد کنید.',
        'startTime.date_format' => 'فرمت زمان شروع نامعتبر است.',
        'endTime.required' => 'لطفاً زمان پایان را وارد کنید.',
        'endTime.date_format' => 'فرمت زمان پایان نامعتبر است.',
        'endTime.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
        'maxAppointments.required' => 'لطفاً تعداد حداکثر نوبت‌ها را وارد کنید.',
        'maxAppointments.integer' => 'تعداد نوبت‌ها باید عدد باشد.',
        'maxAppointments.min' => 'تعداد نوبت‌ها باید حداقل ۱ باشد.',
        'sourceDay.required' => 'لطفاً روز مبدا را انتخاب کنید.',
        'targetDays.required' => 'لطفاً حداقل یک روز مقصد را انتخاب کنید.',
        'targetDays.min' => 'لطفاً حداقل یک روز مقصد را انتخاب کنید.',
        'holidayDate.required' => 'لطفاً تاریخ تعطیلی را وارد کنید.',
        'oldDate.required' => 'لطفاً تاریخ قدیمی را وارد کنید.',
        'newDate.required' => 'لطفاً تاریخ جدید را وارد کنید.',
        'newDate.date_format' => 'فرمت تاریخ جدید نامعتبر است.',
        'scheduleModalDay.required' => 'لطفاً یک روز را برای زمان‌بندی انتخاب کنید.',
        'scheduleModalIndex.required' => 'اندیس زمان‌بندی نامعتبر است.',
        'selectedScheduleDays.required' => 'لطفاً حداقل یک روز را انتخاب کنید.',
        'selectedScheduleDays.min' => 'لطفاً حداقل یک روز را انتخاب کنید.',
    ];
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
        'openAddSickModal' => 'handleOpenAddSickModal',
        'getAppointmentDetails' => 'getAppointmentDetails',
        'testAvailableTimes' => 'testAvailableTimes',
        'medicalCenterSelected' => 'handleMedicalCenterSelected',
        'doctorSelected' => 'handleDoctorSelected',
    ];
    // --- Manual Appointment Setting Properties ---
    public $manualNobatActive = false;
    public $manualNobatSendLink = 10;
    public $manualNobatConfirmLink = 30;
    public $manualNobatSettingId = null;
    public function mount()
    {
        // دریافت مرکز درمانی احراز هویت شده
        $this->medicalCenter = Auth::guard('medical_center')->user();
        if (!$this->medicalCenter) {
            abort(403, 'دسترسی غیرمجاز');
        }

        $this->activeMedicalCenterId = $this->medicalCenter->id;

        // دریافت پزشک انتخاب‌شده
        $this->selectedDoctorId = $this->getSelectedDoctorId();

        if (!$this->selectedDoctorId) {
            // اگر پزشکی انتخاب نشده، اولین پزشک فعال را انتخاب کن
            try {
                $activeDoctor = \App\Models\Doctor::where('medical_center_id', $this->medicalCenter->id)
                    ->where('doctors.is_active', true)
                    ->first();

                if ($activeDoctor) {
                    $this->selectedDoctorId = $activeDoctor->id;
                    // ذخیره پزشک انتخاب‌شده در دیتابیس
                    \App\Models\MedicalCenterSelectedDoctor::updateOrCreate(
                        ['medical_center_id' => $this->medicalCenter->id],
                        ['doctor_id' => $activeDoctor->id]
                    );
                } else {
                    // اگر هیچ پزشک فعالی وجود ندارد، پیام مناسب نمایش بده
                    session()->flash('warning', 'هیچ پزشک فعالی در این مرکز درمانی وجود ندارد. لطفاً ابتدا یک پزشک اضافه کنید.');
                    return;
                }
            } catch (\Exception $e) {
                session()->flash('error', 'خطا در دریافت اطلاعات پزشکان: ' . $e->getMessage());
                return;
            }
        }

        $this->doctorId = $this->selectedDoctorId;

        // دریافت اطلاعات پزشک
        if ($this->selectedDoctorId) {
            $this->doctor = Doctor::find($this->selectedDoctorId);
        }

        // تنظیمات نوبت‌دهی
        $this->appointmentConfig = DoctorAppointmentConfig::firstOrCreate(
            [
                'doctor_id' => $this->selectedDoctorId,
                'medical_center_id' => $this->activeMedicalCenterId,
            ],
            [
                'auto_scheduling' => false,
                'online_consultation' => false,
                'holiday_availability' => false,
                'calendar_days' => 7,
                'is_active' => false,
                'duration_send_link' => 24,
                'duration_confirm_link' => 2,
            ]
        );

        // به‌روزرسانی مقادیر از دیتابیس
        $this->autoScheduling = (bool) $this->appointmentConfig->auto_scheduling;
        $this->onlineConsultation = (bool) $this->appointmentConfig->online_consultation;
        $this->holidayAvailability = (bool) $this->appointmentConfig->holiday_availability;
        $this->calendarDays = (int) $this->appointmentConfig->calendar_days;
        $this->manualNobatActive = (bool) $this->appointmentConfig->is_active;
        $this->manualNobatSendLink = (int) $this->appointmentConfig->duration_send_link;
        $this->manualNobatConfirmLink = (int) $this->appointmentConfig->duration_confirm_link;
        $this->manualNobatSettingId = $this->appointmentConfig->id;

        // بارگذاری برنامه‌های کاری
        $this->refreshWorkSchedules();

        // تنظیم مقادیر پیش‌فرض
        $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $this->isWorking = array_fill_keys($daysOfWeek, false);
        $this->slots = array_fill_keys($daysOfWeek, []);
        $this->selectedScheduleDays = array_fill_keys($daysOfWeek, false);
        $this->scheduleSettings = array_fill_keys($daysOfWeek, []);

        // پر کردن اطلاعات از دیتابیس
        foreach ($this->workSchedules as $schedule) {
            $day = $schedule['day'];
            $this->isWorking[$day] = (bool) $schedule['is_working'];

            if (isset($schedule['work_hours']) && is_array($schedule['work_hours'])) {
                $this->slots[$day] = [];
                foreach ($schedule['work_hours'] as $index => $slot) {
                    $this->slots[$day][] = [
                        'id' => $schedule['id'] . '-' . $index,
                        'start_time' => $slot['start'] ?? '',
                        'end_time' => $slot['end'] ?? '',
                        'max_appointments' => $slot['max_appointments'] ?? null,
                    ];
                }
            }

            // پر کردن scheduleSettings از appointment_settings
            if (isset($schedule['appointment_settings']) && is_array($schedule['appointment_settings'])) {
                $this->scheduleSettings[$day] = $schedule['appointment_settings'];
            }
        }

        // اگر هیچ زمانی برای روزی تنظیم نشده، یک زمان خالی اضافه کن
        foreach ($daysOfWeek as $day) {
            if (empty($this->slots[$day])) {
                $this->slots[$day][] = [
                    'id' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'max_appointments' => null,
                ];
            }
        }
    }
    /**
     * Resolve medical center ID based on request or session.
     */
    private function resolveMedicalCenterId(): string
    {
        if (request()->is('mc/panel/doctors-clinic/activation/workhours/*')) {
            return request()->route('clinic') ?? 'default';
        }
        return $this->getSelectedMedicalCenterId() ?? 'default';
    }
    public function autoSaveCalendarDays()
    {
        try {
            // اعتبارسنجی
            $this->validate([
                'calendarDays' => 'required|integer|min:1',
            ], [
                'calendarDays.required' => 'تعداد روزهای تقویم الزامی است',
                'calendarDays.integer' => 'تعداد روزهای تقویم باید عدد باشد',
                'calendarDays.min' => 'تعداد روزهای تقویم باید حداقل ۱ باشد',
            ]);

            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            DB::beginTransaction();
            // به‌روزرسانی یا ایجاد تنظیمات
            $this->appointmentConfig = DoctorAppointmentConfig::updateOrCreate(
                [
                    'doctor_id' => $this->selectedDoctorId,
                    'medical_center_id' => $this->activeMedicalCenterId,
                ],
                [
                    'calendar_days' => (int) $this->calendarDays,
                ]
            );
            // به‌روزرسانی پراپرتی محلی
            $this->calendarDays = (int) $this->calendarDays;
            DB::commit();
            $this->showSuccessMessage('تعداد روزهای باز تقویم با موفقیت ذخیره شد');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = implode('، ', $e->validator->errors()->all());
            $this->showErrorMessage($errorMessage ?: 'لطفاً یک عدد معتبر وارد کنید');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorMessage($e->getMessage() ?: 'خطا در ذخیره تعداد روزهای تقویم');
        }
    }
    /**
     * Show success message and dispatch toastr event.
     */
    private function showSuccessMessage(string $message): void
    {
        $this->modalMessage = $message;
        $this->modalType = 'success';
        $this->modalOpen = true;
        $this->dispatch('show-toastr', ['message' => $message, 'type' => 'success']);
    }
    /**
     * Show error message and dispatch toastr event.
     */
    private function showErrorMessage(string $message): void
    {
        $this->modalMessage = $message;
        $this->modalType = 'error';
        $this->modalOpen = true;
        $this->dispatch('show-toastr', ['message' => $message, 'type' => 'error']);
    }
    public function setSelectedClinicId($clinicId)
    {
        try {
            // تنظیم clinicId و activeClinicId
            $this->selectedMedicalCenterId = $clinicId;
            $this->activeMedicalCenterId = $this->medicalCenterId ?? $clinicId;
            // ذخیره clinicId در سشن
            session(['selectedMedicalCenterId' => $clinicId]);
            // بازنشانی پراپرتی‌ها
            $this->reset(['workSchedules', 'isWorking', 'slots']);
            // بارگذاری مجدد داده‌ها
            $this->mount();
            // ارسال رویداد رفرش
            $this->dispatch('refresh-clinic-data');
        } catch (\Exception $e) {
            $this->showErrorMessage('خطا در تنظیم کلینیک: ' . ($e->getMessage() ?: 'خطای ناشناخته'));
        }
    }

    #[On('medicalCenterSelected')]
    public function handleMedicalCenterSelected($data)
    {
        $medicalCenterId = $data['medicalCenterId'] ?? null;

        // بروزرسانی selectedMedicalCenterId
        $this->selectedMedicalCenterId = $medicalCenterId;
        $this->activeMedicalCenterId = $this->medicalCenterId ?? $medicalCenterId;

        // ذخیره در سشن
        session(['selectedMedicalCenterId' => $medicalCenterId]);

        // بازنشانی پراپرتی‌ها
        $this->reset(['workSchedules', 'isWorking', 'slots']);

        // بارگذاری مجدد داده‌ها
        $this->mount();

        // ارسال رویداد رفرش
        $this->dispatch('refresh-clinic-data');
    }

    #[On('doctorSelected')]
    public function handleDoctorSelected($data)
    {
        $doctorId = $data['doctorId'] ?? null;

        // بروزرسانی پزشک انتخاب‌شده
        $this->selectedDoctorId = $doctorId;
        $this->doctorId = $doctorId;

        if ($doctorId) {
            $this->doctor = Doctor::find($doctorId);
        } else {
            $this->doctor = null;
        }

        // بازنشانی پراپرتی‌ها
        $this->reset(['workSchedules', 'isWorking', 'slots']);

        // بارگذاری مجدد داده‌ها
        $this->refreshWorkSchedules();

        // بازسازی آرایه‌های روزهای کاری
        $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $this->isWorking = array_fill_keys($daysOfWeek, false);
        $this->slots = array_fill_keys($daysOfWeek, []);

        // پر کردن ساعت کاری‌ها به صورت بهینه
        foreach ($this->workSchedules as $schedule) {
            $day = $schedule['day'];
            $this->isWorking[$day] = (bool) $schedule['is_working'];
            $workHours = $schedule['work_hours'] ?? [];
            if (!empty($workHours)) {
                $this->slots[$day] = array_map(function ($slot, $index) use ($schedule) {
                    return [
                        'id' => $schedule['id'] . '-' . $index,
                        'start_time' => $slot['start'] ?? null,
                        'end_time' => $slot['end'] ?? null,
                        'max_appointments' => $slot['max_appointments'] ?? null,
                    ];
                }, $workHours, array_keys($workHours));
            }
            if (empty($this->slots[$day])) {
                $this->slots[$day][] = [
                    'id' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'max_appointments' => null,
                ];
            }
        }

        // ارسال رویداد رفرش
        $this->dispatch('refresh-clinic-data');
    }
    public function forceRefreshSettings()
    {
        $this->dispatch('refresh-schedule-settings');
    }
    public function updatedSelectAllScheduleModal($value)
    {
        try {
            // اطمینان از معتبر بودن ورودی
            if (!is_bool($value)) {
                throw new \Exception('مقدار انتخاب همه روزها نامعتبر است');
            }
            // به‌روزرسانی انتخاب همه روزها
            $this->selectedScheduleDays = array_fill_keys(
                ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                (bool) $value
            );
        } catch (\Exception $e) {
            $this->showErrorMessage('خطا در به‌روزرسانی انتخاب روزها: ' . ($e->getMessage() ?: 'خطای ناشناخته'));
        }
    }
    // اضافه کردن پراپرتی‌های جدید
    public function openScheduleModal($day, $index)
    {
        // بررسی وجود پزشک انتخاب‌شده
        if (!$this->selectedDoctorId) {
            $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
            return;
        }

        $this->scheduleModalDay = $day;
        $this->scheduleModalIndex = $index;
        // تنظیم copySourceDay و copySourceIndex برای کپی
        $this->copySourceDay = $day;
        $this->copySourceIndex = $index;
        $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $this->selectedScheduleDays = array_fill_keys($daysOfWeek, false);
        $this->scheduleSettings = [];
        // دریافت برنامه کاری برای روز مبدا
        $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
            ->where('day', $this->scheduleModalDay)
            ->where('medical_center_id', $this->activeMedicalCenterId)
            ->first();
        if ($schedule && $schedule->appointment_settings) {
            $settings = json_decode($schedule->appointment_settings, true) ?? [];
            foreach ($settings as $setting) {
                if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$index) {
                    // ساختار جدید (با کلید day)
                    if (isset($setting['day'])) {
                        $d = $setting['day'];
                        $this->selectedScheduleDays[$d] = true;
                        $this->scheduleSettings[$d][] = [
                            'start_time' => $setting['start_time'],
                            'end_time' => $setting['end_time'],
                        ];
                    }
                    // ساختار قدیمی (با کلید days)
                    if (isset($setting['days']) && is_array($setting['days'])) {
                        foreach ($setting['days'] as $d) {
                            $this->selectedScheduleDays[$d] = true;
                            $this->scheduleSettings[$d][] = [
                                'start_time' => $setting['start_time'],
                                'end_time' => $setting['end_time'],
                            ];
                        }
                    }
                }
            }
        }
        // اگر هیچ تنظیمی برای روز جاری وجود نداشت
        if (empty($this->scheduleSettings[$day])) {
            $this->selectedScheduleDays[$day] = true;
            $this->scheduleSettings[$day][] = [
                'start_time' => null,
                'end_time' => null,
            ];
        }
        $this->refreshWorkSchedules();
        $this->dispatch('refresh-schedule-settings');
    }
    public function saveSchedule($startTime, $endTime)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            // اعتبارسنجی
            $validator = Validator::make([
                'startTime' => $startTime,
                'endTime' => $endTime,
                'selectedScheduleDays' => $this->selectedScheduleDays,
                'scheduleModalDay' => $this->scheduleModalDay,
                'scheduleModalIndex' => $this->scheduleModalIndex,
            ], [
                'scheduleModalDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
                'scheduleModalIndex' => 'required|integer',
                'selectedScheduleDays' => 'required|array|min:1',
                'startTime' => 'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
            ], [
                'scheduleModalDay.required' => 'لطفاً یک روز را برای زمان‌بندی انتخاب کنید.',
                'scheduleModalIndex.required' => 'اندیس زمان‌بندی نامعتبر است.',
                'selectedScheduleDays.required' => 'لطفاً حداقل یک روز انتخاب کنید.',
                'selectedScheduleDays.min' => 'لطفاً حداقل یک روز انتخاب کنید.',
                'startTime.required' => 'لطفاً زمان شروع را وارد کنید.',
                'startTime.date_format' => 'فرمت زمان شروع نامعتبر است.',
                'endTime.required' => 'لطفاً زمان پایان را وارد کنید.',
                'endTime.date_format' => 'فرمت زمان پایان نامعتبر است.',
                'endTime.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
            ]);
            if ($validator->fails()) {
                $this->modalMessage = implode(' ', $validator->errors()->all());
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'error']);
                return;
            }
            $days = array_keys(array_filter($this->selectedScheduleDays));
            $timeToMinutes = function ($time) {
                [$hours, $minutes] = explode(':', $time);
                return (int)$hours * 60 + (int)$minutes;
            };
            $startMinutes = $timeToMinutes($startTime);
            $endMinutes = $timeToMinutes($endTime);
            // بررسی تداخل زمانی
            foreach ($days as $day) {
                $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                    ->where('day', $day)
                    ->where('medical_center_id', $this->activeMedicalCenterId)
                    ->first();
                if ($schedule && $schedule->appointment_settings) {
                    $settings = json_decode($schedule->appointment_settings, true) ?? [];
                    foreach ($settings as $setting) {
                        if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] !== (int)$this->scheduleModalIndex) {
                            if ($this->isTimeConflict($startTime, $endTime, $setting['start_time'], $setting['end_time'])) {
                                throw new \Exception("تداخل زمانی در روز {$this->getPersianDay($day)} در بازه {$setting['start_time']} تا {$setting['end_time']} وجود دارد.");
                            }
                        }
                    }
                }
            }
            // ذخیره تنظیمات برای همه روزهای انتخاب‌شده
            foreach ($days as $day) {
                $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                    ->where('day', $day)
                    ->where('medical_center_id', $this->activeMedicalCenterId)
                    ->first();
                if (!$schedule) {
                    $schedule = DoctorWorkSchedule::create([
                            'doctor_id' => $this->selectedDoctorId,
                            'day' => $day,
                            'medical_center_id' => $this->activeMedicalCenterId,
                        'is_working' => true,
                        'work_hours' => json_encode([]),
                        'appointment_settings' => json_encode([]),
                    ]);
                }
                $appointmentSettings = json_decode($schedule->appointment_settings, true) ?? [];
                // حذف تنظیمات قبلی با همان work_hour_key
                $appointmentSettings = array_filter(
                    $appointmentSettings,
                    fn ($setting) => !(isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->scheduleModalIndex)
                );
                // افزودن تنظیم جدید
                $appointmentSettings[] = [
                    'day' => $day,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'work_hour_key' => (int)$this->scheduleModalIndex,
                ];
                $schedule->update(['appointment_settings' => json_encode(array_values($appointmentSettings))]);
            }
            $this->refreshWorkSchedules();
            $this->closeScheduleModal();
            $this->showSuccessMessage('تنظیمات زمان‌بندی با موفقیت ذخیره شد');
        } catch (\Exception $e) {
            $this->showErrorMessage($e->getMessage());
        }
    }
    public function autoSaveSchedule($day, $index)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            // اعتبارسنجی
            $validator = Validator::make(
                [
                    'start_time' => $this->scheduleSettings[$day][$index]['start_time'] ?? null,
                    'end_time' => $this->scheduleSettings[$day][$index]['end_time'] ?? null,
                ],
                [
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i|after:start_time',
                ],
                [
                    'start_time.required' => 'لطفاً زمان شروع را وارد کنید.',
                    'start_time.date_format' => 'فرمت زمان شروع نامعتبر است.',
                    'end_time.required' => 'لطفاً زمان پایان را وارد کنید.',
                    'end_time.date_format' => 'فرمت زمان پایان نامعتبر است.',
                    'end_time.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
                ]
            );
            if ($validator->fails()) {
                $this->modalMessage = implode(' ', $validator->errors()->all());
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'error']);
                return;
            }
            $newStartTime = $this->scheduleSettings[$day][$index]['start_time'];
            $newEndTime = $this->scheduleSettings[$day][$index]['end_time'];
            $timeToMinutes = function ($time) {
                [$hours, $minutes] = explode(':', $time);
                return (int)$hours * 60 + (int)$minutes;
            };
            $newStartMinutes = $timeToMinutes($newStartTime);
            $newEndMinutes = $timeToMinutes($newEndTime);
            // دریافت یا ایجاد برنامه کاری برای روز مبدا (scheduleModalDay)
            $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                ->where('day', $this->scheduleModalDay)
                ->where('medical_center_id', $this->activeMedicalCenterId)
                ->first();
            if (!$schedule) {
                $schedule = DoctorWorkSchedule::create([
                    'doctor_id' => $this->selectedDoctorId,
                    'day' => $this->scheduleModalDay,
                    'medical_center_id' => $this->activeMedicalCenterId,
                    'is_working' => true,
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                ]);
            }
            $appointmentSettings = json_decode($schedule->appointment_settings, true) ?? [];
            // بررسی تداخل زمانی - راه‌حل جدید
            // ابتدا تنظیمات فعلی را از آرایه حذف کن
            $settingsToCheck = [];
            foreach ($appointmentSettings as $setting) {
                // فقط تنظیمات همان روز را بررسی کن
                $isSameDay = false;
                // ساختار جدید
                if (isset($setting['day']) && $setting['day'] === $day) {
                    $isSameDay = true;
                }
                // ساختار قدیمی
                if (isset($setting['days']) && is_array($setting['days']) && in_array($day, $setting['days'])) {
                    $isSameDay = true;
                }
                // اگر همان روز است، فقط اگر work_hour_key متفاوت است بررسی کن
                if ($isSameDay) {
                    if (!isset($setting['work_hour_key']) || (int)$setting['work_hour_key'] !== (int)$this->scheduleModalIndex) {
                        $settingsToCheck[] = $setting;
                    }
                }
            }
            // حالا فقط تنظیمات غیرفعلی همان روز را برای تداخل بررسی کن
            foreach ($settingsToCheck as $setting) {
                if ($this->isTimeConflict($newStartTime, $newEndTime, $setting['start_time'], $setting['end_time'])) {
                    throw new \Exception("تداخل زمانی در بازه {$setting['start_time']} تا {$setting['end_time']} وجود دارد.");
                }
            }
            // حفظ تنظیمات سایر روزها و به‌روزرسانی فقط روز مشخص
            $updatedSettings = [];
            foreach ($appointmentSettings as $setting) {
                if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->scheduleModalIndex) {
                    // ساختار جدید
                    if (isset($setting['day']) && $setting['day'] !== $day) {
                        $updatedSettings[] = $setting; // حفظ تنظیمات روزهایی که انتخاب نشده‌اند
                    }
                    // ساختار قدیمی
                    if (isset($setting['days']) && is_array($setting['days'])) {
                        $remainingDays = array_diff($setting['days'], [$day]);
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
                    $updatedSettings[] = $setting; // حفظ تنظیمات با work_hour_key متفاوت
                }
            }
            // افزودن تنظیم جدید برای روز مشخص
            $updatedSettings[] = [
                'day' => $day,
                'start_time' => $newStartTime,
                'end_time' => $newEndTime,
                'work_hour_key' => (int)$this->scheduleModalIndex,
            ];
            // به‌روزرسانی دیتابیس
            $schedule->update(['appointment_settings' => json_encode(array_values($updatedSettings))]);
            // به‌روزرسانی آرایه‌های محلی
            $this->scheduleSettings[$day][$index] = [
                'start_time' => $newStartTime,
                'end_time' => $newEndTime,
            ];
            $this->selectedScheduleDays[$day] = true;
            $this->refreshWorkSchedules();
            $this->modalMessage = 'تنظیم زمان‌بندی با موفقیت ذخیره شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'success']);
        } catch (\Exception $e) {
            if (isset($this->scheduleSettings[$day][$index])) {
                $this->scheduleSettings[$day][$index]['start_time'] = null;
                $this->scheduleSettings[$day][$index]['end_time'] = null;
            }
            $this->modalMessage = $e->getMessage();
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'error']);
        }
    }
    public function addScheduleSetting($day)
    {
        // اضافه کردن شرط برای تنظیم activeMedicalCenterId
        if (request()->is('mc/panel/doctors-clinic/activation/workhours/*')) {
            $currentMedicalCenterId = request()->route('medicalCenter') ?? 'default';
            $this->activeMedicalCenterId = $currentMedicalCenterId;
        } else {
            $medicalCenterId = $this->activeMedicalCenterId;
            $this->activeMedicalCenterId = $medicalCenterId ?? 'default';
        }
        if (!isset($this->scheduleSettings[$day])) {
            $this->scheduleSettings[$day] = [];
        }
        // بررسی تداخل بازه‌ها
        $existing = $this->scheduleSettings[$day];
        // اگر بازه‌ای کل روز را پوشش داده (مثلاً 00:00 تا 23:59)، اجازه افزودن نده
        foreach ($existing as $slot) {
            if (
                isset($slot['start_time'], $slot['end_time']) &&
                $slot['start_time'] === '00:00' && $slot['end_time'] === '23:59'
            ) {
                $this->modalMessage = 'شما قبلاً کل روز را رزرو کرده‌اید و امکان افزودن بازه جدید وجود ندارد.';
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'error']);
                return;
            }
        }
        // بررسی تداخل بازه جدید با بازه‌های قبلی (فرض: بازه جدید هنوز مقدار ندارد، پس فقط اگر هیچ بازه‌ای کل روز را نگرفته اجازه بده)
        // اگر بازه جدید مقدار دارد (مثلاً کاربر مقدار وارد کرده)، باید تداخل را بررسی کنیم
        // اما اینجا فقط اجازه اضافه شدن ردیف خالی را می‌دهیم اگر تداخلی با بازه‌های قبلی نباشد
        // پس اگر بازه جدید با بازه‌های قبلی تداخل داشته باشد، ردیف جدید را اضافه نکن
        // (در این مرحله بازه جدید مقدار ندارد، پس فقط بررسی کل روز کافی است)
        $this->scheduleSettings[$day][] = [
            'start_time' => null,
            'end_time' => null,
        ];
        $this->selectedScheduleDays[$day] = true;
        $this->dispatch('refresh-timepicker');
    }
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
    public function deleteScheduleSetting($day, $index)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            // نگاشت روزهای انگلیسی به فارسی
            $dayTranslations = [
                'saturday' => 'شنبه',
                'sunday' => 'یکشنبه',
                'monday' => 'دوشنبه',
                'tuesday' => 'سه‌شنبه',
                'wednesday' => 'چهارشنبه',
                'thursday' => 'پنج‌شنبه',
                'friday' => 'جمعه',
            ];
            // دریافت برنامه کاری برای روز مبدا
            $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                ->where('day', $this->scheduleModalDay)
                ->where('medical_center_id', $this->activeMedicalCenterId)
                ->first();
            if (!$schedule) {
                throw new \Exception('تنظیمات برای این روز یافت نشد');
            }
            $appointmentSettings = json_decode($schedule->appointment_settings, true) ?? [];
            // حذف تنظیم مربوط به روز مشخص (ساختار جدید یا قدیمی)
            $updatedSettings = array_filter(
                $appointmentSettings,
                fn ($setting) => !(isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->scheduleModalIndex && (
                    (isset($setting['day']) && $setting['day'] === $day) ||
                    (isset($setting['days']) && is_array($setting['days']) && in_array($day, $setting['days']))
                ))
            );
            // به‌روزرسانی دیتابیس
            $schedule->update(['appointment_settings' => json_encode(array_values($updatedSettings))]);
            // به‌روزرسانی آرایه‌های محلی
            if (isset($this->scheduleSettings[$day][$index])) {
                unset($this->scheduleSettings[$day][$index]);
                $this->scheduleSettings[$day] = array_values($this->scheduleSettings[$day]);
            }
            if (empty($this->scheduleSettings[$day])) {
                $this->selectedScheduleDays[$day] = false;
                unset($this->scheduleSettings[$day]);
            }
            $this->refreshWorkSchedules();
            $persianDay = $dayTranslations[$day] ?? $day;
            $this->modalMessage = "تنظیمات برای روز $persianDay با موفقیت حذف شد";
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'success']);
            $this->dispatch('day-setting-deleted', ['day' => $day]);
        } catch (\Exception $e) {
            $this->modalMessage = $e->getMessage();
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'error']);
        }
    }
    public function deleteScheduleSettingsForDay($day)
    {
        try {
            // تنظیم activeMedicalCenterId
            if (request()->is('mc/panel/doctors-clinic/activation/workhours/*')) {
                $currentMedicalCenterId = request()->route('medicalCenter') ?? 'default';
                $this->activeMedicalCenterId = $currentMedicalCenterId;
            } else {
                $medicalCenterId = $this->activeMedicalCenterId;
                $this->activeMedicalCenterId = $medicalCenterId ?? 'default';
            }
            // نگاشت روزهای انگلیسی به فارسی
            $dayTranslations = [
                'saturday' => 'شنبه',
                'sunday' => 'یکشنبه',
                'monday' => 'دوشنبه',
                'tuesday' => 'سه‌شنبه',
                'wednesday' => 'چهارشنبه',
                'thursday' => 'پنج‌شنبه',
                'friday' => 'جمعه',
            ];
            // دریافت برنامه کاری برای روز مبدا
            $schedule = DoctorWorkSchedule::where('doctor_id', $this->doctorId)
                ->where('day', $this->scheduleModalDay)
                ->where(function ($query) {
                    if ($this->activeMedicalCenterId !== 'default') {
                        $query->where('medical_center_id', $this->activeMedicalCenterId);
                    } else {
                        $query->whereNull('medical_center_id');
                    }
                })->first();
            if (!$schedule) {
                throw new \Exception('تنظیمات برای این روز یافت نشد');
            }
            $appointmentSettings = json_decode($schedule->appointment_settings, true) ?? [];
            $updatedSettings = [];
            foreach ($appointmentSettings as $setting) {
                if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->scheduleModalIndex) {
                    // ساختار جدید
                    if (isset($setting['day']) && $setting['day'] !== $day) {
                        $updatedSettings[] = $setting;
                    }
                    // ساختار قدیمی
                    if (isset($setting['days']) && is_array($setting['days'])) {
                        $remainingDays = array_diff($setting['days'], [$day]);
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
            // به‌روزرسانی دیتابیس
            $schedule->update(['appointment_settings' => json_encode(array_values($updatedSettings))]);
            // به‌روزرسانی آرایه‌های محلی
            $this->selectedScheduleDays[$day] = false;
            unset($this->scheduleSettings[$day]);
            $this->refreshWorkSchedules();
            $persianDay = $dayTranslations[$day] ?? $day;
            $this->modalMessage = "تنظیمات برای روز $persianDay با موفقیت حذف شد";
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'success']);
            $this->dispatch('day-setting-deleted', ['day' => $day]);
        } catch (\Exception $e) {
            $this->modalMessage = $e->getMessage();
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'error']);
        }
    }
    public function copyScheduleSetting($replace = false)
    {
        // اطمینان از مقداردهی پراپرتی‌های کلیدی
        if (empty($this->copySourceDay) && !empty($this->scheduleModalDay)) {
            $this->copySourceDay = $this->scheduleModalDay;
        }
        if ((is_null($this->copySourceIndex) || $this->copySourceIndex === '') && !is_null($this->scheduleModalIndex)) {
            $this->copySourceIndex = $this->scheduleModalIndex;
        }
        // اگر هنوز مقدار ندارند، از copySource استفاده کن (برای مودال checkbox-modal)
        if (empty($this->copySourceDay) && !empty($this->copySource['day'])) {
            $this->copySourceDay = $this->copySource['day'];
        }
        if ((is_null($this->copySourceIndex) || $this->copySourceIndex === '') && isset($this->copySource['index'])) {
            $this->copySourceIndex = $this->copySource['index'];
        }
        // بررسی وجود مقادیر ضروری
        if (empty($this->copySourceDay) || is_null($this->copySourceIndex) || $this->copySourceIndex === '') {
            Log::error('copySourceDay or copySourceIndex is still empty before copy!', [
                'copySourceDay' => $this->copySourceDay,
                'copySourceIndex' => $this->copySourceIndex,
                'scheduleModalDay' => $this->scheduleModalDay,
                'scheduleModalIndex' => $this->scheduleModalIndex,
                'copySource' => $this->copySource
            ]);
            throw new \Exception('اطلاعات منبع کپی نامعتبر است. لطفاً مجدداً تلاش کنید.');
        }
        try {
            // تنظیم activeMedicalCenterId
            if (request()->is('mc/panel/doctors-clinic/activation/workhours/*')) {
                $currentMedicalCenterId = request()->route('medicalCenter') ?? 'default';
                $this->activeMedicalCenterId = $currentMedicalCenterId;
            } else {
                $medicalCenterId = $this->activeMedicalCenterId;
                $this->activeMedicalCenterId = $medicalCenterId ?? 'default';
            }
            // اعتبارسنجی روزهای انتخاب‌شده - ابتدا از selectedCopyScheduleDays، سپس از selectedDays
            $filteredDays = array_filter($this->selectedCopyScheduleDays);
            // اگر selectedCopyScheduleDays خالی است، از selectedDays استفاده کن
            if (empty($filteredDays)) {
                $filteredDays = array_filter($this->selectedDays);
            }
            if (empty($filteredDays)) {
                throw new \Exception('هیچ روزی برای کپی انتخاب نشده است');
            }
            // دریافت تنظیمات منبع
            $sourceSchedule = collect($this->workSchedules)->firstWhere('day', $this->copySourceDay);
            if (!$sourceSchedule) {
                throw new \Exception('تنظیمات برای روز مبدا یافت نشد');
            }
            $sourceSettings = is_array($sourceSchedule['appointment_settings'])
                ? $sourceSchedule['appointment_settings']
                : json_decode($sourceSchedule['appointment_settings'], true) ?? [];
            // فیلتر کردن تنظیمات منبع برای work_hour_key مشخص
            $filteredSettings = array_values(array_filter(
                $sourceSettings,
                fn ($setting) => isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->copySourceIndex
                    && isset($setting['day']) && $setting['day'] === $this->copySourceDay
            ));
            if (empty($filteredSettings)) {
                throw new \Exception('تنظیم انتخاب‌شده برای کپی یافت نشد');
            }
            $sourceSetting = $filteredSettings[0];
            $selectedTargetDays = array_keys($filteredDays);
            $conflicts = [];
            // پیدا کردن schedule برای مودال جاری (بر اساس copySourceDay)
            $currentSchedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                ->where('day', $this->copySourceDay)
                ->where(function ($query) {
                    if ($this->activeMedicalCenterId !== 'default') {
                        $query->where('medical_center_id', $this->activeMedicalCenterId);
                    } else {
                        $query->whereNull('medical_center_id');
                    }
                })->first();
            if (!$currentSchedule) {
                throw new \Exception('رکورد زمان‌بندی برای روز مبدا یافت نشد');
            }
            $currentSettings = is_array($currentSchedule->appointment_settings)
                ? $currentSchedule->appointment_settings
                : json_decode($currentSchedule->appointment_settings, true) ?? [];
            DB::beginTransaction();
            // اگر replace = false، بررسی تداخل انجام شود
            if (!$replace) {
                foreach ($selectedTargetDays as $targetDay) {
                    if ($targetDay === $this->copySourceDay) {
                        continue;
                    }
                    // بررسی تداخل فقط در appointment_settings مودال جاری
                    foreach ($currentSettings as $setting) {
                        if (isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] !== (int)$this->copySourceIndex
                            && isset($setting['day']) && $setting['day'] === $targetDay) {
                            if ($this->isTimeConflict(
                                $sourceSetting['start_time'],
                                $sourceSetting['end_time'],
                                $setting['start_time'],
                                $setting['end_time']
                            )) {
                                $conflicts[$targetDay][] = [
                                    'start_time' => $setting['start_time'],
                                    'end_time' => $setting['end_time'],
                                ];
                            }
                        }
                    }
                }
                if (!empty($conflicts)) {
                    $this->dispatch('show-conflict-alert', ['conflicts' => $conflicts]);
                    DB::rollBack();
                    return;
                }
            }
            // به‌روزرسانی تنظیمات فقط در مودال جاری (رکورد روز منبع)
            $updatedSettings = array_filter(
                $currentSettings,
                fn ($setting) => !(isset($setting['work_hour_key']) && (int)$setting['work_hour_key'] === (int)$this->copySourceIndex
                    && isset($setting['day']) && in_array($setting['day'], $selectedTargetDays))
            );
            foreach ($selectedTargetDays as $targetDay) {
                if ($targetDay === $this->copySourceDay) {
                    continue;
                }
                $updatedSettings[] = [
                    'day' => $targetDay,
                    'start_time' => $sourceSetting['start_time'],
                    'end_time' => $sourceSetting['end_time'],
                    'work_hour_key' => (int)$this->copySourceIndex,
                ];
            }
            // ذخیره در دیتابیس فقط برای روز منبع
            $currentSchedule->update(['appointment_settings' => json_encode(array_values($updatedSettings))]);
            $this->selectedScheduleDays[$this->copySourceDay] = true;
            // ابتدا workSchedules را بروزرسانی کن
            $this->refreshWorkSchedules();
            // بازسازی آرایه scheduleSettings برای همه روزهای انتخاب‌شده
            $this->scheduleSettings = [];
            foreach ($this->workSchedules as $schedule) {
                $day = $schedule['day'];
                if ($day !== $this->copySourceDay) {
                    continue; // فقط تنظیمات روز منبع بازسازی می‌شوند
                }
                $appointmentSettings = $schedule['appointment_settings'] ?? [];
                if (!empty($appointmentSettings)) {
                    foreach ($appointmentSettings as $setting) {
                        if (
                            isset($setting['work_hour_key']) &&
                            (int)$setting['work_hour_key'] === (int)$this->copySourceIndex &&
                            isset($setting['day'])
                        ) {
                            $targetDay = $setting['day'];
                            $this->scheduleSettings[$targetDay][] = [
                                'start_time' => $setting['start_time'] ?? null,
                                'end_time' => $setting['end_time'] ?? null,
                            ];
                            $this->selectedScheduleDays[$targetDay] = true; // فعال کردن روز هدف در UI
                        }
                    }
                }
            }
            $this->selectAllCopyScheduleModal = false;
            $this->modalMessage = $replace ? 'تنظیمات با موفقیت جایگزین شد' : 'تنظیمات با موفقیت کپی شد';
            $this->modalType = 'success';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'success']);

            // تاخیر کوتاه قبل از بستن مودال
            $this->dispatch('close-modal', ['name' => 'copy-schedule-modal']);
            $this->dispatch('refresh-schedule-settings'); // رفرش UI مودال اسکجول
            $this->dispatch('refresh-work-hours'); // رفرش کامل UI
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Copy schedule setting error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->modalMessage = $e->getMessage() ?: 'خطا در کپی تنظیمات';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', ['message' => $this->modalMessage, 'type' => 'error']);
        }
    }
    public function updatedSelectAllCopyScheduleModal($value)
    {
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            if ($day !== $this->copySourceDay) {
                $this->selectedCopyScheduleDays[$day] = $value;
            }
        }
    }
    public function refreshWorkSchedules()
    {
        // بررسی وجود پزشک انتخاب‌شده
        if (!$this->selectedDoctorId) {
            $this->workSchedules = [];
            return;
        }

        $this->workSchedules = DoctorWorkSchedule::withoutGlobalScopes()
            ->select(['id', 'day', 'is_working', 'work_hours', 'appointment_settings', 'emergency_times'])
            ->where('doctor_id', $this->selectedDoctorId)
            ->where('medical_center_id', $this->activeMedicalCenterId)
            ->get()
            ->map(function ($schedule) {
                $workHours = $schedule->work_hours;
                $appointmentSettings = $schedule->appointment_settings;
                $emergencyTimes = $schedule->emergency_times;
                // اطمینان از اینکه داده‌ها به درستی decode می‌شوند
                if (is_string($workHours)) {
                    $workHours = json_decode($workHours, true) ?? [];
                } elseif (!is_array($workHours)) {
                    $workHours = [];
                }
                if (is_string($appointmentSettings)) {
                    $appointmentSettings = json_decode($appointmentSettings, true) ?? [];
                } elseif (!is_array($appointmentSettings)) {
                    $appointmentSettings = [];
                }
                if (is_string($emergencyTimes)) {
                    $emergencyTimes = json_decode($emergencyTimes, true) ?? [];
                } elseif (!is_array($emergencyTimes)) {
                    $emergencyTimes = [];
                }
                return [
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'is_working' => (bool) $schedule->is_working,
                    'work_hours' => $workHours,
                    'appointment_settings' => $appointmentSettings,
                    'emergency_times' => $emergencyTimes,
                ];
            })
            ->toArray();
        // بازسازی آرایه slots برای نمایش صحیح در UI
        $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $this->slots = array_fill_keys($daysOfWeek, []);
        foreach ($this->workSchedules as $schedule) {
            $day = $schedule['day'];
            $this->isWorking[$day] = (bool) $schedule['is_working'];
            $workHours = $schedule['work_hours'] ?? [];
            if (!empty($workHours)) {
                $this->slots[$day] = array_map(function ($slot, $index) use ($schedule) {
                    return [
                        'id' => $schedule['id'] . '-' . $index,
                        'start_time' => $slot['start'] ?? null,
                        'end_time' => $slot['end'] ?? null,
                        'max_appointments' => $slot['max_appointments'] ?? null,
                    ];
                }, $workHours, array_keys($workHours));
            }
            if (empty($this->slots[$day])) {
                $this->slots[$day][] = [
                    'id' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'max_appointments' => null,
                ];
            }
        }
    }
    public function closeScheduleModal()
    {
        $this->reset(['scheduleModalDay', 'scheduleModalIndex', 'scheduleSettings']);
        $this->dispatch('close-schedule-modal');
    }
    public function updatedSelectAllCopyModal($value)
    {
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            if ($day !== $this->sourceDay) {
                $this->selectedDays[$day] = $value;
            }
        }
    }
    public function saveEmergencyTimes()
    {
        // بررسی وجود پزشک انتخاب‌شده
        if (!$this->selectedDoctorId) {
            $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
            return;
        }

        $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
            ->where('day', $this->emergencyModalDay)
            ->where('medical_center_id', $this->activeMedicalCenterId)
            ->first();
        if ($schedule) {
            $schedule->emergency_times = json_encode($this->emergencyTimes);
            $schedule->save();
            $this->refreshWorkSchedules();
            $this->showSuccessMessage('زمان‌های اورژانسی با موفقیت ذخیره شدند');
        } else {
            $this->showErrorMessage('برنامه کاری برای این روز یافت نشد');
        }
        $this->isEmergencyModalOpen = false;
        $this->dispatch('close-emergency-modal');
    }
    public function saveWorkSchedule()
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            DB::beginTransaction();
            $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            foreach ($daysOfWeek as $day) {
                $isWorking = $this->isWorking[$day] ?? false;
                $workHours = [];
                if (isset($this->slots[$day]) && is_array($this->slots[$day])) {
                    foreach ($this->slots[$day] as $slot) {
                        if (!empty($slot['start_time']) && !empty($slot['end_time'])) {
                            $workHours[] = [
                                'start' => $slot['start_time'],
                                'end' => $slot['end_time'],
                                'max_appointments' => $slot['max_appointments'] ?? null,
                            ];
                        }
                    }
                }
                $schedule = DoctorWorkSchedule::updateOrCreate(
                    [
                        'doctor_id' => $this->selectedDoctorId,
                        'day' => $day,
                        'medical_center_id' => $this->activeMedicalCenterId,
                    ],
                    [
                        'is_working' => $isWorking,
                        'work_hours' => json_encode($workHours),
                    ]
                );
            }
            DB::commit();
            $this->showSuccessMessage('برنامه کاری با موفقیت ذخیره شد');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorMessage('خطا در ذخیره برنامه کاری: ' . $e->getMessage());
        }
    }
    /**
    * Called when a slot is updated.
    */
    public function updatedSlots($value, $key)
    {
        // $key به فرمت slots.day.index.field است، مثلاً slots.saturday.0.start_time
        $parts = explode('.', $key);
        if (count($parts) === 4 && $parts[0] === 'slots') {
            $day = $parts[1];
            $index = (int) $parts[2];
            $this->autoSaveTimeSlot($day, $index);
        }
    }
    /**
 * Automatically saves a time slot when input fields are updated.
 */
    public function autoSaveTimeSlot($day, $index)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $slot = $this->slots[$day][$index] ?? null;
            if (!$slot) {
                return;
            }
            $startTime = $slot['start_time'] ?? null;
            $endTime = $slot['end_time'] ?? null;
            $maxAppointments = $slot['max_appointments'] ?? null;
            // اگر هر دو زمان خالی باشند، نیازی به ذخیره نیست
            if (empty($startTime) && empty($endTime)) {
                return;
            }
            // اعتبارسنجی
            if (!empty($startTime) && !empty($endTime)) {
                $validator = Validator::make([
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ], [
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i|after:start_time',
                ], [
                    'start_time.required' => 'لطفاً زمان شروع را وارد کنید.',
                    'start_time.date_format' => 'فرمت زمان شروع نامعتبر است.',
                    'end_time.required' => 'لطفاً زمان پایان را وارد کنید.',
                    'end_time.date_format' => 'فرمت زمان پایان نامعتبر است.',
                    'end_time.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
                ]);
                if ($validator->fails()) {
                    $this->showErrorMessage(implode(' ', $validator->errors()->all()));
                    return;
                }
            }
            // بررسی تداخل زمانی
            $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                ->where('day', $day)
                ->where('medical_center_id', $this->activeMedicalCenterId)
                    ->first();
            if ($schedule && $schedule->work_hours) {
                $workHours = json_decode($schedule->work_hours, true) ?? [];
                foreach ($workHours as $existingIndex => $existingSlot) {
                    if ($existingIndex !== $index && !empty($existingSlot['start']) && !empty($existingSlot['end'])) {
                        if ($this->isTimeConflict($startTime, $endTime, $existingSlot['start'], $existingSlot['end'])) {
                            $this->showErrorMessage("تداخل زمانی با بازه {$existingSlot['start']} تا {$existingSlot['end']} وجود دارد.");
                            return;
                        }
                    }
                }
            }
            // به‌روزرسانی یا ایجاد برنامه کاری
            if (!$schedule) {
                $schedule = DoctorWorkSchedule::create([
                    'doctor_id' => $this->selectedDoctorId,
                        'day' => $day,
                    'medical_center_id' => $this->activeMedicalCenterId,
                        'is_working' => true,
                        'work_hours' => json_encode([]),
                        'appointment_settings' => json_encode([]),
                    ]);
            }
            $workHours = json_decode($schedule->work_hours, true) ?? [];
            // به‌روزرسانی یا افزودن slot
            if (isset($workHours[$index])) {
                $workHours[$index] = [
                    'start' => $startTime,
                    'end' => $endTime,
                    'max_appointments' => $maxAppointments,
                ];
            } else {
                $workHours[] = [
                    'start' => $startTime,
                    'end' => $endTime,
                    'max_appointments' => $maxAppointments,
                ];
            }
            $schedule->update(['work_hours' => json_encode($workHours)]);
            $this->refreshWorkSchedules();
        } catch (\Exception $e) {
            $this->showErrorMessage('خطا در ذخیره زمان: ' . $e->getMessage());
        }
    }
    public function saveCalculator($day, $index, $appointmentCount, $timePerAppointment, $startTime, $endTime)
    {
        try {
            $validator = Validator::make(
                [
                    'day' => $day,
                    'index' => $index,
                    'appointmentCount' => $appointmentCount,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                ],
                [
                    'day' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
                    'index' => 'required|integer',
                    'appointmentCount' => 'required|integer|min:1',
                    'startTime' => 'required|date_format:H:i',
                    'endTime' => 'required|date_format:H:i|after:startTime',
                ],
                [
                    'day.required' => 'لطفاً یک روز را انتخاب کنید.',
                    'day.in' => 'روز انتخاب‌شده نامعتبر است.',
                    'index.required' => 'لطفاً ایندکس را وارد کنید.',
                    'index.integer' => 'ایندکس باید یک عدد صحیح باشد.',
                    'appointmentCount.required' => 'لطفاً تعداد حداکثر نوبت‌ها را وارد کنید.',
                    'appointmentCount.integer' => 'تعداد نوبت‌ها باید یک عدد صحیح باشد.',
                    'appointmentCount.min' => 'تعداد نوبت‌ها باید حداقل ۱ باشد.',
                    'startTime.required' => 'لطفاً زمان شروع را وارد کنید.',
                    'startTime.date_format' => 'فرمت زمان شروع باید به صورت ساعت:دقیقه باشد.',
                    'endTime.required' => 'لطفاً زمان پایان را وارد کنید.',
                    'endTime.date_format' => 'فرمت زمان پایان باید به صورت ساعت:دقیقه باشد.',
                    'endTime.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
                ]
            );
            if ($validator->fails()) {
                $errorMessage = implode('، ', $validator->errors()->all());
                $this->modalMessage = $errorMessage ?: 'لطفاً تمام فیلدهای مورد نیاز را پر کنید';
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'error',
                ]);
                $this->dispatch('close-calculator-modal');
                return;
            }
            $newSlot = [
                'start' => $startTime,
                'end' => $endTime,
                'max_appointments' => $appointmentCount,
            ];

            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            DB::beginTransaction();
            try {
                $workSchedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                    ->where('day', $day)
                    ->where('medical_center_id', $this->activeMedicalCenterId)
                    ->first();
                if (!$workSchedule) {
                    $workSchedule = DoctorWorkSchedule::create([
                        'doctor_id' => $this->selectedDoctorId,
                        'day' => $day,
                        'medical_center_id' => $this->activeMedicalCenterId,
                        'is_working' => true,
                        'work_hours' => '[]',
                        'appointment_settings' => '[]',
                    ]);
                }
                $workHours = is_array($workSchedule->work_hours)
                    ? $workSchedule->work_hours
                    : json_decode($workSchedule->work_hours, true) ?? [];
                $isSameTime = isset($workHours[$index]) &&
                              $workHours[$index]['start'] === $newSlot['start'] &&
                              $workHours[$index]['end'] === $newSlot['end'];
                if (!$isSameTime || !isset($workHours[$index])) {
                    $slotExists = array_filter($workHours, function ($slot, $i) use ($newSlot, $index) {
                        return $i !== $index && $slot['start'] === $newSlot['start'] && $slot['end'] === $newSlot['end'];
                    }, ARRAY_FILTER_USE_BOTH);
                    if (!empty($slotExists)) {
                        throw new \Exception(sprintf(
                            'بازه زمانی %s تا %s قبلاً برای روز %s ثبت شده است',
                            $newSlot['start'],
                            $newSlot['end'],
                            $this->getPersianDay($day)
                        ));
                    }
                    // بررسی تداخل زمانی
                    foreach ($workHours as $key => $slot) {
                        if ($key !== $index && $this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                            throw new \Exception(sprintf(
                                'زمان %s تا %s با بازه زمانی موجود %s تا %s در روز %s تداخل دارد',
                                $newSlot['start'],
                                $newSlot['end'],
                                $slot['start'],
                                $slot['end'],
                                $this->getPersianDay($day)
                            ));
                        }
                    }
                }
                if (!isset($workHours[$index])) {
                    $workHours[$index] = $newSlot;
                } else {
                    $workHours[$index] = $newSlot;
                }
                // تعریف تمام روزهای هفته برای appointment_settings
                $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                $newAppointmentSettings = [];
                // ایجاد تنظیمات پیش‌فرض برای همه روزهای هفته
                foreach ($daysOfWeek as $currentDay) {
                    $newAppointmentSettings[] = [
                        'day' => $currentDay,
                        'start_time' => '00:00',
                        'end_time' => '23:59',
                        'work_hour_key' => (int)$index,
                    ];
                }
                // به‌روزرسانی دیتابیس
                $workSchedule->update([
                    'work_hours' => json_encode($workHours),
                    'is_working' => true,
                    'appointment_settings' => json_encode($newAppointmentSettings),
                ]);
                $this->slots[$day][$index] = [
                    'id' => $workSchedule->id . '-' . $index,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'max_appointments' => $appointmentCount,
                ];
                DB::commit();
                $this->refreshWorkSchedules();
                $this->modalMessage = 'ساعت کاری و تنظیمات نوبت‌دهی با موفقیت ذخیره شد';
                $this->modalType = 'success';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'success',
                ]);
                $this->dispatch('close-calculator-modal');
                $this->dispatch('close-modal', ['name' => 'calculator-modal']);
                $this->dispatch('refresh-work-hours');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->modalMessage = $e->getMessage() ?: 'خطا در ذخیره‌سازی ساعات کاری';
                $this->modalType = 'error';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'error',
                ]);
                $this->dispatch('close-calculator-modal');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();
            $errorMessage = implode('، ', $errorMessages);
            $this->modalMessage = $errorMessage ?: 'لطفاً تمام فیلدهای مورد نیاز را پر کنید';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
            $this->dispatch('close-calculator-modal');
        }
    }
    public function copySchedule($replace = false)
    {
        try {
            // اضافه کردن شرط برای تنظیم activeMedicalCenterId
            if (request()->is('mc/panel/doctors-clinic/activation/workhours/*')) {
                $currentMedicalCenterId = request()->route('medicalCenter') ?? 'default';
                $this->activeMedicalCenterId = $currentMedicalCenterId;
            } else {
                $medicalCenterId = $this->activeMedicalCenterId;
                $this->activeMedicalCenterId = $medicalCenterId ?? 'default';
            }
            if (!$replace && !empty($this->copySource['day'])) {
                $this->storedCopySource = $this->copySource;
                $this->storedSelectedDays = $this->selectedDays;
            }
            $copySource = $replace || empty($this->copySource['day']) ? $this->storedCopySource : $this->copySource;
            $selectedDays = $replace || empty(array_filter($this->selectedDays)) ? $this->storedSelectedDays : $this->selectedDays;
            if (!isset($copySource['day']) || !in_array($copySource['day'], ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday']) || !isset($copySource['index']) || !is_numeric($copySource['index']) || $copySource['index'] < 0) {
                throw new \Exception('داده‌های منبع کپی نامعتبر است');
            }

            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $sourceDay = $copySource['day'];
            $sourceIndex = (int) $copySource['index'];
            Log::info($selectedDays);
            if (empty(array_filter($selectedDays))) {
                throw new \Exception('هیچ روزی برای کپی انتخاب نشده است');
            }
            $sourceSchedule = collect($this->workSchedules)->firstWhere('day', $sourceDay);
            if (!$sourceSchedule) {
                throw new \Exception('برنامه کاری برای روز مبدا یافت نشد');
            }
            $sourceWorkHours = $sourceSchedule['work_hours'] ?? [];
            $sourceAppointmentSettings = $sourceSchedule['appointment_settings'] ?? [];
            $sourceEmergencyTimes = $sourceSchedule['emergency_times'] ?? [];
            if (empty($sourceWorkHours[$sourceIndex])) {
                throw new \Exception('زمان انتخاب‌شده برای کپی یافت نشد');
            }
            $sourceSlot = $sourceWorkHours[$sourceIndex];
            $conflicts = [];
            $selectedTargetDays = array_keys(array_filter($selectedDays));
            DB::beginTransaction();
            try {
                foreach ($selectedTargetDays as $targetDay) {
                    if ($targetDay === $sourceDay) {
                        continue;
                    }
                    $targetSchedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                        ->where('day', $targetDay)
                        ->where('medical_center_id', $this->activeMedicalCenterId)
                        ->first();
                    if ($targetSchedule) {
                        $targetWorkHours = is_string($targetSchedule->work_hours) ? json_decode($targetSchedule->work_hours, true) : $targetSchedule->work_hours;
                        $targetEmergencyTimes = $targetSchedule->emergency_times;
                        if (is_string($targetEmergencyTimes)) {
                            $targetEmergencyTimes = json_decode($targetEmergencyTimes, true) ?? [];
                        }
                        if (!$replace && (!empty($targetWorkHours) || !empty($targetEmergencyTimes))) {
                            $conflictDetails = [];
                            foreach ($targetWorkHours as $slot) {
                                if ($this->isTimeConflict($sourceSlot['start'], $sourceSlot['end'], $slot['start'], $slot['end'])) {
                                    $conflictDetails['work_hours'][] = [
                                        'start' => $slot['start'],
                                        'end' => $slot['end'],
                                        'max_appointments' => $slot['max_appointments'] ?? null,
                                    ];
                                }
                            }
                            if (!empty($targetEmergencyTimes)) {
                                $conflictDetails['emergency_times'] = $targetEmergencyTimes;
                            }
                            if (!empty($conflictDetails)) {
                                $conflicts[$targetDay] = $conflictDetails;
                            }
                        }
                    }
                }
                if (!empty($conflicts) && !$replace) {
                    $this->dispatch('show-conflict-alert', ['conflicts' => $conflicts]);
                    $this->dispatch('close-checkbox-modal');
                    return;
                }
                $daysToCopy = $replace ? array_diff($selectedTargetDays, [$sourceDay]) : array_diff($selectedTargetDays, array_keys($conflicts), [$sourceDay]);
                if (empty($daysToCopy)) {
                    throw new \Exception('هیچ روزی برای کپی بدون تداخل یافت نشد');
                }
                foreach ($daysToCopy as $targetDay) {
                    $targetSchedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                        ->where('day', $targetDay)
                        ->where('medical_center_id', $this->activeMedicalCenterId)
                        ->first();
                    if (!$targetSchedule) {
                        $targetSchedule = DoctorWorkSchedule::create([
                            'doctor_id' => $this->selectedDoctorId,
                            'day' => $targetDay,
                            'medical_center_id' => $this->activeMedicalCenterId,
                            'is_working' => true,
                            'work_hours' => json_encode([$sourceSlot]),
                            'appointment_settings' => json_encode($sourceAppointmentSettings),
                            'emergency_times' => json_encode($sourceEmergencyTimes),
                        ]);
                    } else {
                        $targetWorkHours = is_string($targetSchedule->work_hours) ? json_decode($targetSchedule->work_hours, true) : $targetSchedule->work_hours;
                        if ($replace) {
                            $targetWorkHours = array_filter($targetWorkHours, fn ($slot) => !$this->isTimeConflict($sourceSlot['start'], $sourceSlot['end'], $slot['start'], $slot['end']));
                        }
                        $targetWorkHours[] = $sourceSlot;
                        $targetSchedule->update([
                            'work_hours' => json_encode($targetWorkHours),
                            'appointment_settings' => is_array($sourceAppointmentSettings) ? json_encode($sourceAppointmentSettings) : $sourceAppointmentSettings,
                            'emergency_times' => is_array($sourceEmergencyTimes) ? json_encode($sourceEmergencyTimes) : $sourceEmergencyTimes,
                            'is_working' => true,
                        ]);
                    }
                }
                $this->refreshWorkSchedules();
                $this->slots = [];
                $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                foreach ($daysOfWeek as $day) {
                    $schedule = collect($this->workSchedules)->firstWhere('day', $day);
                    if ($schedule) {
                        $this->isWorking[$day] = (bool) $schedule['is_working'];
                        $workHours = !empty($schedule['work_hours']) ? $schedule['work_hours'] : [];
                        if (!empty($workHours)) {
                            foreach ($workHours as $index => $slot) {
                                $this->slots[$day][$index] = [
                                    'id' => $schedule['id'] . '-' . $index,
                                    'start_time' => $slot['start'] ?? null,
                                    'end_time' => $slot['end'] ?? null,
                                    'max_appointments' => $slot['max_appointments'] ?? null,
                                ];
                            }
                        }
                    }
                    if (empty($this->slots[$day])) {
                        $this->slots[$day][] = [
                            'id' => null,
                            'start_time' => null,
                            'end_time' => null,
                            'max_appointments' => null,
                        ];
                    }
                }
                DB::commit();
                $this->selectedDays = [];
                $this->selectAllCopyModal = false;
                $this->modalMessage = $replace ? 'برنامه کاری با موفقیت جایگزین شد' : 'برنامه کاری با موفقیت کپی شد';
                $this->modalType = 'success';
                $this->modalOpen = true;
                $this->dispatch('show-toastr', [
                    'message' => $this->modalMessage,
                    'type' => 'success',
                ]);
                $this->dispatch('close-checkbox-modal');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->modalMessage = $e->getMessage() ?: 'خطا در کپی برنامه کاری';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }
    private function isTimeConflict($newStart, $newEnd, $existingStart, $existingEnd)
    {
        try {
            if (empty($newStart) || empty($newEnd) || empty($existingStart) || empty($existingEnd)) {
                return false;
            }
            $newStartTime = Carbon::createFromFormat('H:i', $newStart);
            $newEndTime = Carbon::createFromFormat('H:i', $newEnd);
            $existingStartTime = Carbon::createFromFormat('H:i', $existingStart);
            $existingEndTime = Carbon::createFromFormat('H:i', $existingEnd);
            if (!$newStartTime || !$newEndTime || !$existingStartTime || !$existingEndTime) {
                return false;
            }
            // حذف شرط بررسی بازه‌های یکسان - این باعث تداخل اشتباه می‌شود
            return ($newStartTime < $existingEndTime && $newEndTime > $existingStartTime);
        } catch (\Exception $e) {
            return false;
        }
    }
    public function openCopyModal($day)
    {
        try {
            if (!in_array($day, ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'])) {
                throw new \Exception('روز نامعتبر است');
            }
            $this->sourceDay = $day;
            $this->selectAllCopyModal = false;
            $this->selectedDays = array_fill_keys(
                ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                false
            );
            $this->dispatchBrowserEvent('open-checkbox-modal');
        } catch (\Exception $e) {
            $this->modalMessage = $e->getMessage() ?: 'خطا در باز کردن مودال کپی';
            $this->modalType = 'error';
            $this->modalOpen = true;
            $this->dispatch('show-toastr', [
                'message' => $this->modalMessage,
                'type' => 'error',
            ]);
        }
    }
    protected function timeToMinutes($time)
    {
        try {
            if (empty($time)) {
                return 0;
            }
            if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
                throw new \Exception('فرمت زمان نامعتبر است');
            }
            [$hours, $minutes] = explode(':', $time);
            $totalMinutes = ((int)$hours * 60) + (int)$minutes;
            if ($totalMinutes < 0 || $totalMinutes > 1440) { // 24 * 60 = 1440 minutes in a day
                throw new \Exception('زمان خارج از محدوده مجاز است');
            }
            return $totalMinutes;
        } catch (\Exception $e) {
            return 0;
        }
    }
    public function updatedCalculatorAppointmentCount($value)
    {
        try {
            // فقط مقدار مقابل را محاسبه کن و مقدار ورودی کاربر را هرگز تغییر نده
            if (!$value || !is_numeric($value) || $value <= 0) {
                $this->calculator['time_per_appointment'] = null;
                return;
            }
            $startTime = $this->calculator['start_time'] ?? null;
            $endTime = $this->calculator['end_time'] ?? null;
            if (!$startTime || !$endTime) {
                $this->calculator['time_per_appointment'] = null;
                $this->showError('زمان شروع یا پایان وارد نشده است');
                return;
            }
            $totalMinutes = $this->timeToMinutes($endTime) - $this->timeToMinutes($startTime);
            if ($totalMinutes <= 0) {
                $this->calculator['time_per_appointment'] = null;
                $this->showError('زمان پایان باید بعد از زمان شروع باشد');
                return;
            }
            if ($totalMinutes < $value) {
                $this->calculator['time_per_appointment'] = null;
                $this->showError('تعداد نوبت‌ها نمی‌تواند بیشتر از کل زمان باشد');
                return;
            }
            $timePerAppointment = $value > 0 ? $totalMinutes / $value : null;
            if ($timePerAppointment !== null && $timePerAppointment < 5) {
                $this->calculator['time_per_appointment'] = null;
                $this->showError('تعداد نوبت‌ها بیش از حد مجاز است. حداقل زمان هر نوبت باید ۵ دقیقه باشد.');
                return;
            }
            $this->calculator['time_per_appointment'] = $timePerAppointment;
            $this->calculationMode = 'count';
        } catch (\Exception $e) {
            $this->calculator['time_per_appointment'] = null;
            $this->showError('خطا در محاسبه زمان هر نوبت');
        }
    }
    private function showError($message)
    {
        $this->modalMessage = $message;
        $this->modalType = 'error';
        $this->modalOpen = true;
        $this->dispatch('show-toastr', [
            'message' => $message,
            'type' => 'error'
        ]);
    }
    public function updatedCalculatorTimePerAppointment($value)
    {
        try {
            // فقط مقدار مقابل را محاسبه کن و مقدار ورودی کاربر را هرگز تغییر نده
            if (!$value || !is_numeric($value) || $value <= 0) {
                $this->calculator['appointment_count'] = null;
                return;
            }
            $startTime = $this->calculator['start_time'] ?? null;
            $endTime = $this->calculator['end_time'] ?? null;
            if (!$startTime || !$endTime) {
                $this->calculator['appointment_count'] = null;
                $this->showError('زمان شروع یا پایان وارد نشده است');
                return;
            }
            $totalMinutes = $this->timeToMinutes($endTime) - $this->timeToMinutes($startTime);
            if ($totalMinutes <= 0) {
                $this->calculator['appointment_count'] = null;
                $this->showError('زمان پایان باید بعد از زمان شروع باشد');
                return;
            }
            if ($value > $totalMinutes) {
                $this->calculator['appointment_count'] = null;
                $this->showError('زمان هر نوبت نمی‌تواند بیشتر از کل زمان باشد');
                return;
            }
            if ($value < 5) {
                $this->calculator['appointment_count'] = null;
                $this->showError('حداقل زمان هر نوبت باید ۵ دقیقه باشد.');
                return;
            }
            $appointmentCount = $value > 0 ? $totalMinutes / $value : null;
            $this->calculator['appointment_count'] = $appointmentCount;
            $this->calculationMode = 'time';
        } catch (\Exception $e) {
            $this->calculator['appointment_count'] = null;
            $this->showError('خطا در محاسبه تعداد نوبت‌ها');
        }
    }
    private function getPersianDay($englishDay)
    {
        $dayMap = [
            'saturday' => 'شنبه',
            'sunday' => 'یکشنبه',
            'monday' => 'دوشنبه',
            'tuesday' => 'سه‌شنبه',
            'wednesday' => 'چهارشنبه',
            'thursday' => 'پنج‌شنبه',
            'friday' => 'جمعه',
        ];
        return $dayMap[$englishDay] ?? $englishDay;
    }
    public function setEmergencyModalOpen($isOpen)
    {
        $this->isEmergencyModalOpen = $isOpen;
    }
    public function updatedSelectedMedicalCenterId()
    {
        $this->activeMedicalCenterId = $this->selectedMedicalCenterId;
        $this->reset(['workSchedules', 'isWorking', 'slots']);
        $this->mount($this->medicalCenterId);
        $this->dispatch('refresh-clinic-data');
    }
    public function saveTimeSlot()
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $this->validate([
                'selectedDay' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
                'startTime' => 'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
                'maxAppointments' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();
            try {
                $workSchedule = DoctorWorkSchedule::withoutGlobalScopes()
                    ->where('doctor_id', $this->selectedDoctorId)
                    ->where('day', $this->selectedDay)
                    ->where('medical_center_id', $this->activeMedicalCenterId)
                    ->first();
                if (!$workSchedule) {
                    $workSchedule = DoctorWorkSchedule::create([
                        'doctor_id' => $this->selectedDoctorId,
                        'day' => $this->selectedDay,
                        'medical_center_id' => $this->activeMedicalCenterId,
                        'is_working' => true,
                        'work_hours' => json_encode([]),
                    ]);
                }
                $existingWorkHours = is_array($workSchedule->work_hours) ? $workSchedule->work_hours : json_decode($workSchedule->work_hours, true) ?? [];
                $newSlot = [
                    'start' => $this->startTime,
                    'end' => $this->endTime,
                    'max_appointments' => (int) $this->maxAppointments,
                ];
                // بررسی تداخل زمانی با یک حلقه
                foreach ($existingWorkHours as $slot) {
                    if ($this->isTimeConflict($newSlot['start'], $newSlot['end'], $slot['start'], $slot['end'])) {
                        throw new \Exception(sprintf(
                            'زمان %s تا %s با بازه زمانی موجود %s تا %s در روز %s تداخل دارد',
                            $newSlot['start'],
                            $newSlot['end'],
                            $slot['start'],
                            $slot['end'],
                            $this->getPersianDay($this->selectedDay)
                        ));
                    }
                }
                $existingWorkHours[] = $newSlot;
                $workSchedule->update([
                    'work_hours' => json_encode($existingWorkHours),
                    'is_working' => true
                ]);
                $newSlotIndex = count($existingWorkHours) - 1;
                $this->slots[$this->selectedDay][] = [
                    'id' => $workSchedule->id . '-' . $newSlotIndex,
                    'start_time' => $this->startTime,
                    'end_time' => $this->endTime,
                    'max_appointments' => (int) $this->maxAppointments,
                ];
                DB::commit();
                $this->showSuccessMessage('ساعت کاری با موفقیت ذخیره شد');
                $this->reset(['startTime', 'endTime', 'maxAppointments', 'selectedDay']);
                $this->dispatch('refresh-work-hours');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();
            $errorMessage = implode('، ', $errorMessages);
            $this->showErrorMessage($errorMessage ?: 'لطفاً تمام فیلدهای مورد نیاز را پر کنید');
        } catch (\Exception $e) {
            $this->showErrorMessage($e->getMessage() ?: 'خطا در ذخیره‌سازی ساعت کاری');
        }
    }
    public function deleteTimeSlot($day, $index)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            DB::beginTransaction();
            try {
                $workSchedule = DoctorWorkSchedule::withoutGlobalScopes()
                    ->where('doctor_id', $this->selectedDoctorId)
                    ->where('day', $day)
                    ->where('medical_center_id', $this->activeMedicalCenterId)
                    ->first();
                if (!$workSchedule) {
                    throw new \Exception('ساعات کاری برای این روز یافت نشد');
                }
                $existingWorkHours = is_array($workSchedule->work_hours) ? $workSchedule->work_hours : json_decode($workSchedule->work_hours, true) ?? [];
                if (!isset($existingWorkHours[$index])) {
                    throw new \Exception('زمان انتخاب‌شده یافت نشد');
                }
                // حذف زمان و بازسازی آرایه
                unset($existingWorkHours[$index]);
                $updatedWorkHours = array_values($existingWorkHours);
                // به‌روزرسانی رکورد
                $workSchedule->update([
                    'work_hours' => json_encode($updatedWorkHours),
                    'is_working' => !empty($updatedWorkHours)
                ]);
                // به‌روزرسانی آرایه slots
                unset($this->slots[$day][$index]);
                $this->slots[$day] = array_values($this->slots[$day]);
                // اگر هیچ زمانی باقی نمانده، یک زمان خالی اضافه کن
                if (empty($this->slots[$day])) {
                    $this->slots[$day][] = [
                        'id' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'max_appointments' => null,
                    ];
                }
                DB::commit();
                $this->showSuccessMessage('زمان با موفقیت حذف شد');
                $this->dispatch('refresh-work-hours');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->showErrorMessage($e->getMessage() ?: 'خطا در حذف زمان');
        }
    }
    public function removeSlot($day, $index)
    {
        $this->deleteTimeSlot($day, $index);
    }
    public function updateWorkDayStatus($day)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $isWorking = $this->isWorking[$day] ?? false;
            $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                ->where('day', $day)
                ->where('medical_center_id', $this->activeMedicalCenterId)
                    ->first();
            if ($schedule) {
                $schedule->update(['is_working' => $isWorking]);
            } else {
                DoctorWorkSchedule::create([
                'doctor_id' => $this->selectedDoctorId,
                'day' => $day,
                'medical_center_id' => $this->activeMedicalCenterId,
                    'is_working' => $isWorking,
                    'work_hours' => json_encode([]),
                'appointment_settings' => json_encode([]),
                ]);
            }
            $this->refreshWorkSchedules();
        } catch (\Exception $e) {
            $this->showErrorMessage('خطا در به‌روزرسانی وضعیت روز کاری: ' . $e->getMessage());
        }
    }
    public function addSlot($day)
    {
        // بررسی وجود پزشک انتخاب‌شده
        if (!$this->selectedDoctorId) {
            $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
            return;
        }

        if (!isset($this->slots[$day])) {
            $this->slots[$day] = [];
        }
        // بررسی تداخل بازه‌ها
        $existing = $this->slots[$day];
        // اگر بازه‌ای کل روز را پوشش داده (مثلاً 00:00 تا 23:59)، اجازه افزودن نده
        foreach ($existing as $slot) {
            if (
                isset($slot['start_time'], $slot['end_time']) &&
                $slot['start_time'] === '00:00' && $slot['end_time'] === '23:59'
            ) {
                $this->showErrorMessage('شما قبلاً کل روز را رزرو کرده‌اید و امکان افزودن بازه جدید وجود ندارد.');
                return;
            }
        }
        // بررسی تداخل بازه جدید با بازه‌های قبلی (فرض: بازه جدید هنوز مقدار ندارد، پس فقط اگر هیچ بازه‌ای کل روز را نگرفته اجازه بده)
        // اگر بازه جدید مقدار دارد (مثلاً کاربر مقدار وارد کرده)، باید تداخل را بررسی کنیم
        // اما اینجا فقط اجازه اضافه شدن ردیف خالی را می‌دهیم اگر تداخلی با بازه‌های قبلی نباشد
        // پس اگر بازه جدید با بازه‌های قبلی تداخل داشته باشد، ردیف جدید را اضافه نکن
        // (در این مرحله بازه جدید مقدار ندارد، پس فقط بررسی کل روز کافی است)
        $this->slots[$day][] = [
            'id' => null,
            'start_time' => null,
            'end_time' => null,
            'max_appointments' => null,
        ];
        $this->dispatch('refresh-timepicker');
    }
    public function updatedIsWorking($value, $key)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $day = $key;
            $isWorking = (bool) $value;

            $schedule = DoctorWorkSchedule::where('doctor_id', $this->selectedDoctorId)
                ->where('day', $day)
                ->where('medical_center_id', $this->activeMedicalCenterId)
                ->first();

            if ($schedule) {
                $schedule->update(['is_working' => $isWorking]);
            } else {
                DoctorWorkSchedule::create([
                    'doctor_id' => $this->selectedDoctorId,
                    'day' => $day,
                    'medical_center_id' => $this->activeMedicalCenterId,
                    'is_working' => $isWorking,
                    'work_hours' => json_encode([]),
                    'appointment_settings' => json_encode([]),
                ]);
            }

            $this->refreshWorkSchedules();
        } catch (\Exception $e) {
            $this->showErrorMessage('خطا در به‌روزرسانی وضعیت روز کاری: ' . $e->getMessage());
        }
    }
    public function updateAutoScheduling()
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $rules = [];
            $messages = [];
            // فقط در حالت نوبت‌دهی آنلاین + دستی، این موارد رو اعتبارسنجی کن
            if ($this->autoScheduling) {
                $rules['calendarDays'] = 'required|integer|min:1';
                $rules['holidayAvailability'] = 'boolean';
                $messages = [
                    'calendarDays.required' => 'تعداد روزهای تقویم الزامی است',
                    'calendarDays.integer' => 'تعداد روزهای تقویم باید عدد باشد',
                    'calendarDays.min' => 'تعداد روزهای تقویم باید حداقل ۱ باشد',
                    'holidayAvailability.boolean' => 'مقدار در دسترس بودن در تعطیلات نامعتبر است',
                ];
            }
            $rules['autoScheduling'] = 'boolean';
            $rules['onlineConsultation'] = 'boolean';
            $messages['autoScheduling.boolean'] = 'مقدار نوبت‌دهی آنلاین + دستی نامعتبر است';
            $messages['onlineConsultation.boolean'] = 'مقدار مشاوره آنلاین نامعتبر است';
            $this->validate($rules, $messages);

            DB::beginTransaction();
            try {
                $data = [
                    'auto_scheduling' => (bool) $this->autoScheduling,
                    'online_consultation' => (bool) $this->onlineConsultation,
                ];
                if ($this->autoScheduling) {
                    $data['calendar_days'] = (int) $this->calendarDays;
                    $data['holiday_availability'] = (bool) $this->holidayAvailability;
                }
                $config = DoctorAppointmentConfig::withoutGlobalScopes()->updateOrCreate(
                    [
                        'doctor_id' => $this->selectedDoctorId,
                        'medical_center_id' => $this->activeMedicalCenterId,
                    ],
                    $data
                );
                // به‌روزرسانی تنظیمات در حافظه
                $this->appointmentConfig = $config;
                if ($this->autoScheduling) {
                    $this->calendarDays = (int) $this->calendarDays;
                    $this->holidayAvailability = (bool) $this->holidayAvailability;
                }
                $this->autoScheduling = (bool) $this->autoScheduling;
                $this->onlineConsultation = (bool) $this->onlineConsultation;
                DB::commit();
                $this->showSuccessMessage('تنظیمات با موفقیت ذخیره شد');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = $e->validator->errors()->all();
            $errorMessage = implode('، ', $errorMessages);
            $this->showErrorMessage($errorMessage ?: 'لطفاً تمام فیلدهای مورد نیاز را پر کنید');
        } catch (\Exception $e) {
            $this->showErrorMessage($e->getMessage() ?: 'خطا در ذخیره تنظیمات');
        }
    }
    public function render()
    {
        return view('livewire.mc.panel.work-hours');
    }
    // ذخیره خودکار هر فیلد تنظیمات نوبت دستی
    public function autoSaveManualNobatSetting($field)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $value = $this->$field;
            $config = DoctorAppointmentConfig::where('doctor_id', $this->selectedDoctorId)
                ->where('medical_center_id', $this->activeMedicalCenterId)
                ->first();
            if ($config) {
                $config->update([$field => $value]);
            }
        } catch (\Exception $e) {
            $this->showErrorMessage('خطا در ذخیره تنظیمات: ' . $e->getMessage());
        }
    }
    // متد برای toggle فعال/غیرفعال بودن نوبت دستی
    public function updateManualNobatActive()
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $data = [
                'doctor_id' => $this->selectedDoctorId,
                'medical_center_id' => $this->activeMedicalCenterId,
            ];
            $values = [
                'is_active' => (bool) $this->manualNobatActive,
                'duration_send_link' => (int) $this->manualNobatSendLink,
                'duration_confirm_link' => (int) $this->manualNobatConfirmLink,
            ];
            $setting = \App\Models\DoctorAppointmentConfig::updateOrCreate($data, $values);
            $this->manualNobatSettingId = $setting->id;
            // به‌روزرسانی مقادیر در کامپوننت از دیتابیس
            $this->manualNobatActive = (bool) $setting->is_active;
            $this->manualNobatSendLink = (int) $setting->duration_send_link;
            $this->manualNobatConfirmLink = (int) $setting->duration_confirm_link;
            $this->showSuccessMessage($this->manualNobatActive ? 'نوبت‌دهی دستی فعال شد' : 'نوبت‌دهی دستی غیرفعال شد');
        } catch (\Exception $e) {
            $this->showErrorMessage($e->getMessage() ?: 'خطا در تغییر وضعیت نوبت‌دهی دستی');
        }
    }
    // متد auto-save برای تغییر وضعیت نوبت دستی
    public function updatedManualNobatActive($value)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $data = [
                'doctor_id' => $this->selectedDoctorId,
                'medical_center_id' => $this->activeMedicalCenterId,
            ];
            $values = [
                'is_active' => (bool) $value,
                'duration_send_link' => (int) $this->manualNobatSendLink,
                'duration_confirm_link' => (int) $this->manualNobatConfirmLink,
            ];
            $setting = \App\Models\DoctorAppointmentConfig::updateOrCreate($data, $values);
            $this->manualNobatSettingId = $setting->id;
            // به‌روزرسانی مقادیر در کامپوننت از دیتابیس
            $this->manualNobatActive = (bool) $setting->is_active;
            $this->manualNobatSendLink = (int) $setting->duration_send_link;
            $this->manualNobatConfirmLink = (int) $setting->duration_confirm_link;
            $this->showSuccessMessage($value ? 'تاییدیه نوبت دستی فعال شد' : 'تاییدیه نوبت دستی غیرفعال شد');
        } catch (\Exception $e) {
            $this->showErrorMessage($e->getMessage() ?: 'خطا در ذخیره خودکار وضعیت نوبت‌دهی دستی');
        }
    }
    // متد auto-save برای تغییر مدت زمان ارسال لینک
    public function updatedManualNobatSendLink($value)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $data = [
                'doctor_id' => $this->selectedDoctorId,
                'medical_center_id' => $this->activeMedicalCenterId,
            ];
            $values = [
                'is_active' => (bool) $this->manualNobatActive,
                'duration_send_link' => (int) $value,
                'duration_confirm_link' => (int) $this->manualNobatConfirmLink,
            ];
            $setting = \App\Models\DoctorAppointmentConfig::updateOrCreate($data, $values);
            $this->manualNobatSettingId = $setting->id;
            // به‌روزرسانی مقادیر در کامپوننت از دیتابیس
            $this->manualNobatActive = (bool) $setting->is_active;
            $this->manualNobatSendLink = (int) $setting->duration_send_link;
            $this->manualNobatConfirmLink = (int) $setting->duration_confirm_link;
            $this->showSuccessMessage('مدت زمان ارسال لینک با موفقیت بروزرسانی شد');
        } catch (\Exception $e) {
            $this->showErrorMessage($e->getMessage() ?: 'خطا در ذخیره خودکار مدت زمان ارسال لینک');
        }
    }
    // متد auto-save برای تغییر مدت زمان اعتبار لینک
    public function updatedManualNobatConfirmLink($value)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            $data = [
                'doctor_id' => $this->selectedDoctorId,
                'medical_center_id' => $this->activeMedicalCenterId,
            ];
            $values = [
                'is_active' => (bool) $this->manualNobatActive,
                'duration_send_link' => (int) $this->manualNobatSendLink,
                'duration_confirm_link' => (int) $value,
            ];
            $setting = \App\Models\DoctorAppointmentConfig::updateOrCreate($data, $values);
            $this->manualNobatSettingId = $setting->id;
            // به‌روزرسانی مقادیر در کامپوننت از دیتابیس
            $this->manualNobatActive = (bool) $setting->is_active;
            $this->manualNobatSendLink = (int) $setting->duration_send_link;
            $this->manualNobatConfirmLink = (int) $setting->duration_confirm_link;
            $this->showSuccessMessage('مدت زمان اعتبار لینک با موفقیت بروزرسانی شد');
        } catch (\Exception $e) {
            $this->showErrorMessage($e->getMessage() ?: 'خطا در ذخیره خودکار مدت زمان اعتبار لینک');
        }
    }
    // متد auto-save برای تغییر وضعیت باز بودن مطب در تعطیلات
    public function updatedHolidayAvailability($value)
    {
        try {
            // بررسی وجود پزشک انتخاب‌شده
            if (!$this->selectedDoctorId) {
                $this->showErrorMessage('لطفاً ابتدا یک پزشک انتخاب کنید');
                return;
            }

            DB::beginTransaction();
            $data = [
                'auto_scheduling' => (bool) $this->autoScheduling,
                'online_consultation' => (bool) $this->onlineConsultation,
                'holiday_availability' => (bool) $value,
            ];
            if ($this->autoScheduling) {
                $data['calendar_days'] = (int) $this->calendarDays;
            }
            $config = DoctorAppointmentConfig::updateOrCreate(
                [
                    'doctor_id' => $this->selectedDoctorId,
                    'medical_center_id' => $this->activeMedicalCenterId,
                ],
                $data
            );
            DB::commit();
            $this->appointmentConfig = $config;
            $this->holidayAvailability = (bool) $value;
            $this->showSuccessMessage('وضعیت باز بودن در تعطیلات با موفقیت بروزرسانی شد');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorMessage($e->getMessage() ?: 'خطا در ذخیره خودکار وضعیت باز بودن در تعطیلات');
        }
    }
    public function openCopyScheduleModal($day, $index)
    {
        $this->copySourceDay = $day;
        $this->copySourceIndex = $index;
        Log::info('openCopyScheduleModal called', ['copySourceDay' => $day, 'copySourceIndex' => $index]);
    }
    public function setSelectedMedicalCenterId($medicalCenterId)
    {
        try {
            // تنظیم medicalCenterId و activeMedicalCenterId
            $this->selectedMedicalCenterId = $medicalCenterId;
            $this->activeMedicalCenterId = $this->medicalCenterId ?? $medicalCenterId;
            // ذخیره medicalCenterId در سشن
            session(['selectedMedicalCenterId' => $medicalCenterId]);
            // بازنشانی پراپرتی‌ها
            $this->reset(['workSchedules', 'isWorking', 'slots']);
            // بارگذاری مجدد داده‌ها
            $this->mount();
            // ارسال رویداد رفرش
            $this->dispatch('refresh-clinic-data');
        } catch (\Exception $e) {
            $this->showErrorMessage('خطا در تنظیم مرکز درمانی: ' . ($e->getMessage() ?: 'خطای ناشناخته'));
        }
    }
}
