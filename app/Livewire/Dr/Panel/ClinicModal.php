<?php

namespace App\Livewire\Dr\Panel;

use Livewire\Component;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ClinicModal extends Component
{
    public $showModal = false;
    public $workSchedules = [];
    public $doctorId;

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
            $this->showModal = true;
            $this->loadWorkSchedules();

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
