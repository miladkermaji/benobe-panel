<?php

namespace App\Livewire\Admin\Panel\ClinicDepositSettings;

use Livewire\Component;
use App\Models\ClinicDepositSetting;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClinicDepositSettingsEdit extends Component
{
    public $clinicDepositSetting;
    public $form = [];
    public $doctors;
    public $clinics;

    public function mount($id)
    {
        $this->clinicDepositSetting = ClinicDepositSetting::findOrFail($id);
        $this->form = $this->clinicDepositSetting->toArray();
        $this->form['no_deposit'] = $this->form['deposit_amount'] == 0;
        $this->doctors = Doctor::all();
        $this->clinics = MedicalCenter::whereHas('doctors', function ($query) {
            $query->where('doctor_id', $this->form['doctor_id']);
        })->where('type', 'policlinic')->get();

        // Dispatch initial clinics data
        $this->dispatch('clinics-updated', clinics: $this->clinics->map(function ($clinic) {
            return ['id' => $clinic->id, 'text' => $clinic->name];
        })->toArray());
    }

    public function updatedFormDoctorId($value)
    {
        $this->clinics = MedicalCenter::whereHas('doctors', function ($query) use ($value) {
            $query->where('doctor_id', $value);
        })->where('type', 'policlinic')->get();
        $this->form['clinic_id'] = '';

        $this->dispatch('clinics-updated', clinics: $this->clinics->map(function ($clinic) {
            return ['id' => $clinic->id, 'text' => $clinic->name];
        })->toArray());
    }

    public function updatedFormNoDeposit($value)
    {
        if ($value) {
            $this->form['deposit_amount'] = 0;
        } else {
            $this->form['deposit_amount'] = '';
        }
    }

    public function update()
    {
        try {
            $validator = Validator::make($this->form, [
                'doctor_id' => 'required|exists:doctors,id',
                'clinic_id' => 'nullable|exists:medical_centers,id',
                'deposit_amount' => 'required|numeric|min:0',
                'no_deposit' => 'boolean',
                'notes' => 'nullable|string|max:500',
            ], [
                'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
                'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
                'clinic_id.exists' => 'مطب انتخاب‌شده معتبر نیست.',
                'deposit_amount.required' => 'مبلغ بیعانه الزامی است.',
                'deposit_amount.numeric' => 'مبلغ بیعانه باید عددی باشد.',
                'deposit_amount.min' => 'مبلغ بیعانه نمی‌تواند منفی باشد.',
                'notes.max' => 'یادداشت نمی‌تواند بیش از ۵۰۰ کاراکتر باشد.',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->dispatch('show-alert', type: 'error', message: $error);
                }
                return;
            }

            $data = $this->form;
            $data['clinic_id'] = $data['clinic_id'] === '' ? null : $data['clinic_id'];
            unset($data['no_deposit']);

            $this->clinicDepositSetting->update($data);

            $this->dispatch('show-alert', type: 'success', message: 'تنظیم بیعانه با موفقیت به‌روزرسانی شد!');
            return redirect()->route('admin.panel.clinic-deposit-settings.index');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطایی در به‌روزرسانی تنظیمات رخ داد. لطفاً دوباره تلاش کنید.');
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.clinic-deposit-settings.clinic-deposit-setting-edit');
    }
}
