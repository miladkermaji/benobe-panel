<?php

namespace App\Livewire\Admin\Panel\DoctorSpecialties;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorSpecialty;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\AcademicDegree;

class DoctorSpecialtyEdit extends Component
{
    public $specialty;
    public $doctor_id;
    public $specialty_id;
    public $academic_degree_id;
    public $specialty_title;
    public $is_main;

    public $doctors;
    public $specialties;
    public $academicDegrees;

    public function mount($id)
    {
        $this->specialty = DoctorSpecialty::findOrFail($id);
        $this->doctor_id = $this->specialty->doctor_id;
        $this->specialty_id = $this->specialty->specialty_id;
        $this->academic_degree_id = $this->specialty->academic_degree_id;
        $this->specialty_title = $this->specialty->specialty_title;
        $this->is_main = $this->specialty->is_main;

        $this->doctors = Doctor::select('id', 'first_name', 'last_name')->get();
        $this->specialties = Specialty::select('id', 'name')->where('status', 1)->get();
        $this->academicDegrees = AcademicDegree::select('id', 'title')->get();
    }

    public function update()
    {
        $validator = Validator::make([
            'doctor_id' => $this->doctor_id,
            'specialty_id' => $this->specialty_id,
            'academic_degree_id' => $this->academic_degree_id,
            'specialty_title' => $this->specialty_title,
            'is_main' => $this->is_main,
        ], [
            'doctor_id' => 'required|exists:doctors,id',
            'specialty_id' => 'required|exists:specialties,id',
            'academic_degree_id' => 'nullable|exists:academic_degrees,id',
            'specialty_title' => 'nullable|string|max:255',
            'is_main' => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'specialty_id.required' => 'لطفاً تخصص را انتخاب کنید.',
            'specialty_id.exists' => 'تخصص انتخاب‌شده معتبر نیست.',
            'academic_degree_id.exists' => 'درجه علمی انتخاب‌شده معتبر نیست.',
            'specialty_title.string' => 'عنوان تخصص باید یک رشته متنی باشد.',
            'specialty_title.max' => 'عنوان تخصص نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'is_main.boolean' => 'وضعیت تخصص اصلی باید بله یا خیر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        // بررسی اینکه آیا این پزشک قبلاً تخصص اصلی دیگه‌ای داره یا نه
        if ($this->is_main) {
            $existingMainSpecialty = DoctorSpecialty::where('doctor_id', $this->doctor_id)
                ->where('is_main', true)
                ->where('id', '!=', $this->specialty->id) // تخصص فعلی رو مستثنی می‌کنیم
                ->exists();

            if ($existingMainSpecialty) {
                $this->dispatch('show-alert', type: 'error', message: 'این پزشک قبلاً یک تخصص اصلی دارد. فقط یک تخصص می‌تواند اصلی باشد.');
                return;
            }
        }

        $this->specialty->update([
            'doctor_id' => $this->doctor_id,
            'specialty_id' => $this->specialty_id,
            'academic_degree_id' => $this->academic_degree_id,
            'specialty_title' => $this->specialty_title,
            'is_main' => $this->is_main,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'تخصص پزشک با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.doctorspecialties.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctorspecialties.doctorspecialty-edit');
    }
}
