<?php

namespace App\Livewire\Dr\Panel\DoctorServices;

use App\Models\Clinic;
use App\Models\Service;
use Livewire\Component;
use App\Models\Insurance;
use App\Models\DoctorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DoctorServiceCreate extends Component
{
    public $selected_service;
    public $service_id;
    public $clinic_id;
    public $duration;
    public $description;
    public $insurance_id;
    public $price;
    public $discount = 0;
    public $final_price = 0;
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
            $this->final_price = $this->price - $this->discountAmount;
        } else {
            $this->discountAmount = 0;
            $this->final_price = $this->price;
        }
    }

    public function updatedDiscountAmount($value)
    {
        if ($this->price && $value) {
            $this->discountPercent = ($value / $this->price) * 100;
            $this->final_price = $this->price - $value;
        } else {
            $this->discountPercent = 0;
            $this->final_price = $this->price;
        }
    }

    public function updatedPrice($value)
    {
        $this->final_price = $value - ($value * $this->discount / 100);
    }

    public function applyDiscount()
    {
        $this->discount = $this->discountPercent;
        $this->final_price = $this->price - ($this->price * $this->discount / 100);
        $this->showDiscountModal = false;
    }

    public function updatedSelectedService($value)
    {
        // فقط فیلدهای ضروری رو ریست می‌کنیم، clinic_id رو نگه می‌داریم
        $this->reset(['service_id', 'duration', 'description', 'price', 'discount', 'final_price', 'insurance_id']);

        if ($value) {
            if (str_starts_with($value, 'doctor_service_')) {
                // خدمت از DoctorService
                $doctorServiceId = str_replace('doctor_service_', '', $value);
                $doctorService = DoctorService::find($doctorServiceId);
                if ($doctorService) {
                    $this->service_id = $doctorService->service_id;
                    $this->clinic_id = $doctorService->clinic_id; // کلینیک قبلی رو لود می‌کنیم
                    $this->duration = $doctorService->duration;
                    $this->description = $doctorService->description;
                    $this->price = $doctorService->price;
                    $this->discount = $doctorService->discount;
                    $this->final_price = $this->price - ($this->price * $this->discount / 100);
                    $this->insurance_id = null; // بیمه را خالی می‌کنیم

                    // Dispatch event to update Select2 with clinic_id
                    $this->dispatch('update-select2', clinicId: $this->clinic_id);
                }
            } else {
                // خدمت از Service
                $service = Service::find($value);
                if ($service) {
                    $this->service_id = $service->id;
                    $this->description = $service->description;
                    $this->duration = 15; // مقدار پیش‌فرض
                    $this->price = 0;
                    $this->discount = 0;
                    $this->final_price = 0;
                    $this->insurance_id = null;
                    // clinic_id رو تغییر نمی‌دیم، اجازه می‌دیم کاربر انتخاب کنه
                }
            }
        }
    }

    public function updatedClinicId($value)
    {
        Log::info('Clinic ID updated: ' . ($value ?? 'null')); // Debug
    }

    public function store()
    {
        $validator = Validator::make([
            'service_id' => $this->service_id,
            'clinic_id' => $this->clinic_id,
            'duration' => $this->duration,
            'description' => $this->description,
            'insurance_id' => $this->insurance_id,
            'price' => $this->price,
            'discount' => $this->discount,
        ], [
            'service_id' => 'required|exists:services,id',
            'clinic_id' => 'required|exists:clinics,id',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'insurance_id' => 'required|exists:insurances,id',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
        ], [
            'service_id.required' => 'انتخاب خدمت الزامی است.',
            'service_id.exists' => 'خدمت انتخاب‌شده معتبر نیست.',
            'clinic_id.required' => 'انتخاب کلینیک الزامی است.',
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'duration.required' => 'مدت زمان الزامی است.',
            'duration.integer' => 'مدت زمان باید عدد صحیح باشد.',
            'duration.min' => 'مدت زمان باید حداقل ۱ دقیقه باشد.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
            'insurance_id.required' => 'انتخاب بیمه الزامی است.',
            'insurance_id.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
            'price.required' => 'قیمت الزامی است.',
            'price.numeric' => 'قیمت باید عدد باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
            'discount.numeric' => 'تخفیف باید عدد باشد.',
            'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
            'discount.max' => 'تخفیف نمی‌تواند بیشتر از ۱۰۰ درصد باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        // بررسی تکراری بودن خدمت
        $exists = DoctorService::where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->where('service_id', $this->service_id)
            ->where('insurance_id', $this->insurance_id)
            ->where('clinic_id', $this->clinic_id)
            ->exists();

        if ($exists) {
            $this->dispatch('show-alert', type: 'error', message: 'این خدمت با این بیمه و کلینیک قبلاً تعریف شده است.');
            return;
        }

        $service = Service::find($this->service_id);

        DoctorService::create([
            'doctor_id' => Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id,
            'service_id' => $this->service_id,
            'clinic_id' => $this->clinic_id,
            'insurance_id' => $this->insurance_id,
            'name' => $service->name,
            'description' => $this->description,
            'status' => true,
            'duration' => $this->duration,
            'price' => $this->price,
            'discount' => $this->discount,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'خدمت با موفقیت ایجاد شد!');
        return redirect()->route('dr.panel.doctor-services.index');
    }

    public function render()
    {
        $services = Service::where('status', true)->get();
        $insurances = Insurance::all();
        $clinics = Clinic::where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)->get();
        $doctorServices = DoctorService::where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->with(['service', 'insurance', 'clinic'])
            ->get();

        return view('livewire.dr.panel.doctor-services.doctor-service-create', compact('services', 'insurances', 'clinics', 'doctorServices'));
    }
}