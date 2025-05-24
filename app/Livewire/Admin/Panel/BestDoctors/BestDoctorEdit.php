<?php

namespace App\Livewire\Admin\Panel\Bestdoctors;

use App\Models\BestDoctor;
use App\Models\Doctor;
use App\Models\Clinic;
use Livewire\Component;

class BestDoctorEdit extends Component
{
    public $bestdoctor;
    public $doctor_id;
    public $clinic_id;
    public $best_doctor;
    public $best_consultant;
    public $status;

    public $doctors;
    public $clinics = [];

    public function mount($bestdoctorId)
    {
        $this->bestdoctor = BestDoctor::findOrFail($bestdoctorId);

        $this->doctor_id       = $this->bestdoctor->doctor_id;
        $this->clinic_id       = $this->bestdoctor->clinic_id;
        $this->best_doctor     = $this->bestdoctor->best_doctor;
        $this->best_consultant = $this->bestdoctor->best_consultant;
        $this->status          = $this->bestdoctor->status;

        $this->doctors = Doctor::all();
        $this->loadClinics();
    }

    public function loadClinics()
    {

        if ($this->doctor_id) {
            $clinics = Clinic::where('doctor_id', $this->doctor_id)->get();

            $this->clinics = $clinics;

            // ارسال داده‌ها با فرمت صحیح برای Select2
            $this->dispatch('clinics-updated', clinics: $clinics->map(function ($clinic) {
                return [
                    'id' => $clinic->id,
                    'text' => $clinic->name
                ];
            })->toArray());
        } else {
            $this->clinics = collect();
            $this->dispatch('clinics-updated', clinics: []);
        }
    }

    public function updatedDoctorId($value)
    {
        $this->doctor_id = $value;
        $this->clinic_id = null;
        $this->loadClinics();
    }

    public function update()
    {
        $this->validate([
            'doctor_id'       => [
                'required',
                'exists:doctors,id',
                "unique:best_doctors,doctor_id,{$this->bestdoctor->id},id,clinic_id," . ($this->clinic_id ?? 'NULL'),
            ],
            'clinic_id'       => 'nullable|exists:clinics,id',
            'best_doctor'     => 'boolean',
            'best_consultant' => 'boolean',
            'status'          => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً یک پزشک انتخاب کنید.',
            'doctor_id.exists'   => 'پزشک انتخاب‌شده معتبر نیست.',
            'doctor_id.unique'   => 'این پزشک با این کلینیک قبلاً ثبت شده است.',
            'clinic_id.exists'   => 'کلینیک انتخاب‌شده معتبر نیست.',
        ]);

        $this->bestdoctor->doctor_id       = $this->doctor_id;
        $this->bestdoctor->clinic_id       = $this->clinic_id;
        $this->bestdoctor->best_doctor     = $this->best_doctor ?? false;
        $this->bestdoctor->best_consultant = $this->best_consultant ?? false;
        $this->bestdoctor->status          = $this->status ?? false;
        $updated                           = $this->bestdoctor->save();

        if ($updated) {
            $this->dispatch('show-alert', type: 'success', message: 'بهترین پزشک با موفقیت ویرایش شد!');
            $this->dispatch('refresh-index');
            return redirect()->route('admin.panel.best-doctors.index');
        } else {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره‌سازی رخ داد!');
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.best-doctors.best-doctor-edit');
    }
}
