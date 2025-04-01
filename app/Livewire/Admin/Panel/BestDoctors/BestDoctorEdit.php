<?php

namespace App\Livewire\Admin\Panel\Bestdoctors;

use App\Models\BestDoctor;
use App\Models\Doctor;
use App\Models\Hospital;
use Livewire\Component;

class BestDoctorEdit extends Component
{
    public $bestdoctor;
    public $doctor_id;
    public $hospital_id;
    public $best_doctor;
    public $best_consultant;
    public $status;

    public $doctors;
    public $hospitals;

    public function mount($bestdoctorId)
    {
        $this->bestdoctor = BestDoctor::findOrFail($bestdoctorId);

        $this->doctor_id       = $this->best_doctor->doctor_id;
        $this->hospital_id     = $this->best_doctor->hospital_id;
        $this->best_doctor     = $this->best_doctor->best_doctor;
        $this->best_consultant = $this->best_doctor->best_consultant;
        $this->status          = $this->best_doctor->status;

        $this->doctors   = Doctor::all();
        $this->hospitals = Hospital::all();
    }

    public function update()
    {
        $this->validate([
            'doctor_id'       => [
                'required',
                'exists:doctors,id',
                "unique:best_doctors,doctor_id,{$this->best_doctor->id},id,hospital_id," . ($this->hospital_id ?? 'NULL'),
            ],
            'hospital_id'     => 'nullable|exists:hospitals,id',
            'best_doctor'     => 'boolean',
            'best_consultant' => 'boolean',
            'status'          => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً یک پزشک انتخاب کنید.',
            'doctor_id.exists'   => 'پزشک انتخاب‌شده معتبر نیست.',
            'doctor_id.unique'   => 'این پزشک با این بیمارستان قبلاً ثبت شده است.',
            'hospital_id.exists' => 'بیمارستان انتخاب‌شده معتبر نیست.',
        ]);

        $this->best_doctor->doctor_id       = $this->doctor_id;
        $this->best_doctor->hospital_id     = $this->hospital_id;
        $this->best_doctor->best_doctor     = $this->best_doctor ?? false;
        $this->best_doctor->best_consultant = $this->best_consultant ?? false;
        $this->best_doctor->status          = $this->status ?? false;
        $updated                           = $this->best_doctor->save();

        if ($updated) {
            $this->dispatch('show-alert', type: 'success', message: 'بهترین پزشک با موفقیت ویرایش شد!');
            $this->dispatch('refresh-index');
            return redirect()->route('admin.panel.best_doctors.index');
        } else {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره‌سازی رخ داد!');
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.best-doctors.best-doctor-edit');
    }
}
