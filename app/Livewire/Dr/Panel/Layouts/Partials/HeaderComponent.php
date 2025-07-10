<?php

namespace App\Livewire\Dr\Panel\Layouts\Partials;

use Livewire\Component;
use App\Models\DoctorWallet;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Collection;

class HeaderComponent extends Component
{
    public $walletBalance = 0;
    public $notifications;
    public $unreadCount = 0;
    public $selectedClinicId = null;
    public $selectedClinicName = 'مشاوره آنلاین به نوبه';
    public $clinics = [];

    public function mount()
    {
        $this->notifications = new Collection();

        if (Auth::guard('doctor')->check()) {
            $doctor = Auth::guard('doctor')->user();
            $doctorId = $doctor->id;
            $doctorMobile = $doctor->mobile;


            // بارگذاری کلینیک‌ها
            $this->loadClinics($doctor);

            // تنظیم کلینیک انتخاب‌شده
            $this->setSelectedClinicFromDatabase($doctor);

            $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
                ->sum('balance');

            // لود اعلان‌ها برای پزشک
            $doctorNotifications = NotificationRecipient::where('recipient_type', 'App\\Models\\Doctor')
                ->where('recipient_id', $doctorId)
                ->where('is_read', false)
                ->with('notification')
                ->get();

            $singleNotifications = NotificationRecipient::where('recipient_type', 'phone')
                ->where('phone_number', $doctorMobile)
                ->where('is_read', false)
                ->with('notification')
                ->get();

            $this->notifications = $doctorNotifications->merge($singleNotifications);
        } elseif (Auth::guard('secretary')->check()) {
            $secretary = Auth::guard('secretary')->user();
            $doctorId = $secretary->doctor_id;
            $secretaryMobile = $secretary->mobile;

            // بارگذاری کلینیک‌ها
            $this->loadClinics($secretary->doctor);

            // تنظیم کلینیک انتخاب‌شده
            $this->setSelectedClinicFromDatabase($secretary->doctor);

            if ($doctorId) {
                $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
                    ->sum('balance');
            }

            $secretaryNotifications = NotificationRecipient::where('recipient_type', 'App\\Models\\Secretary')
                ->where('recipient_id', $secretary->id)
                ->where('is_read', false)
                ->with('notification')
                ->get();

            $singleNotifications = NotificationRecipient::where('recipient_type', 'phone')
                ->where('phone_number', $secretaryMobile)
                ->where('is_read', false)
                ->with('notification')
                ->get();

            $this->notifications = $secretaryNotifications->merge($singleNotifications);
        }

        $this->unreadCount = $this->notifications->count();
    }

    protected function loadClinics($doctor)
    {
        if ($doctor) {
            $this->clinics = $doctor->clinics()->select('id', 'name', 'is_active', 'province_id', 'city_id')->with(['province', 'city'])->get();
        }
    }

    protected function setSelectedClinicFromDatabase($doctor)
    {
        if ($doctor) {
            // اگر دکتر کلینیک انتخاب‌شده‌ای دارد، از آن استفاده کن
            if ($doctor->selectedClinic) {
                $this->selectedClinicId = $doctor->selectedClinic->clinic_id;
                $this->selectedClinicName = $this->selectedClinicId
                    ? $doctor->selectedClinic->clinic->name
                    : 'مشاوره آنلاین به نوبه';
                return;
            }

            // اگر کلینیک انتخاب‌شده‌ای ندارد، بررسی کن که آیا کلینیک فعالی دارد یا نه
            $activeClinics = $this->clinics->where('is_active', true);

            if ($activeClinics->count() > 0) {
                // اولین کلینیک فعال را انتخاب کن
                $firstActiveClinic = $activeClinics->first();
                // کلینیک انتخاب‌شده را در دیتابیس ذخیره کن
                $doctor->setSelectedClinic($firstActiveClinic->id);
                $doctor->refresh();
                $this->selectedClinicId = $doctor->selectedClinic->clinic_id;
                $this->selectedClinicName = $doctor->selectedClinic->clinic->name;
            } else {
                // هیچ کلینیک فعالی ندارد، روی مشاوره آنلاین بگذار
                $this->selectedClinicId = null;
                $this->selectedClinicName = 'مشاوره آنلاین به نوبه';

                // در دیتابیس هم null ذخیره کن
                $doctor->setSelectedClinic(null);
            }
        }
    }

    public function selectClinic($clinicId = null)
    {
        $doctor = Auth::guard('doctor')->check()
            ? Auth::guard('doctor')->user()
            : Auth::guard('secretary')->user()->doctor;

        if ($doctor) {
            // اعتبارسنجی کلینیک
            if ($clinicId && !$doctor->clinics()->where('id', $clinicId)->exists()) {
                $this->addError('clinic', 'کلینیک انتخاب‌شده معتبر نیست.');
                return;
            }

            // ذخیره کلینیک انتخاب‌شده
            $doctor->setSelectedClinic($clinicId);

            // به‌روزرسانی مقادیر
            $this->selectedClinicId = $clinicId;
            $this->selectedClinicName = $clinicId
                ? $doctor->clinics()->find($clinicId)->name
                : 'مشاوره آنلاین به نوبه';

            // اطلاع‌رسانی به سایر کامپوننت‌ها
            $this->dispatch('clinicSelected', ['clinicId' => $clinicId]);
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
        return view('livewire.dr.panel.layouts.partials.header-component');
    }
}
