<?php

namespace App\Livewire\Mc\Panel\DoctorPrescriptions;

use Livewire\Component;
use App\Models\PrescriptionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        }

        if (!$doctorId) {
            return response()->json(['success' => false, 'message' => 'پزشک انتخاب نشده است.'], 400);
        }

        $settings = PrescriptionRequest::where('doctor_id', $doctorId)->first();
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
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
            $doctor = Auth::guard('doctor')->user();
            $doctorId = $doctor ? $doctor->id : null;
        }

        if (!$doctorId) {
            return response()->json(['success' => false, 'message' => 'پزشک انتخاب نشده است.'], 400);
        }

        $settings = PrescriptionRequest::where('doctor_id', $doctorId)->first();
        if (!$settings) {
            $settings = new PrescriptionRequest();
            $settings->doctor_id = $doctorId;
            $settings->requestable_type = 'App\Models\Doctor';
            $settings->requestable_id = $doctorId;
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
