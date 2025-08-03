<?php

namespace App\Livewire\Mc\Panel\DoctorServices;

use App\Models\MedicalCenter;
use App\Models\Service;
use Livewire\Component;
use App\Models\Insurance;
use App\Models\DoctorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DoctorServiceCreate extends Component
{
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
    public $pendingInsuranceIndex = null;
    public $pendingInsuranceOldValue = null;

    public function mount()
    {
        $this->pricing[] = [
            'insurance_id' => null,
            'price' => 0,
            'discount' => 0,
            'final_price' => 0,
        ];
    }

    public function openDiscountModal($index = null)
    {
        $this->currentPricingIndex = $index;
        $this->showDiscountModal = true;
        if ($index !== null && isset($this->pricing[$index])) {
            $this->discountPercent = $this->pricing[$index]['discount'] ?? 0;
            $this->discountAmount = $this->pricing[$index]['price'] && $this->discountPercent
                ? ($this->pricing[$index]['price'] * $this->discountPercent / 100)
                : 0;
        }
    }

    public function closeDiscountModal()
    {
        $this->showDiscountModal = false;
        $this->currentPricingIndex = null;
    }

    public function updatedDiscountPercent($value)
    {
        if ($this->currentPricingIndex !== null && isset($this->pricing[$this->currentPricingIndex])) {
            if ($this->pricing[$this->currentPricingIndex]['price'] && $value) {
                $this->discountAmount = $this->pricing[$this->currentPricingIndex]['price'] * $value / 100;
                $this->pricing[$this->currentPricingIndex]['final_price'] = $this->pricing[$this->currentPricingIndex]['price'] - $this->discountAmount;
            } else {
                $this->discountAmount = 0;
                $this->pricing[$this->currentPricingIndex]['final_price'] = $this->pricing[$this->currentPricingIndex]['price'];
            }
        }
    }

    public function updatedDiscountAmount($value)
    {
        if ($this->currentPricingIndex !== null && isset($this->pricing[$this->currentPricingIndex])) {
            if ($this->pricing[$this->currentPricingIndex]['price'] && $value) {
                $this->discountPercent = ($value / $this->pricing[$this->currentPricingIndex]['price']) * 100;
                $this->pricing[$this->currentPricingIndex]['final_price'] = $this->pricing[$this->currentPricingIndex]['price'] - $value;
            } else {
                $this->discountPercent = 0;
                $this->pricing[$this->currentPricingIndex]['final_price'] = $this->pricing[$this->currentPricingIndex]['price'];
            }
        }
    }

    public function updatedPricing($value, $name)
    {
        $parts = explode('.', $name);
        $index = $parts[0];
        $field = $parts[1] ?? null;
        if ($field === 'insurance_id' && isset($this->pricing[$index])) {
            $insuranceId = $value;
            // Get doctor_id based on guard
            $doctorId = null;
            $medicalCenterId = null;
            if (Auth::guard('medical_center')->check()) {
                // For medical_center guard, get the selected doctor and medical center
                $medicalCenter = Auth::guard('medical_center')->user();
                $medicalCenterId = $medicalCenter->id;
                $selectedDoctor = DB::table('medical_center_selected_doctors')
                    ->where('medical_center_id', $medicalCenter->id)
                    ->first();
                $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
            } else {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            }
            $exists = DoctorService::where('doctor_id', $doctorId)
                ->where('service_id', $this->service_id)
                ->where('medical_center_id', $medicalCenterId)
                ->where('insurance_id', $insuranceId)
                ->exists();
            if ($exists) {
                $service = Service::find($this->service_id);
                $insurance = Insurance::find($insuranceId);
                $this->pendingInsuranceIndex = $index;
                $this->pendingInsuranceOldValue = $this->pricing[$index]['insurance_id'];
                $this->dispatch(
                    'confirm-edit',
                    serviceName: $service ? $service->name : 'نامشخص',
                    insuranceName: $insurance ? $insurance->name : 'نامشخص'
                );
                return;
            }
        }
        if (isset($this->pricing[$index])) {
            $this->pricing[$index]['final_price'] = $this->pricing[$index]['price'] - ($this->pricing[$index]['price'] * ($this->pricing[$index]['discount'] ?? 0) / 100);
            $this->save();
        }
    }

    public function confirmEditInsurance()
    {
        if ($this->pendingInsuranceIndex !== null) {
            $index = $this->pendingInsuranceIndex;
            $this->pricing[$index]['final_price'] = $this->pricing[$index]['price'] - ($this->pricing[$index]['price'] * ($this->pricing[$index]['discount'] ?? 0) / 100);
            $this->save();
            $this->pendingInsuranceIndex = null;
            $this->pendingInsuranceOldValue = null;
        }
    }

    public function cancelEditInsurance()
    {
        if ($this->pendingInsuranceIndex !== null && $this->pendingInsuranceOldValue !== null) {
            $index = $this->pendingInsuranceIndex;
            $this->pricing[$index]['insurance_id'] = $this->pendingInsuranceOldValue;
            $this->pendingInsuranceIndex = null;
            $this->pendingInsuranceOldValue = null;
        }
    }

    public function applyDiscount()
    {
        if ($this->currentPricingIndex !== null && isset($this->pricing[$this->currentPricingIndex])) {
            $this->pricing[$this->currentPricingIndex]['discount'] = $this->discountPercent;
            $this->pricing[$this->currentPricingIndex]['final_price'] = $this->pricing[$this->currentPricingIndex]['price'] - ($this->pricing[$this->currentPricingIndex]['price'] * $this->discountPercent / 100);
            $this->save();
        }
        $this->showDiscountModal = false;
        $this->currentPricingIndex = null;
    }

    public function addPricingRow()
    {
        $this->pricing[] = [
            'insurance_id' => null,
            'price' => 0,
            'discount' => 0,
            'final_price' => 0,
        ];
        $this->save();
    }

    public function removePricingRow($index)
    {
        unset($this->pricing[$index]);
        $this->pricing = array_values($this->pricing);
        $this->save();
    }

    public function updatedSelectedService($value)
    {
        $this->reset(['service_id', 'duration', 'description', 'pricing']);
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
                        'insurance_id' => null, // بیمه جدید انتخاب شود
                        'price' => $doctorService->price,
                        'discount' => $doctorService->discount,
                        'final_price' => $doctorService->price - ($doctorService->price * $doctorService->discount / 100),
                    ]];
                    $this->dispatch('update-select2', clinicId: $this->medical_center_id);
                }
            } else {
                $service = Service::find($value);
                if ($service) {
                    $this->service_id = $service->id;
                    $this->description = $service->description;
                    $this->duration = 15;
                    $this->pricing = [[
                        'insurance_id' => null,
                        'price' => 0,
                        'discount' => 0,
                        'final_price' => 0,
                    ]];
                }
            }
        }
        // هیچ ذخیره‌ای انجام نشود
    }

    public function updatedDuration($value)
    {
        $this->save();
    }

    public function updatedDescription($value)
    {
        $this->save();
    }

    private function save()
    {
        $this->isSaving = true;
        // Get doctor_id based on guard
        $doctorId = null;
        $medicalCenterId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor and medical center
            $medicalCenter = Auth::guard('medical_center')->user();
            $medicalCenterId = $medicalCenter->id;
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        }
        $currentState = [
            'service_id' => $this->service_id,
            'medical_center_id' => $medicalCenterId,
            'duration' => $this->duration,
            'description' => $this->description,
            'pricing' => $this->pricing,
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
            'medical_center_id.required' => 'مرکز درمانی الزامی است.',
            'medical_center_id.exists' => 'مرکز درمانی معتبر نیست.',
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

        // ذخیره‌سازی یا آپدیت رکورد
        $service = Service::find($this->service_id);
        foreach ($this->pricing as $pricing) {
            $doctorService = DoctorService::where('doctor_id', $doctorId)
                ->where('service_id', $this->service_id)
                ->where('medical_center_id', $medicalCenterId)
                ->where('insurance_id', $pricing['insurance_id'])
                ->first();

            if ($doctorService) {
                // اگر رکورد وجود داشت، آپدیت کن
                $doctorService->update([
                    'name' => $service->name,
                    'description' => $this->description,
                    'status' => true,
                    'duration' => $this->duration,
                    'price' => $pricing['price'],
                    'discount' => $pricing['discount'] ?? 0,
                ]);
            } else {
                // اگر نبود، ایجاد کن
                DoctorService::create([
                    'doctor_id' => $doctorId,
                    'service_id' => $this->service_id,
                    'medical_center_id' => $medicalCenterId,
                    'insurance_id' => $pricing['insurance_id'],
                    'name' => $service->name,
                    'description' => $this->description,
                    'status' => true,
                    'duration' => $this->duration,
                    'price' => $pricing['price'],
                    'discount' => $pricing['discount'] ?? 0,
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
            return redirect()->route('mc.panel.doctor-services.index');
        }
    }

    public function render()
    {
        $services = Service::where('status', true)->get();
        $insurances = Insurance::all();
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        }
        $doctorServices = DoctorService::where('doctor_id', $doctorId)
            ->with(['service', 'insurance', 'medicalCenter'])
            ->get();
        return view('livewire.mc.panel.doctor-services.doctor-service-create', compact('services', 'insurances', 'doctorServices'));
    }
}
