<?php

namespace App\Livewire\Admin\Panel\DoctorInsurances;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\Insurance;
use App\Models\Doctor;
use App\Models\DoctorInsurance;
use App\Models\Clinic;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class DoctorInsuranceEdit extends Component
{
    use WithFileUploads;

    public $insurance;
    public $doctor_id;
    public $clinic_id;
    public $name;
    public $calculation_method;
    public $appointment_price;
    public $insurance_percent;
    public $final_price;
    public $photo;
    public $photoPreview;
    public $doctors;
    public $clinics;

    public function mount($id)
    {
        $this->insurance = Insurance::findOrFail($id);
        $this->doctor_id = $this->insurance->doctors->first()->id ?? null;
        $this->clinic_id = $this->insurance->clinic_id;
        $this->name = $this->insurance->name;
        $this->calculation_method = $this->insurance->calculation_method;
        $this->appointment_price = $this->insurance->appointment_price;
        $this->insurance_percent = $this->insurance->insurance_percent;
        $this->final_price = $this->insurance->final_price;
        $this->photoPreview = $this->insurance->photo ? Storage::url($this->insurance->photo) : asset('default-avatar.png');
        $this->doctors = Doctor::all();
        $this->clinics = Clinic::all();
    }

    public function updatedPhoto()
    {
        $this->photoPreview = $this->photo->temporaryUrl();
    }

    public function update()
    {
        $validator = Validator::make([
            'doctor_id' => $this->doctor_id,
            'clinic_id' => $this->clinic_id,
            'name' => $this->name,
            'calculation_method' => $this->calculation_method,
            'appointment_price' => $this->appointment_price,
            'insurance_percent' => $this->insurance_percent,
            'final_price' => $this->final_price,
            'photo' => $this->photo,
        ], [
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'nullable|exists:medical_centers,id',
            'name' => 'required|string|max:255',
            'calculation_method' => 'required|in:0,1,2,3,4',
            'appointment_price' => 'nullable|numeric|min:0',
            'insurance_percent' => 'nullable|numeric|min:0|max:100',
            'final_price' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
        ], [
            'doctor_id.required' => 'انتخاب دکتر الزامی است.',
            'doctor_id.exists' => 'دکتر انتخاب‌شده معتبر نیست.',
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'name.required' => 'نام بیمه الزامی است.',
            'name.string' => 'نام بیمه باید رشته باشد.',
            'name.max' => 'نام بیمه نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'calculation_method.required' => 'روش محاسبه الزامی است.',
            'calculation_method.in' => 'روش محاسبه انتخاب‌شده معتبر نیست.',
            'appointment_price.numeric' => 'قیمت نوبت باید عددی باشد.',
            'appointment_price.min' => 'قیمت نوبت نمی‌تواند منفی باشد.',
            'insurance_percent.numeric' => 'درصد بیمه باید عددی باشد.',
            'insurance_percent.min' => 'درصد بیمه نمی‌تواند منفی باشد.',
            'insurance_percent.max' => 'درصد بیمه نمی‌تواند بیش از ۱۰۰ باشد.',
            'final_price.numeric' => 'قیمت نهایی باید عددی باشد.',
            'final_price.min' => 'قیمت نهایی نمی‌تواند منفی باشد.',
            'photo.image' => 'فایل باید یک تصویر باشد.',
            'photo.max' => 'حجم تصویر نمی‌تواند بیش از ۲ مگابایت باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $data = [
            'clinic_id' => $this->clinic_id,
            'name' => $this->name,
            'calculation_method' => $this->calculation_method,
            'appointment_price' => $this->appointment_price,
            'insurance_percent' => $this->insurance_percent,
            'final_price' => $this->final_price,
        ];

        if ($this->photo) {
            $data['photo'] = $this->photo->store('photos', 'public');
        }

        $this->insurance->update($data);
        DoctorInsurance::updateOrCreate(
            ['insurance_id' => $this->insurance->id],
            ['doctor_id' => $this->doctor_id]
        );

        $this->dispatch('show-alert', type: 'success', message: 'بیمه با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.doctor-insurances.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctor-insurances.doctor-insurance-edit');
    }
}
