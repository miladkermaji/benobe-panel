<?php

namespace App\Livewire\Dr\Panel\DoctorServices;

use App\Models\Clinic;
use App\Models\Service;
use App\Models\Insurance;
use App\Models\DoctorService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\MedicalCenter;

class DoctorServiceEdit extends Component
{
    public $doctorService;
    public $selected_service;
    public $service_id;
    public $medical_center_id;
    public $duration;
    public $description;
    public $pricing = [];
    public $showDiscountModal = false;
    public $discountPercent = 0;
    public $discountAmount = 0;
    public $currentPricingIndex = null;
    public $isSaving = false;
    protected $previousState = [];
    protected $listeners = ['autoSave' => 'save'];

    public function mount($id)
    {
        $this->doctorService = DoctorService::findOrFail($id);
        $this->selected_service = 'doctor_service_' . $this->doctorService->id;
        $this->service_id = $this->doctorService->service_id;
        $this->medical_center_id = $this->doctorService->medical_center_id;
        $this->duration = $this->doctorService->duration;
        $this->description = $this->doctorService->description;
        $this->pricing = [[
            'id' => $this->doctorService->id,
            'insurance_id' => $this->doctorService->insurance_id,
            'price' => $this->doctorService->price,
            'discount' => $this->doctorService->discount,
            'final_price' => $this->doctorService->price - ($this->doctorService->price * $this->doctorService->discount / 100),
        ]];
        $this->previousState = [
            'service_id' => $this->service_id,
            'medical_center_id' => $this->medical_center_id,
            'duration' => $this->duration,
            'description' => $this->description,
            'pricing' => $this->pricing,
        ];
    }

    public function openDiscountModal($index = null)
    {
        $this->currentPricingIndex = $index;
        $this->showDiscountModal = true;
        if ($index !== null && isset($this->pricing[$index])) {
            $this->discountPercent = $this->pricing[$index]['discount'] ?? 0;

            // Clean price value by removing commas
            $cleanPrice = is_string($this->pricing[$index]['price']) ?
                (float) str_replace(',', '', $this->pricing[$index]['price']) :
                (float) $this->pricing[$index]['price'];

            $this->discountAmount = $cleanPrice && $this->discountPercent
                ? ($cleanPrice * $this->discountPercent / 100)
                : 0;
        }
        $this->dispatch('openDiscountModal');
    }

    public function closeDiscountModal()
    {
        $this->showDiscountModal = false;
        $this->currentPricingIndex = null;
        $this->dispatch('closeDiscountModal');
    }

    public function calculateDiscountPercent($value)
    {
        if ($this->currentPricingIndex !== null && isset($this->pricing[$this->currentPricingIndex])) {
            // Clean price value by removing commas
            $cleanPrice = is_string($this->pricing[$this->currentPricingIndex]['price']) ?
                (float) str_replace(',', '', $this->pricing[$this->currentPricingIndex]['price']) :
                (float) $this->pricing[$this->currentPricingIndex]['price'];

            if ($cleanPrice && $value) {
                $this->discountAmount = round($cleanPrice * $value / 100, 0);
                $this->pricing[$this->currentPricingIndex]['final_price'] = $cleanPrice - $this->discountAmount;
            } else {
                $this->discountAmount = 0;
                $this->pricing[$this->currentPricingIndex]['final_price'] = $cleanPrice;
            }
        }
    }

    public function calculateDiscountAmount($value)
    {
        if ($this->currentPricingIndex !== null && isset($this->pricing[$this->currentPricingIndex])) {
            // Clean price value by removing commas
            $cleanPrice = is_string($this->pricing[$this->currentPricingIndex]['price']) ?
                (float) str_replace(',', '', $this->pricing[$this->currentPricingIndex]['price']) :
                (float) $this->pricing[$this->currentPricingIndex]['price'];

            if ($cleanPrice && $value) {
                $this->discountPercent = round(($value / $cleanPrice) * 100, 2);
                $this->pricing[$this->currentPricingIndex]['final_price'] = $cleanPrice - $value;
            } else {
                $this->discountPercent = 0;
                $this->pricing[$this->currentPricingIndex]['final_price'] = $cleanPrice;
            }
        }
    }

