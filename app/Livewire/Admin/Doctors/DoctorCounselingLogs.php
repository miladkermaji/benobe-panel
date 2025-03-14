<?php
namespace App\Livewire\Admin\Doctors;

use App\Helpers\JalaliHelper;
use App\Models\CounselingAppointment;
use App\Models\Doctor;
use App\Models\UserBlocking;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class DoctorCounselingLogs extends Component
{
    use WithPagination;

    public $reqDoctor            = '0';
    public $mobile               = '';
    public $trackingCode         = '';
    public $startDate            = '';
    public $endDate              = '';
    public $search               = '';
    public $selectedAppointments = [];
    public $selectAll            = false;
    public $perPage              = 50;

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['reqDoctor', 'mobile', 'trackingCode', 'startDate', 'endDate', 'search', 'selectAll'])) {
            $this->resetPage();
        }
    }

    public function updatedSelectAll($value)
    {
        $appointments               = $this->getAppointmentsQuery()->paginate($this->perPage);
        $this->selectedAppointments = $value ? $appointments->pluck('id')->toArray() : [];
    }

    public function updatedSelectedAppointments()
    {
        $appointments    = $this->getAppointmentsQuery()->paginate($this->perPage);
        $currentPageIds  = $appointments->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedAppointments) && ! array_diff($currentPageIds, $this->selectedAppointments);
    }

    public function resetFilters()
    {
        $this->reqDoctor            = '0';
        $this->mobile               = '';
        $this->trackingCode         = '';
        $this->startDate            = '';
        $this->endDate              = '';
        $this->search               = '';
        $this->selectedAppointments = [];
        $this->selectAll            = false;
        $this->resetPage();
    }

    public function toggleBlockUser($userId, $doctorId, $userName, $status)
    {

        $appointment = CounselingAppointment::where('patient_id', $userId)->where('doctor_id', $doctorId)->first();
        $patientName = $appointment && $appointment->patient
        ? trim($appointment->patient->first_name . ' ' . $appointment->patient->last_name)
        : 'کاربر بدون نام';

        $isBlocked = UserBlocking::where('user_id', $userId)
            ->where('doctor_id', $doctorId)
            ->where('status', 1)
            ->exists();

        $this->dispatch('show-ban-form', [
            'userId'   => $userId,
            'doctorId' => $doctorId,
            'userName' => $patientName,
            'status'   => $isBlocked ? 0 : 1,
        ]);
    }

    public function toggleBlockUserConfirm($userId, $doctorId, $data)
    {

        try {
            $isBlocked = UserBlocking::where('user_id', $userId)
                ->where('doctor_id', $doctorId)
                ->where('status', 1)
                ->exists();

            $user       = \App\Models\User::findOrFail($userId);
            $doctor     = Doctor::findOrFail($doctorId);
            $doctorName = $doctor->first_name . ' ' . $doctor->last_name;

            if ($data['status'] == 1) {
                if ($isBlocked) {
                    $this->dispatch('toast', ['message' => 'این کاربر قبلاً مسدود شده است.', 'type' => 'error']);
                    return;
                }
                $expiryGregorian = JalaliHelper::parsePersianTextDate($data['expiry']);
                UserBlocking::create([
                    'user_id'      => $userId,
                    'doctor_id'    => $doctorId,
                    'blocked_at'   => now(),
                    'unblocked_at' => $expiryGregorian,
                    'reason'       => $data['reason'],
                    'status'       => 1,
                ]);

                // ارسال پیامک برای مسدود کردن
                $message    = "کاربر گرامی، شما توسط پزشک {$doctorName} مسدود شده‌اید.";
                $smsService = new MessageService(
                    SmsService::create(100254, $user->mobile, [$doctorName])
                );
                $smsService->send();

                $this->dispatch('toast', ['message' => 'کاربر با موفقیت مسدود شد و پیامک ارسال شد.', 'type' => 'success']);
            } else {
                if (! $isBlocked) {
                    $this->dispatch('toast', ['message' => 'این کاربر مسدود نیست.', 'type' => 'error']);
                    return;
                }
                UserBlocking::where('user_id', $userId)
                    ->where('doctor_id', $doctorId)
                    ->update(['status' => 0]);

                // ارسال پیامک برای رفع مسدودی
                $message    = "کاربر گرامی، شما توسط پزشک {$doctorName} از حالت مسدودی خارج شدید.";
                $smsService = new MessageService(
                    SmsService::create(100255, $user->mobile, [$doctorName])
                );
                $smsService->send();

                $this->dispatch('toast', ['message' => 'کاربر با موفقیت از مسدودی خارج شد و پیامک ارسال شد.', 'type' => 'success']);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => 'خطا در عملیات: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function cancelAppointment($id)
    {
        $this->dispatch('confirm-action', ['action' => 'cancel', 'id' => $id]);
    }

    public function confirmCancel($id)
    {
        try {
            $appointment = CounselingAppointment::findOrFail($id);

            // چک کردن اینکه نوبت قبلاً لغو شده یا نه
            if ($appointment->status === 'cancelled') {
                $this->dispatch('toast', ['message' => 'این نوبت قبلاً لغو شده است.', 'type' => 'warning']);
                return;
            }

            $appointment->update(['status' => 'cancelled']);

            // ارسال پیامک برای لغو نوبت
            $user         = \App\Models\User::findOrFail($appointment->patient_id);
            $doctor       = Doctor::findOrFail($appointment->doctor_id);
            $doctorName   = $doctor->first_name . ' ' . $doctor->last_name;
            $trackingCode = $appointment->tracking_code;
            $message      = "نوبت شما با کد پیگیری {$trackingCode} توسط پزشک {$doctorName} لغو شد.";
            $smsService   = new MessageService(
                SmsService::create(100256, $user->mobile, [$trackingCode, $doctorName])
            );
            $smsService->send();

            $this->dispatch('toast', ['message' => 'نوبت با موفقیت لغو شد و پیامک ارسال شد.', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => 'خطا در لغو نوبت: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function deleteAppointment($id)
    {
        $this->dispatch('confirm-action', ['action' => 'delete', 'id' => $id]);
    }

    public function confirmDelete($id)
    {
        try {
            $appointment = CounselingAppointment::findOrFail($id);
            $appointment->delete();
            $this->selectedAppointments = array_diff($this->selectedAppointments, [$id]);
            $this->dispatch('toast', ['message' => 'نوبت با موفقیت حذف شد.', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => 'خطا در حذف نوبت: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function deleteSelected()
    {

        if (empty($this->selectedAppointments)) {
            $this->dispatch('toast', ['message' => 'هیچ نوبت‌ی انتخاب نشده است.', 'type' => 'warning']);
            return;
        }
        $this->dispatch('confirm-delete-selected');
    }

    public function confirmDeleteSelected()
    {

        try {
            CounselingAppointment::whereIn('id', $this->selectedAppointments)->delete();
            $this->selectedAppointments = [];
            $this->selectAll            = false;
            $this->dispatch('toast', ['message' => 'نوبت‌های انتخاب‌شده با موفقیت حذف شدند.', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => 'خطا در حذف نوبت‌ها: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function export()
    {
        $appointments = $this->getAppointmentsQuery()->get();
        $csv          = "ردیف,پزشک,شماره تماس,استان/شهر,تاریخ ملاقات,زمان ملاقات,نام کاربر,کدملی کاربر,تاریخ رزرو,کد پیگیری,وضعیت,مبلغ (تومان),مدت زمان (دقیقه),زمان تماس,وضعیت تسویه\n";
        foreach ($appointments as $index => $appointment) {
            $statusText = match ($appointment->status) {
                'scheduled' => 'در انتظار خدمت',
                'cancelled' => 'لغو شده',
                'attended' => 'حضور یافته',
                'missed' => 'غایب',
                'pending_review' => 'در انتظار بررسی و تماس',
                'call_answered' => 'تماس و پاسخ داده شده',
                'call_completed' => 'مکالمه انجام و پایان یافته است',
                'refunded' => 'بازگشت به کیف پول',
                default => $appointment->status,
            };

            $csv .= ($index + 1) . ',' .
            ($appointment->doctor->full_name ?? '') . ',' .
            ($appointment->doctor->mobile ?? '') . ',' .
            ($appointment->doctor->province->name ?? '') . '/' . ($appointment->doctor->city->name ?? '') . ',' .
            JalaliHelper::toJalaliDate($appointment->appointment_date) . ',' .
            ($appointment->start_time ?? '') . ',' .
            ($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? '') . ' (' . ($appointment->patient->mobile ?? '') . '),' .
            ($appointment->patient->national_code ?? '') . ',' .
            JalaliHelper::toJalaliDateTime($appointment->reserved_at) . ',' .
                ($appointment->tracking_code ?? '') . ',' .
                $statusText . ',' .
                ($appointment->fee ? number_format($appointment->fee) : '0') . ',' .
                ($appointment->duration ?? '0') . ',' .
                ($appointment->confirmed_at ? JalaliHelper::toJalaliDateTime($appointment->confirmed_at) : '---') . ',' .
                ($appointment->payment_status === 'paid' ? 'پرداخت شده' : ($appointment->payment_status === 'unpaid' ? 'پرداخت نشده' : 'در انتظار پرداخت')) . "\n";
        }
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'appointments-logs.csv');
    }
    public function changeStatus($appointmentId, $newStatus)
    {
        try {
            $appointment = CounselingAppointment::findOrFail($appointmentId);

            // آپدیت وضعیت
            $appointment->update(['status' => $newStatus]);

            // ارسال پیامک به کاربر (اختیاری، بر اساس نیاز)
            $user         = \App\Models\User::findOrFail($appointment->patient_id);
            $doctor       = Doctor::findOrFail($appointment->doctor_id);
            $doctorName   = $doctor->first_name . ' ' . $doctor->last_name;
            $trackingCode = $appointment->tracking_code;

            $statusMessage = match ($newStatus) {
                'pending_review' => 'در انتظار بررسی و تماس قرار گرفت.',
                'call_answered' => 'تماس شما پاسخ داده شد.',
                'call_completed' => 'مکالمه شما با موفقیت انجام و پایان یافت.',
                'refunded' => 'نوبت شما لغو و مبلغ به کیف پول شما بازگشت.',
                'missed' => 'به دلیل عدم پاسخگویی، نوبت شما لغو شد.',
                default => 'وضعیت نوبت شما تغییر کرد.',
            };

            $message    = "نوبت شما با کد پیگیری {$trackingCode} با پزشک {$doctorName} به {$statusMessage}";
            $smsService = new MessageService(
                SmsService::create(100257, $user->mobile, [$trackingCode, $doctorName, $statusMessage])
            );
            $smsService->send();

            $this->dispatch('toast', ['message' => 'وضعیت نوبت با موفقیت تغییر کرد و پیامک ارسال شد.', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => 'خطا در تغییر وضعیت: ' . $e->getMessage(), 'type' => 'error']);
        }
    }
    private function getAppointmentsQuery()
    {
        $startDateGregorian = $this->startDate ? JalaliHelper::parsePersianTextDate($this->startDate) : null;
        $endDateGregorian   = $this->endDate ? JalaliHelper::parsePersianTextDate($this->endDate) : null;
        return CounselingAppointment::with(['doctor', 'patient', 'doctor.province', 'doctor.city'])
            ->when($this->reqDoctor && $this->reqDoctor != '0', fn($q) => $q->where('doctor_id', $this->reqDoctor))
            ->when($this->mobile, fn($q) => $q->whereHas('patient', fn($q2) => $q2->where('mobile', 'like', '%' . trim($this->mobile) . '%')))
            ->when($this->trackingCode, fn($q) => $q->where('tracking_code', 'like', '%' . trim($this->trackingCode) . '%'))
            ->when($this->startDate, fn($q) => $q->whereDate('appointment_date', '>=', $startDateGregorian))
            ->when($this->endDate, fn($q) => $q->whereDate('appointment_date', '<=', $endDateGregorian))
            ->when($this->search, fn($q) => $q->where(function ($query) {
                $searchTerm = '%' . trim($this->search) . '%';
                $query->whereHas('doctor', fn($q2) => $q2->where('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm)
                        ->orWhere('mobile', 'like', $searchTerm)
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm])) // جستجو بر اساس نام کامل پزشک
                    ->orWhereHas('patient', fn($q2) => $q2->where('first_name', 'like', $searchTerm)
                            ->orWhere('last_name', 'like', $searchTerm)
                            ->orWhere('mobile', 'like', $searchTerm)
                            ->orWhere('national_code', 'like', $searchTerm)
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchTerm])) // جستجو بر اساس نام کامل بیمار
                    ->orWhere('tracking_code', 'like', $searchTerm)
                    ->orWhere('appointment_date', 'like', $searchTerm)
                    ->orWhere('start_time', 'like', $searchTerm);
            }));
    }

    public function mount()
    {
        $this->reqDoctor = '0';
    }

    public function render()
    {
        $appointments = $this->getAppointmentsQuery()->paginate($this->perPage);
        $doctors      = Doctor::all();
        return view('livewire.admin.doctors.doctor-counseling-logs', [
            'appointments'     => $appointments,
            'doctors'          => $doctors,
            'selectedDoctorId' => $this->reqDoctor,
        ]);
    }
}
