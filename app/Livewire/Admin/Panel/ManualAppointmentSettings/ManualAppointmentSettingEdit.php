<?php

namespace App\Livewire\Admin\Panel\ManualAppointmentSettings;

use Livewire\Component;
use App\Models\ManualAppointmentSetting;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Validator;

class ManualAppointmentSettingEdit extends Component
{
    public $setting;
    public $doctor_id;
    public $clinic_id;
    public $is_active;
    public $duration_send_link;
    public $duration_confirm_link;
    public $doctors;
    public $clinics;

    public function mount($id)
    {
        $this->setting = ManualAppointmentSetting::with(['doctor', 'clinic'])->findOrFail($id);
        $this->doctor_id = $this->setting->doctor_id;
        $this->clinic_id = $this->setting->clinic_id;
        $this->is_active = $this->setting->is_active;
        $this->duration_send_link = $this->setting->duration_send_link;
        $this->duration_confirm_link = $this->setting->duration_confirm_link;

        $this->doctors = Doctor::with('specialty')->get();
        $this->loadClinics();
    }

    protected function loadClinics()
    {
        $clinics = MedicalCenter::where('type', 'policlinic')
            ->get()
            ->map(function ($clinic) {
                return [
                    'id' => $clinic->id,
                    'name' => $clinic->name
                ];
            })
            ->toArray();

        // Add general settings option
        $generalSetting = [
            'id' => null,
            'name' => 'تنظیمات عمومی (برای همه کلینیک‌ها)'
        ];

        $this->clinics = array_merge([$generalSetting], $clinics);
    }

    public function update()
    {
        $validator = Validator::make([
            'doctor_id' => $this->doctor_id,
            'clinic_id' => $this->clinic_id,
            'is_active' => $this->is_active,
            'duration_send_link' => $this->duration_send_link,
            'duration_confirm_link' => $this->duration_confirm_link,
        ], [
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'nullable|exists:medical_centers,id',
            'is_active' => 'required|boolean',
            'duration_send_link' => 'required|integer|min:1',
            'duration_confirm_link' => 'required|integer|min:1',
        ], [
            'doctor_id.required' => 'انتخاب پزشک الزامی است.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'is_active.required' => 'وضعیت تأیید دو مرحله‌ای الزامی است.',
            'is_active.boolean' => 'وضعیت تأیید دو مرحله‌ای باید بله یا خیر باشد.',
            'duration_send_link.required' => 'زمان ارسال لینک الزامی است.',
            'duration_send_link.integer' => 'زمان ارسال لینک باید عدد صحیح باشد.',
            'duration_send_link.min' => 'زمان ارسال لینک باید حداقل ۱ ساعت باشد.',
            'duration_confirm_link.required' => 'مدت اعتبار لینک الزامی است.',
            'duration_confirm_link.integer' => 'مدت اعتبار لینک باید عدد صحیح باشد.',
            'duration_confirm_link.min' => 'مدت اعتبار لینک باید حداقل ۱ ساعت باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        // بررسی یکتایی تنظیمات (به جز رکورد فعلی)
        $exists = ManualAppointmentSetting::where('doctor_id', $this->doctor_id)
            ->where('clinic_id', $this->clinic_id)
            ->where('id', '!=', $this->setting->id)
            ->exists();

        if ($exists) {
            $message = $this->clinic_id
                ? 'این پزشک قبلاً برای این کلینیک تنظیمات دارد.'
                : 'این پزشک قبلاً تنظیمات عمومی دارد.';

            $this->dispatch('show-alert', type: 'error', message: $message);
            return;
        }

        $this->setting->update([
            'doctor_id' => $this->doctor_id,
            'clinic_id' => $this->clinic_id,
            'is_active' => $this->is_active,
            'duration_send_link' => $this->duration_send_link,
            'duration_confirm_link' => $this->duration_confirm_link,
        ]);

        $this->dispatch(
            'show-alert',
            type: 'success',
            message: 'تنظیمات نوبت دستی با موفقیت به‌روزرسانی شد!'
        );
        return redirect()->route('admin.panel.manual-appointment-settings.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.manual-appointment-settings.manual-appointment-setting-edit');
    }
}