    public function updatedPricing($value, $name)
    {
        $index = explode('.', $name)[0];
        if (isset($this->pricing[$index])) {
            // Clean price value by removing commas
            $cleanPrice = is_string($this->pricing[$index]['price']) ?
                (float) str_replace(',', '', $this->pricing[$index]['price']) :
                (float) $this->pricing[$index]['price'];

            $this->pricing[$index]['final_price'] = $cleanPrice - ($cleanPrice * ($this->pricing[$index]['discount'] ?? 0) / 100);
            $this->save();
        }
    }

    public function applyDiscount()
    {
        if ($this->currentPricingIndex !== null && isset($this->pricing[$this->currentPricingIndex])) {
            $this->pricing[$this->currentPricingIndex]['discount'] = $this->discountPercent;

            // Clean price value by removing commas
            $cleanPrice = is_string($this->pricing[$this->currentPricingIndex]['price']) ?
                (float) str_replace(',', '', $this->pricing[$this->currentPricingIndex]['price']) :
                (float) $this->pricing[$this->currentPricingIndex]['price'];

            $this->pricing[$this->currentPricingIndex]['final_price'] = $cleanPrice - ($cleanPrice * $this->discountPercent / 100);
            $this->save();
        }
        $this->showDiscountModal = false;
        $this->currentPricingIndex = null;
        $this->dispatch('closeDiscountModal');
    }

    public function removePricingRow($index)
    {
        if (isset($this->pricing[$index]['id'])) {
            DoctorService::find($this->pricing[$index]['id'])->delete();
        }
        unset($this->pricing[$index]);
        $this->pricing = array_values($this->pricing);
        $this->save();
    }

    public function updatedSelectedService($value)
    {
        $this->reset(['service_id', 'medical_center_id', 'duration', 'description', 'pricing']);
        if ($value) {
            if (str_starts_with($value, 'doctor_service_')) {
                $doctorServiceId = str_replace('doctor_service_', '', $value);
                $doctorService = DoctorService::find($doctorServiceId);
                if ($doctorService) {
                    $this->service_id = $doctorService->service_id;
                    $this->medical_center_id = $doctorService->medical_center_id;
                    $this->duration = $doctorService->duration;
                    $this->description = $doctorService->description;
                    $this->pricing = [[
                        'id' => $doctorService->id,
                        'insurance_id' => $doctorService->insurance_id,
                        'price' => $doctorService->price,
                        'discount' => $doctorService->discount,
                        'final_price' => $doctorService->price - ($doctorService->price * $doctorService->discount / 100),
                    ]];
                }
            } else {
                $service = Service::find($value);
                if ($service) {
                    $this->service_id = $service->id;
                    $this->description = $service->description;
                    $this->duration = 15;
                    $this->pricing = [[
                        'id' => null,
                        'insurance_id' => null,
                        'price' => 0,
                        'discount' => 0,
                        'final_price' => 0,
                    ]];
                }
            }
        }
        $this->save();
    }

    public function updatedClinicId($value)
    {
        $this->save();
    }

    public function updatedDuration($value)
    {
        $this->save();
    }

    public function updatedDescription($value)
    {
        $this->save();
    }

