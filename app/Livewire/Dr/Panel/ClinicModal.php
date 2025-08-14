<?php

namespace App\Livewire\Dr\Panel;

use Livewire\Component;
use App\Models\DoctorWorkSchedule;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ClinicModal extends Component
{
    public $showModal = false;
    public $workSchedules = [];
    public $doctorId;

    // New state derived from session/middleware
    public $hasClinic = false;
    public $needsWorkHoursAssignment = false;
    public $needsClinicCreation = false;
    public $clinicType = null;
    public $policlinicCenter = null; // MedicalCenter instance for doctor's policlinic

    protected $listeners = [
        'refreshClinicModal' => '$refresh',
        'clinicCreated' => 'onClinicCreated'
    ];

    public function mount()
    {
        $this->checkAndShowModal();
    }

    public function checkAndShowModal()
    {
        // بررسی session برای نمایش مودال
        if (Session::get('show_clinic_modal')) {
            // Load context
            $context = Session::get('doctor_work_schedule_data', []);
            $this->hasClinic = (bool)($context['has_clinic'] ?? false);
            $this->needsWorkHoursAssignment = (bool)($context['needs_work_hours_assignment'] ?? false);
            $this->needsClinicCreation = (bool)($context['needs_clinic_creation'] ?? false);
            $this->clinicType = $context['clinic_type'] ?? null;

            $this->showModal = true;
            $this->loadWorkSchedules();
            $this->loadDoctorPoliclinic();

            // حذف session بعد از نمایش
            Session::forget('show_clinic_modal');
        }
    }

    public function loadWorkSchedules()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $this->doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        // دریافت ساعات کاری بدون مطب
        $this->workSchedules = DoctorWorkSchedule::where('doctor_id', $this->doctorId)
            ->where('is_working', true)
            ->whereNull('medical_center_id')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'day' => $this->getDayNameInPersian($schedule->day),
                    'english_day' => $schedule->day,
                    'work_hours' => $this->normalizeWorkHours($schedule->work_hours),
                    'is_working' => $schedule->is_working
                ];
            })
            ->toArray();
    }

    private function loadDoctorPoliclinic(): void
    {
        if (!$this->doctorId) {
            return;
        }

        $this->policlinicCenter = MedicalCenter::whereHas('doctors', function ($q) {
            $q->where('doctor_id', $this->doctorId);
        })
            ->where('type', 'policlinic')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->first();
    }

    private function normalizeWorkHours($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    private function getDayNameInPersian($day)
    {
        $days = [
            'saturday'  => 'شنبه',
            'sunday'    => 'یکشنبه',
            'monday'    => 'دوشنبه',
            'tuesday'   => 'سه‌شنبه',
            'wednesday' => 'چهارشنبه',
            'thursday'  => 'پنج‌شنبه',
            'friday'    => 'جمعه',
        ];

        return $days[$day] ?? $day;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function goToCreateClinic()
    {
        // هدایت به صفحه مدیریت مطب‌ها
        return redirect()->route('dr-clinic-management');
    }

    public function onClinicCreated($clinicId)
    {
        // وقتی مطب ایجاد شد، مودال رو ببند
        $this->showModal = false;

        // به‌روزرسانی ساعات کاری با مطب جدید
        $this->updateWorkSchedulesWithClinic($clinicId);
    }

    public function assignToMyClinic()
    {
        if (!$this->policlinicCenter || !$this->doctorId) {
            $this->dispatch('show-toastr', type: 'error', message: 'مطب فعال برای تخصیص یافت نشد.');
            return;
        }

        $updated = DoctorWorkSchedule::where('doctor_id', $this->doctorId)
            ->where('is_working', true)
            ->whereNull('medical_center_id')
            ->update(['medical_center_id' => $this->policlinicCenter->id]);

        if ($updated > 0) {
            $this->dispatch('show-toastr', type: 'success', message: 'ساعات کاری با موفقیت به مطب تخصیص یافت.');
        } else {
            $this->dispatch('show-toastr', type: 'info', message: 'ساعات کاری بدون مطب برای تخصیص یافت نشد.');
        }

        $this->showModal = false;
        return redirect()->route('dr-clinic-management');
    }

    private function updateWorkSchedulesWithClinic($clinicId)
    {
        // به‌روزرسانی ساعات کاری بدون مطب
        DoctorWorkSchedule::where('doctor_id', $this->doctorId)
            ->where('is_working', true)
            ->whereNull('medical_center_id')
            ->update(['medical_center_id' => $clinicId]);
    }

    public function render()
    {
        return view('livewire.dr.panel.clinic-modal');
    }
}
