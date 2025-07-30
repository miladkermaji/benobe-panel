<?php

namespace App\Livewire\Admin\Panel\ClinicDepositSettings;

use Livewire\Component;
use App\Models\ClinicDepositSetting;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClinicDepositSettingsCreate extends Component
{
    public $form = [
        'doctor_id' => '',
        'medical_center_id' => '',
        'deposit_amount' => '',
        'no_deposit' => false,
        'notes' => '',
    ];
    public $doctors;
    public $clinics;

    public function mount()
    {
        $this->doctors = Doctor::all();
        $this->clinics = collect();
    }

    public function updatedFormDoctorId($value)
    {
        $this->clinics = MedicalCenter::whereHas('doctors', function ($query) use ($value) {
            $query->where('doctor_id', $value);
        })->where('type', 'policlinic')->get();
        $this->form['medical_center_id'] = '';

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

    public function store()
    {
        try {
            $validator = Validator::make($this->form, [
                'doctor_id' => 'required|exists:doctors,id',
                'medical_center_id' => 'nullable|exists:medical_centers,id',
                'deposit_amount' => 'required|numeric|min:0',
                'no_deposit' => 'boolean',
                'notes' => 'nullable|string|max:500',
            ], [
                'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
                'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
                'medical_center_id.exists' => 'مطب انتخاب‌شده معتبر نیست.',
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
            $data['medical_center_id'] = $data['medical_center_id'] === '' ? null : $data['medical_center_id'];
            unset($data['no_deposit']);

            ClinicDepositSetting::create($data);

            $this->dispatch('show-alert', type: 'success', message: 'تنظیم بیعانه با موفقیت ایجاد شد!');
            return redirect()->route('admin.panel.clinic-deposit-settings.index');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطایی در ثبت تنظیمات رخ داد. لطفاً دوباره تلاش کنید.');
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.clinic-deposit-settings.clinic-deposit-setting-create');
    }
}
