<?php

namespace App\Livewire\Admin\Panel\Bestdoctors;

use App\Models\BestDoctor;
use App\Models\Doctor;
use App\Models\Clinic;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class BestDoctorCreate extends Component
{
    public $doctor_id;
    public $clinic_id;
    public $best_doctor     = false;
    public $best_consultant = false;
    public $status          = true;

    public $doctors;
    public $clinics = [];

    public function mount()
    {
        $this->doctors = Doctor::all();
    }

    public function loadClinics()
    {
        Log::info('Loading clinics for doctor_id: ' . $this->doctor_id);

        if ($this->doctor_id) {
            $clinics = Clinic::where('doctor_id', $this->doctor_id)->get();
            Log::info('Found clinics:', ['count' => $clinics->count(), 'clinics' => $clinics->toArray()]);
            $this->clinics = $clinics;

            // ارسال داده‌ها با فرمت صحیح برای Select2
            $this->dispatch('clinics-updated', clinics: $clinics->map(function ($clinic) {
                return [
                    'id' => $clinic->id,
                    'text' => $clinic->name
                ];
            })->toArray());
        } else {
            Log::info('No doctor_id provided, returning empty collection');
            $this->clinics = collect();
            $this->dispatch('clinics-updated', clinics: []);
        }
    }

    public function updatedDoctorId($value)
    {
        Log::info('Doctor ID updated:', ['old_value' => $this->doctor_id, 'new_value' => $value]);
        $this->doctor_id = $value;
        $this->clinic_id = null;
        $this->loadClinics();
    }

    protected function rules()
    {
        return [
            'doctor_id'       => [
                'required',
                'exists:doctors,id',
                'unique:best_doctors,doctor_id,NULL,id,clinic_id,' . ($this->clinic_id ?? 'NULL'),
            ],
            'clinic_id'       => 'nullable|exists:clinics,id',
            'best_doctor'     => 'boolean',
            'best_consultant' => 'boolean',
            'status'          => 'boolean',
        ];
    }

    protected $messages = [
        'doctor_id.required' => 'لطفاً یک پزشک انتخاب کنید.',
        'doctor_id.unique'   => 'این پزشک با این کلینیک قبلاً ثبت شده است.',
        'clinic_id.exists'   => 'کلینیک انتخاب‌شده معتبر نیست.',
    ];

    public function store()
    {
        try {
            $this->validate();
            BestDoctor::create([
                'doctor_id'       => $this->doctor_id,
                'clinic_id'       => $this->clinic_id,
                'best_doctor'     => $this->best_doctor,
                'best_consultant' => $this->best_consultant,
                'status'          => $this->status,
            ]);
            $this->dispatch('show-alert', type: 'success', message: 'بهترین پزشک با موفقیت اضافه شد!');
            $this->reset(['doctor_id', 'clinic_id', 'best_doctor', 'best_consultant', 'status']);
            $this->clinics = [];
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
