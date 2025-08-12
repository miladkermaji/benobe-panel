<?php

namespace App\Livewire\Mc\Panel\Layouts\Partials;

use Livewire\Component;
use App\Models\DoctorWallet;
use App\Models\Notification;
use App\Models\MedicalCenter;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\HasSelectedDoctor;

class HeaderComponent extends Component
{
    use HasSelectedDoctor;

    public $walletBalance = 0;
    public $notifications;
    public $unreadCount = 0;
    public $selectedDoctorId = null;
    public $selectedDoctorName = 'انتخاب پزشک';
    public $doctors;
    public $dropdownOpen = false;

    public function mount()
    {
        $this->notifications = new Collection();
        $this->doctors = new Collection(); // Initialize doctors

        if (Auth::guard('medical_center')->check()) {
            /** @var MedicalCenter $medicalCenter */
            $medicalCenter = Auth::guard('medical_center')->user();

            // بارگذاری پزشکان مرکز درمانی
            $this->loadDoctors($medicalCenter);
            // تنظیم پزشک انتخاب‌شده
            $this->setSelectedDoctorFromDatabase($medicalCenter);

            // لود اعلان‌ها برای مرکز درمانی
            $medicalCenterNotifications = NotificationRecipient::where('recipient_type', 'App\\Models\\MedicalCenter')
                ->where('recipient_id', $medicalCenter->id)
                ->where('is_read', false)
                ->with('notification')
                ->get();

            $singleNotifications = NotificationRecipient::where('recipient_type', 'phone')
                ->where('phone_number', $medicalCenter->phone_number)
                ->where('is_read', false)
                ->with('notification')
                ->get();

            $this->notifications = $medicalCenterNotifications->merge($singleNotifications);
        }

        $this->unreadCount = $this->notifications->count();
    }

    /**
     * بارگذاری پزشکان مرکز درمانی
     */
    protected function loadDoctors(MedicalCenter $medicalCenter)
    {
        $this->doctors = $medicalCenter->doctors()
            ->select('doctors.*')
            ->where('doctors.status', true)
            ->with(['specialties'])
                ->get();
    }

    /**
     * باز کردن دراپ‌داون
     */
    public function openDropdown()
    {
        $this->dropdownOpen = true;
    }

    /**
     * بستن دراپ‌داون
     */
    public function closeDropdown()
    {
        $this->dropdownOpen = false;
    }

    /**
     * تغییر وضعیت دراپ‌داون
     */
    public function toggleDropdown()
    {
        $this->dropdownOpen = !$this->dropdownOpen;
    }

    /**
     * تنظیم پزشک انتخاب‌شده از دیتابیس
     */
    protected function setSelectedDoctorFromDatabase(MedicalCenter $medicalCenter)
    {
        // اگر مرکز درمانی پزشک انتخاب‌شده‌ای دارد، از آن استفاده کن
        if ($medicalCenter->selectedDoctor) {
            if ($medicalCenter->selectedDoctor->doctor_id) {
                $this->selectedDoctorId = $medicalCenter->selectedDoctor->doctor_id;
                $this->selectedDoctorName = $medicalCenter->selectedDoctor->doctor->first_name . ' ' . $medicalCenter->selectedDoctor->doctor->last_name;
            } else {
                // هیچ پزشکی انتخاب نشده
                $this->selectedDoctorId = null;
                $this->selectedDoctorName = 'انتخاب پزشک';
            }
            return;
        }

        // اگر رکوردی وجود ندارد، بررسی کن که آیا پزشک فعالی دارد یا نه
        $activeDoctors = $medicalCenter->doctors()
            ->where('doctors.status', true)
                ->get();

        if ($activeDoctors->count() > 0) {
            // اولین پزشک فعال را انتخاب کن
            $firstActiveDoctor = $activeDoctors->first();
            // پزشک انتخاب‌شده را در دیتابیس ذخیره کن
            $medicalCenter->setSelectedDoctor($firstActiveDoctor->id);
            $medicalCenter->refresh();

            $this->selectedDoctorId = $firstActiveDoctor->id;
            $this->selectedDoctorName = $firstActiveDoctor->first_name . ' ' . $firstActiveDoctor->last_name;
        } else {
            // هیچ پزشک فعالی ندارد
            $this->selectedDoctorId = null;
            $this->selectedDoctorName = 'انتخاب پزشک';

            // در دیتابیس رکورد با doctor_id = null ایجاد کن
            $medicalCenter->setSelectedDoctor(null);
        }
    }

    /**
     * انتخاب پزشک
     */
    public function selectDoctor($doctorId = null)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        if ($medicalCenter) {
            // اعتبارسنجی پزشک
            if ($doctorId && !$medicalCenter->doctors()->where('doctors.id', $doctorId)->where('doctors.status', true)->exists()) {
                $this->addError('doctor', 'پزشک انتخاب‌شده معتبر نیست.');
                return;
            }

            // ذخیره پزشک انتخاب‌شده
            $medicalCenter->setSelectedDoctor($doctorId);

            // به‌روزرسانی مقادیر
            $this->selectedDoctorId = $doctorId;

            if ($doctorId) {
                /** @var Doctor $doctor */
                $doctor = $medicalCenter->doctors()->where('doctors.status', true)->find($doctorId);
                $this->selectedDoctorName = $doctor->first_name . ' ' . $doctor->last_name;
            } else {
                $this->selectedDoctorName = 'انتخاب پزشک';
            }

            // بستن دراپ‌داون
            $this->dropdownOpen = false;

            // اطلاع‌رسانی به سایر کامپوننت‌ها
            $this->dispatch('doctorSelected', ['doctorId' => $doctorId]);

            // ارسال رویداد برای ریلود صفحه بعد از چند ثانیه
            $this->dispatch('reloadPageAfterDelay', ['delay' => 3000]); // 3 ثانیه تاخیر
        }
    }

    public function markAsRead($recipientId)
    {
        $recipient = NotificationRecipient::find($recipientId);
        if ($recipient) {
            $recipient->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            $this->notifications = $this->notifications->filter(fn ($item) => $item->id != $recipientId);
            $this->unreadCount = $this->notifications->count();
        }
    }

    public function render()
    {
        return view('livewire.mc.panel.layouts.partials.header-component');
    }
}
