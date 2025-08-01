<?php

namespace App\Livewire\Admin\Panel\Doctorservices;

use App\Models\MedicalCenter;
use App\Models\Doctor;
use App\Models\DoctorService;
use App\Models\Insurance;
use App\Models\Service;
use Livewire\Component;

class DoctorServiceEdit extends Component
{
    public $doctorService;
    public $doctor_id;
    public $medical_center_id;
    public $insurance_id;
    public $service_id;
    public $name;
    public $description;
    public $duration;
    public $price;
    public $discount;
    public $status;
    public $parent_id;

    public $doctors;
    public $clinics;
    public $insurances;
    public $services;
    public $parentServices;

    public function mount($id)
    {
        $this->doctorService = DoctorService::findOrFail($id);
        $this->doctor_id = $this->doctorService->doctor_id;
        $this->medical_center_id = $this->doctorService->medical_center_id;
        $this->insurance_id = $this->doctorService->insurance_id;
        $this->service_id = $this->doctorService->service_id;
        $this->name = $this->doctorService->name;
        $this->description = $this->doctorService->description;
        $this->duration = $this->doctorService->duration;
        $this->price = $this->doctorService->price;
        $this->discount = $this->doctorService->discount;
        $this->status = $this->doctorService->status;
        $this->parent_id = $this->doctorService->parent_id;

        $this->doctors = Doctor::all();
        $this->clinics = MedicalCenter::where('type', 'policlinic')->get();
        $this->insurances = Insurance::all();
        $this->services = Service::all();
        $this->parentServices = DoctorService::whereNull('parent_id')->where('id', '!=', $id)->get();
    }

    protected function rules()
    {
        return [
            'doctor_id' => 'required|exists:doctors,id',
            'medical_center_id' => 'nullable|exists:medical_centers,id',
            'insurance_id' => 'nullable|exists:insurances,id',
            'service_id' => 'nullable|exists:services,id',
            'name' => 'required|string|max:255|unique:doctor_services,name,' . $this->doctorService->id,
            'description' => 'nullable|string|max:500',
            'duration' => 'integer|min:1',
            'price' => 'numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|boolean',
            'parent_id' => 'nullable|exists:doctor_services,id',
        ];
    }

    protected $messages = [
        'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
        'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
        'medical_center_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
        'insurance_id.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
        'service_id.exists' => 'خدمت انتخاب‌شده معتبر نیست.',
        'name.required' => 'لطفاً نام خدمت را وارد کنید.',
        'name.string' => 'نام خدمت باید یک متن باشد.',
        'name.max' => 'نام خدمت نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        'name.unique' => 'این نام خدمت قبلاً ثبت شده است.',
        'description.string' => 'توضیحات باید یک متن باشد.',
        'description.max' => 'توضیحات نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
        'duration.integer' => 'مدت زمان باید یک عدد صحیح باشد.',
        'duration.min' => 'مدت زمان باید حداقل ۱ دقیقه باشد.',
        'price.numeric' => 'قیمت باید یک عدد باشد.',
        'price.min' => 'قیمت نمی‌تواند منفی باشد.',
        'discount.numeric' => 'تخفیف باید یک عدد باشد.',
        'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
        'discount.max' => 'تخفیف نمی‌تواند بیشتر از ۱۰۰ درصد باشد.',
        'status.required' => 'لطفاً وضعیت را مشخص کنید.',
        'status.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
        'parent_id.exists' => 'خدمت مادر انتخاب‌شده معتبر نیست.',
    ];

    public function update()
    {
        if (empty($this->doctor_id) || $this->doctor_id === '' || $this->doctor_id === null) {
            $this->addError('doctor_id', 'لطفاً یک پزشک انتخاب کنید.');
            $this->dispatch('show-alert', type: 'error', message: 'لطفاً یک پزشک انتخاب کنید!');
            return;
        }

        $this->validate();

        $this->doctorService->update([
            'doctor_id' => $this->doctor_id,
            'medical_center_id' => $this->medical_center_id,
            'insurance_id' => $this->insurance_id,
            'service_id' => $this->service_id,
            'name' => $this->name,
            'description' => $this->description,
            'duration' => $this->duration,
            'price' => $this->price,
            'discount' => $this->discount,
            'status' => $this->status,
            'parent_id' => $this->parent_id,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'خدمت پزشک با موفقیت به‌روزرسانی شد!');
        $this->dispatch('redirect-after-delay');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctor-services.doctor-service-edit');
    }
}
