<?php

namespace App\Livewire\Mc\Panel\DoctorPrescriptions;

use Livewire\Component;
use App\Models\PrescriptionRequest;
use Illuminate\Support\Facades\Auth;

class PrescriptionSettings extends Component
{
    public $request_enabled = false;
    public $enabled_types = [];
    public $all_types = [
        'renew_lab' => 'آزمایش',
        'renew_drug' => 'دارو',
        'renew_insulin' => 'انسولین',
        'sonography' => 'سونوگرافی',
        'mri' => 'MRI',
        'other' => 'سایر',
    ];

    public function mount()
    {
        $doctor = Auth::guard('doctor')->user();
        $settings = PrescriptionRequest::where('doctor_id', $doctor->id)->first();
        if ($settings) {
            $this->request_enabled = $settings->request_enabled;
            $this->enabled_types = $settings->enabled_types ?: [];
        }
    }

    public function updatedRequestEnabled($value)
    {
        $this->saveSettings();
    }

    public function updatedEnabledTypes()
    {
        $this->saveSettings();
    }

    public function saveSettings()
    {
        $doctor = Auth::guard('doctor')->user();
        $settings = PrescriptionRequest::where('doctor_id', $doctor->id)->first();
        if (!$settings) {
            $settings = new PrescriptionRequest();
            $settings->doctor_id = $doctor->id;
            $settings->requestable_type = 'App\Models\Doctor';
            $settings->requestable_id = $doctor->id;
        }
        $settings->request_enabled = $this->request_enabled ? 1 : 0;
        $settings->enabled_types = $this->enabled_types;
        $settings->save();
        $this->dispatch('show-alert', type: 'success', message: 'تنظیمات با موفقیت ذخیره شد.');
    }

    public function render()
    {
        return view('livewire.mc.panel.doctor-prescriptions.prescription-settings');
    }
}
