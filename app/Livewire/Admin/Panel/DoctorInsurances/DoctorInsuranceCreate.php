<?php

namespace App\Livewire\Admin\Panel\DoctorInsurances;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\Insurance;
use App\Models\Doctor;
use App\Models\DoctorInsurance;
use App\Models\MedicalCenter;
use Livewire\WithFileUploads;

class DoctorInsuranceCreate extends Component
{
    use WithFileUploads;

    public $doctor_id;
    public $medical_center_id;
    public $name;
    public $calculation_method = 0;
    public $appointment_price;
    public $insurance_percent;
    public $final_price;
    public $photo;
    public $photoPreview;
    public $doctors;
    public $clinics;

    public function mount()
    {
        $this->photoPreview = asset('default-avatar.png');
        $this->doctors = Doctor::all();
        $this->clinics = MedicalCenter::where('type', 'policlinic')->get();
    }

    public function updatedPhoto()
    {
        $this->photoPreview = $this->photo->temporaryUrl();
    }

    public function store()
    {
        $validator = Validator::make([
            'doctor_id' => $this->doctor_id,
            'medical_center_id' => $this->medical_center_id,
            'name' => $this->name,
            'calculation_method' => $this->calculation_method,
            'appointment_price' => $this->appointment_price,
            'insurance_percent' => $this->insurance_percent,
            'final_price' => $this->final_price,
            'photo' => $this->photo,
        ], [
            'doctor_id' => 'required|exists:doctors,id',
            'medical_center_id' => 'nullable|exists:medical_centers,id',
            'name' => 'required|string|max:255',
            'calculation_method' => 'required|in:0,1,2,3,4',
            'appointment_price' => 'nullable|numeric|min:0',
            'insurance_percent' => 'nullable|numeric|min:0|max:100',
            'final_price' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
        ], [
            'doctor_id.required' => 'انتخاب دکتر الزامی است.',
            'doctor_id.exists' => 'دکتر انتخاب‌شده معتبر نیست.',
            'medical_center_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
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
            'medical_center_id' => $this->medical_center_id,
            'name' => $this->name,
            'calculation_method' => $this->calculation_method,
            'appointment_price' => $this->appointment_price,
            'insurance_percent' => $this->insurance_percent,
            'final_price' => $this->final_price,
        ];

        if ($this->photo) {
            $data['photo'] = $this->photo->store('photos', 'public');
        }

        $insurance = Insurance::create($data);
        DoctorInsurance::create([
            'doctor_id' => $this->doctor_id,
            'insurance_id' => $insurance->id,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'بیمه با موفقیت برای دکتر اضافه شد!');
        return redirect()->route('admin.panel.doctor-insurances.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctor-insurances.doctor-insurance-create');
    }
}
