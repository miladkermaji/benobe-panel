<?php

namespace App\Livewire\Admin\Panel\Bestdoctors;

use App\Models\BestDoctor;
use App\Models\Doctor;
use App\Models\Hospital;
use Livewire\Component;

class BestDoctorCreate extends Component
{
    public $doctor_id;
    public $hospital_id;
    public $best_doctor     = false;
    public $best_consultant = false;
    public $status          = true;

    public $doctors;
    public $hospitals;

    public function mount()
    {
        $this->doctors   = Doctor::all();
        $this->hospitals = Hospital::all();
    }

    protected function rules()
    {
        return [
            'doctor_id'       => [
                'required',
                'exists:doctors,id',
                'unique:best_doctors,doctor_id,NULL,id,hospital_id,' . ($this->hospital_id ?? 'NULL'),
            ],
            'hospital_id'     => 'nullable|exists:hospitals,id',
            'best_doctor'     => 'boolean',
            'best_consultant' => 'boolean',
            'status'          => 'boolean',
        ];
    }

    protected $messages = [
        'doctor_id.required' => 'لطفاً یک پزشک انتخاب کنید.',
        'doctor_id.unique'   => 'این پزشک با این بیمارستان قبلاً ثبت شده است.',
        'hospital_id.exists' => 'بیمارستان انتخاب‌شده معتبر نیست.',
    ];

    public function store()
    {
        try {
            $this->validate();
            BestDoctor::create([
                'doctor_id'       => $this->doctor_id,
                'hospital_id'     => $this->hospital_id,
                'best_doctor'     => $this->best_doctor,
                'best_consultant' => $this->best_consultant,
                'status'          => $this->status,
            ]);
            $this->dispatch('show-alert', type: 'success', message: 'بهترین پزشک با موفقیت اضافه شد!');
            $this->reset(['doctor_id', 'hospital_id', 'best_doctor', 'best_consultant', 'status']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $this->dispatch('show-alert', type: 'error', message: $errors[0]);
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.best-doctors.best-doctor-create');
    }
}
