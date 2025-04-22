<?php

namespace App\Livewire\Dr\Panel\DoctorServices;

use App\Models\Clinic;
use Livewire\Component;
use App\Models\Insurance;
use App\Models\DoctorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DoctorServiceCreate extends Component
{
    public $name;
    public $description;
    public $status = true;
    public $duration;
    public $price;
    public $discount;
    public $parent_id;
    public $insurance_id;
    public $clinic_id;
    public $showDiscountModal = false;
    public $discountPercent = 0;
    public $discountAmount = 0;

    public function openDiscountModal()
    {
        $this->showDiscountModal = true;
        $this->discountPercent = $this->discount ?? 0;
        $this->discountAmount = $this->price && $this->discount ? ($this->price * $this->discount / 100) : 0;
    }

    public function closeDiscountModal()
    {
        $this->showDiscountModal = false;
    }

    public function updatedDiscountPercent($value)
    {
        if ($this->price && $value) {
            $this->discountAmount = $this->price * $value / 100;
        } else {
            $this->discountAmount = 0;
        }
    }

    public function updatedDiscountAmount($value)
    {
        if ($this->price && $value) {
            $this->discountPercent = ($value / $this->price) * 100;
        } else {
            $this->discountPercent = 0;
        }
    }

    public function applyDiscount()
    {
        $this->discount = $this->discountPercent;
        $this->showDiscountModal = false;
    }

    public function store()
    {
    

        $validator = Validator::make([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'duration' => $this->duration,
            'price' => $this->price,
            'discount' => $this->discount,
            'parent_id' => $this->parent_id,
            'insurance_id' => $this->insurance_id,
            'clinic_id' => $this->clinic_id,
        ], [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'required|boolean',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'parent_id' => 'nullable|exists:doctor_services,id',
            'insurance_id' => 'required|exists:insurances,id',
            'clinic_id' => 'required|exists:clinics,id',
        ], [
            'name.required' => 'فیلد نام الزامی است.',
            'name.string' => 'نام باید یک رشته باشد.',
            'name.max' => 'نام نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
            'status.required' => 'وضعیت الزامی است.',
            'duration.required' => 'مدت زمان الزامی است.',
            'duration.integer' => 'مدت زمان باید یک عدد صحیح باشد.',
            'duration.min' => 'مدت زمان باید حداقل ۱ دقیقه باشد.',
            'price.required' => 'قیمت الزامی است.',
            'price.numeric' => 'قیمت باید یک عدد باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
            'discount.numeric' => 'تخفیف باید یک عدد باشد.',
            'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
            'discount.max' => 'تخفیف نمی‌تواند بیشتر از ۱۰۰ باشد.',
            'parent_id.exists' => 'سرویس مادر انتخاب‌شده معتبر نیست.',
            'insurance_id.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
            'insurance_id.required' => 'بیمه  الزامی است .',
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'clinic_id.required' => 'کلینیک   الزامی است.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        DoctorService::create([
            'doctor_id' => Auth::guard('doctor')->user()->id,
            'clinic_id' => $this->clinic_id,
            'insurance_id' => $this->insurance_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'duration' => $this->duration,
            'price' => $this->price,
            'discount' => $this->discount,
            'parent_id' => $this->parent_id,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'سرویس با موفقیت ایجاد شد!');
        return redirect()->route('dr.panel.doctor-services.index');
    }

    public function render()
    {
        $parentServices = DoctorService::whereNull('parent_id')
            ->with('clinic')
            ->get();
        $insurances = Insurance::all();
        $clinics = Clinic::where('doctor_id', Auth::guard('doctor')->user()->id)->get();
        return view('livewire.dr.panel.doctor-services.doctor-service-create', compact('parentServices', 'insurances', 'clinics'));
    }
}
