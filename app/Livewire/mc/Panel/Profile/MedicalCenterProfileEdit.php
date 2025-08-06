<?php

namespace App\Livewire\Mc\Panel\Profile;

use App\Models\MedicalCenter;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MedicalCenterProfileEdit extends Component
{
    public $name;
    public $title;
    public $address;
    public $secretary_phone;
    public $phone_number;
    public $postal_code;
    public $siam_code;
    public $description;
    public $consultation_fee;
    public $prescription_tariff;
    public $is_active;

    protected $rules = [
        'name' => 'required|string|max:255',
        'title' => 'nullable|string|max:255',
        'address' => 'required|string|max:500',
        'secretary_phone' => 'required|string|max:20',
        'phone_number' => 'required|string|max:20',
        'postal_code' => 'nullable|string|max:10',
        'siam_code' => 'nullable|string|max:50',
        'description' => 'nullable|string|max:1000',
        'consultation_fee' => 'nullable|numeric|min:0',
        'prescription_tariff' => 'nullable|numeric|min:0',
        'is_active' => 'boolean'
    ];

    protected $messages = [
        'name.required' => 'نام مرکز درمانی الزامی است.',
        'name.string' => 'نام مرکز درمانی باید متن باشد.',
        'name.max' => 'نام مرکز درمانی نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        'title.string' => 'عنوان باید متن باشد.',
        'title.max' => 'عنوان نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        'address.required' => 'آدرس الزامی است.',
        'address.string' => 'آدرس باید متن باشد.',
        'address.max' => 'آدرس نمی‌تواند بیشتر از 500 کاراکتر باشد.',
        'secretary_phone.required' => 'شماره تلفن منشی الزامی است.',
        'secretary_phone.string' => 'شماره تلفن منشی باید متن باشد.',
        'secretary_phone.max' => 'شماره تلفن منشی نمی‌تواند بیشتر از 20 کاراکتر باشد.',
        'phone_number.required' => 'شماره تلفن الزامی است.',
        'phone_number.string' => 'شماره تلفن باید متن باشد.',
        'phone_number.max' => 'شماره تلفن نمی‌تواند بیشتر از 20 کاراکتر باشد.',
        'postal_code.string' => 'کد پستی باید متن باشد.',
        'postal_code.max' => 'کد پستی نمی‌تواند بیشتر از 10 کاراکتر باشد.',
        'siam_code.string' => 'کد سیام باید متن باشد.',
        'siam_code.max' => 'کد سیام نمی‌تواند بیشتر از 50 کاراکتر باشد.',
        'description.string' => 'توضیحات باید متن باشد.',
        'description.max' => 'توضیحات نمی‌تواند بیشتر از 1000 کاراکتر باشد.',
        'consultation_fee.numeric' => 'هزینه مشاوره باید عدد باشد.',
        'consultation_fee.min' => 'هزینه مشاوره نمی‌تواند کمتر از 0 باشد.',
        'prescription_tariff.numeric' => 'تعرفه نسخه باید عدد باشد.',
        'prescription_tariff.min' => 'تعرفه نسخه نمی‌تواند کمتر از 0 باشد.',
        'is_active.boolean' => 'وضعیت فعال باید درست یا نادرست باشد.'
    ];

    public function mount()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $this->name = $medicalCenter->name;
        $this->title = $medicalCenter->title;
        $this->address = $medicalCenter->address;
        $this->secretary_phone = $medicalCenter->secretary_phone;
        $this->phone_number = $medicalCenter->phone_number;
        $this->postal_code = $medicalCenter->postal_code;
        $this->siam_code = $medicalCenter->siam_code;
        $this->description = $medicalCenter->description;
        $this->consultation_fee = $medicalCenter->consultation_fee;
        $this->prescription_tariff = $medicalCenter->prescription_tariff;
        $this->is_active = $medicalCenter->is_active;
    }

    public function update()
    {
        $this->validate();

        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $medicalCenter->update([
            'name' => $this->name,
            'title' => $this->title,
            'address' => $this->address,
            'secretary_phone' => $this->secretary_phone,
            'phone_number' => $this->phone_number,
            'postal_code' => $this->postal_code,
            'siam_code' => $this->siam_code,
            'description' => $this->description,
            'consultation_fee' => $this->consultation_fee,
            'prescription_tariff' => $this->prescription_tariff,
            'is_active' => $this->is_active,
        ]);

        // Test both event types to see which one works
        $this->dispatch('show-alert', type: 'success', message: 'پروفایل مرکز درمانی با موفقیت به‌روزرسانی شد.');
        $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'پروفایل مرکز درمانی با موفقیت به‌روزرسانی شد.']);
    }

    public function render()
    {
        return view('livewire.mc.panel.profile.medical-center-profile-edit');
    }
}