    public function save()
    {
        $this->isSaving = true;
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

        // Clean pricing data before validation
        $cleanedPricing = [];
        foreach ($this->pricing as $pricing) {
            $cleanedPricing[] = [
                'id' => $pricing['id'] ?? null,
                'insurance_id' => $pricing['insurance_id'],
                'price' => is_string($pricing['price']) ?
                    (float) str_replace(',', '', $pricing['price']) :
                    (float) $pricing['price'],
                'discount' => $pricing['discount'] ?? 0,
                'final_price' => $pricing['final_price'] ?? 0,
            ];
        }

        $currentState = [
            'service_id' => $this->service_id,
            'medical_center_id' => $this->medical_center_id,
            'duration' => $this->duration,
            'description' => $this->description,
            'pricing' => $cleanedPricing,
        ];

        // بررسی تغییرات
        if ($this->previousState && $this->previousState == $currentState) {
            $this->isSaving = false;
            return;
        }

        $validator = Validator::make($currentState, [
            'service_id' => 'required|exists:services,id',
            'medical_center_id' => 'required|exists:medical_centers,id',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'pricing' => 'required|array|min:1',
            'pricing.*.insurance_id' => 'required|exists:insurances,id',
            'pricing.*.price' => 'required|numeric|min:0',
            'pricing.*.discount' => 'nullable|numeric|min:0|max:100',
        ], [
            'service_id.required' => 'انتخاب خدمت الزامی است.',
            'service_id.exists' => 'خدمت انتخاب‌شده معتبر نیست.',
            'medical_center_id.required' => 'انتخاب کلینیک الزامی است.',
            'medical_center_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'duration.required' => 'مدت زمان الزامی است.',
            'duration.integer' => 'مدت زمان باید عدد صحیح باشد.',
            'duration.min' => 'مدت زمان باید حداقل ۱ دقیقه باشد.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
            'pricing.required' => 'حداقل یک ردیف قیمت‌گذاری الزامی است.',
            'pricing.*.insurance_id.required' => 'انتخاب بیمه الزامی است.',
            'pricing.*.insurance_id.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
            'pricing.*.price.required' => 'قیمت الزامی است.',
            'pricing.*.price.numeric' => 'قیمت باید عدد باشد.',
            'pricing.*.price.min' => 'قیمت نمی‌تواند منفی باشد.',
            'pricing.*.discount.numeric' => 'تخفیف باید عدد باشد.',
            'pricing.*.discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
            'pricing.*.discount.max' => 'تخفیف نمی‌تواند بیشتر از ۱۰۰ درصد باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            $this->isSaving = false;
            return;
        }

        // بررسی وجود رکورد تکراری
        foreach ($cleanedPricing as $pricing) {
            $exists = DoctorService::where('doctor_id', $doctorId)
                ->where('service_id', $this->service_id)
                ->where('insurance_id', $pricing['insurance_id'])
                ->where('medical_center_id', $this->medical_center_id)
                ->when(isset($pricing['id']), function ($query) use ($pricing) {
                    $query->where('id', '!=', $pricing['id']);
                })
                ->exists();
            if ($exists) {
                $this->dispatch('show-alert', type: 'error', message: 'این خدمت با بیمه ' . Insurance::find($pricing['insurance_id'])->name . ' و کلینیک انتخاب‌شده قبلاً تعریف شده است.');
                $this->isSaving = false;
                return;
            }
        }

        // به‌روزرسانی یا ایجاد رکوردها
        $service = Service::find($this->service_id);
        foreach ($cleanedPricing as $pricing) {
            if (isset($pricing['id']) && $pricing['id']) {
                // به‌روزرسانی رکورد موجود
                DoctorService::find($pricing['id'])->update([
                    'service_id' => $this->service_id,
                    'medical_center_id' => $this->medical_center_id,
                    'insurance_id' => $pricing['insurance_id'],
                    'name' => $service->name,
                    'description' => $this->description,
                    'duration' => $this->duration,
                    'price' => $pricing['price'],
                    'discount' => $pricing['discount'] ?? 0,
                    'status' => true,
                ]);
            }
        }

        $this->previousState = $currentState;
        $this->dispatch('show-alert', type: 'success', message: 'تغییرات با موفقیت ذخیره شد!');
        $this->isSaving = false;
    }

    public function saveAndRedirect()
    {
        $this->save();
        if (!$this->isSaving) {
            $this->dispatch('show-alert', type: 'success', message: 'تغییرات ذخیره شد!');
            return redirect()->route('dr.panel.doctor-services.index');
        }
    }

    public function render()
    {
        $services = Service::where('status', true)->get();
        $insurances = Insurance::all();
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $clinics = MedicalCenter::whereHas('doctors', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })->where('type', 'policlinic')->get();
        $doctorServices = DoctorService::where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->with(['service', 'insurance', 'medicalCenter'])
            ->get();
        return view('livewire.dr.panel.doctor-services.doctor-service-edit', compact('services', 'insurances', 'clinics', 'doctorServices'));
    }
}
