<?php

namespace App\Livewire\Admin\Hospitals\HospitalsManagement;

use Livewire\Component;
use App\Models\Dr\Clinic;
use App\Models\Dr\Doctor;
use App\Models\Admin\Dashboard\Cities\Zone;

class HospitalCreate extends Component
{
    public $doctor_id;
    public $name;
    public $phone_number;
    public $province_id;
    public $city_id;
    public $address;

    public $provinces = [];
    public $cities = [];

    protected $rules = [
        'doctor_id' => 'required|exists:doctors,id',
        'name' => 'required|string|max:255',
        'phone_number' => 'nullable|string|max:20|regex:/^09[0-9]{9}$/',
        'province_id' => 'nullable|exists:zone,id',
        'city_id' => 'nullable|exists:zone,id',
        'address' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'doctor_id.required' => 'لطفاً پزشک مسئول را انتخاب کنید.',
        'name.required' => 'نام بیمارستان الزامی است.',
        'phone_number.regex' => 'شماره تماس باید با فرمت صحیح (مثل 09123456789) باشد.',
    ];

    public function mount()
    {
        $this->provinces = Zone::where('level', 1)->get();
    }

    public function updatedProvinceId($value)
    {
        if ($value) {
            $this->cities = Zone::where('parent_id', $value)->where('level', 2)->get();
            $this->city_id = null; // ریست کردن شهر
        } else {
            $this->cities = [];
            $this->city_id = null;
        }
    }

    public function save()
    {
        $this->validate();

        Clinic::create([
            'doctor_id' => $this->doctor_id,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'address' => $this->address,
        ]);

        $this->dispatch('toast', ['message' => 'بیمارستان با موفقیت اضافه شد.', 'type' => 'success']);
        $this->reset(['doctor_id', 'name', 'phone_number', 'province_id', 'city_id', 'address']);
    }

    public function render()
    {
        $doctors = Doctor::all();
        return view('livewire.admin.hospitals.hospitals-management.hospital-create', compact('doctors'));
    }
}