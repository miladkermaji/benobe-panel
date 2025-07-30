<?php

namespace App\Livewire\Admin\Panel\Insurances;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\Insurance;
use App\Models\Clinic;
use Livewire\WithFileUploads;

class InsuranceCreate extends Component
{
    use WithFileUploads;

    public $clinic_id;
    public $name;
    public $calculation_method = 0;
    public $appointment_price;
    public $insurance_percent;
    public $final_price;
    public $photo;
    public $photoPreview;
    public $clinics;

    public function mount()
    {
        $this->photoPreview = asset('default-avatar.png');
        $this->clinics = Clinic::all();
    }

    public function updatedPhoto()
    {
        $this->photoPreview = $this->photo->temporaryUrl();
    }

    public function store()
    {
        $validator = Validator::make([
            'clinic_id' => $this->clinic_id,
            'name' => $this->name,
            'calculation_method' => $this->calculation_method,
            'appointment_price' => $this->appointment_price,
            'insurance_percent' => $this->insurance_percent,
            'final_price' => $this->final_price,
            'photo' => $this->photo,
        ], [
            'clinic_id' => 'nullable|exists:medical_centers,id',
            'name' => 'required|string|max:255',
            'calculation_method' => 'required|in:0,1',
            'appointment_price' => 'nullable|numeric|min:0',
            'insurance_percent' => 'nullable|numeric|min:0|max:100',
            'final_price' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
        ], [
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

        Insurance::create($data);

        $this->dispatch('show-alert', type: 'success', message: 'بیمه با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.insurances.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.insurances.insurance-create');
    }
}
