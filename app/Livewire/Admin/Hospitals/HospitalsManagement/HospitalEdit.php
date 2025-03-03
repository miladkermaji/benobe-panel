<?php

namespace App\Livewire\Admin\Hospitals\HospitalsManagement;

use Livewire\Component;
use App\Models\Dr\Clinic;
use App\Models\Dr\Doctor;
use App\Models\Admin\Dashboard\Cities\Zone;

class HospitalEdit extends Component
{
    public $hospitalId;
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

    public function mount($id)
    {
        $this->hospitalId = $id;
        $hospital = Clinic::findOrFail($id);
        $this->doctor_id = $hospital->doctor_id;
        $this->name = $hospital->name;
        $this->phone_number = $hospital->phone_number;
        $this->province_id = $hospital->province_id;
        $this->city_id = $hospital->city_id;
        $this->address = $hospital->address;

        $this->provinces = Zone::where('level', 1)->get();
        $this->cities = Zone::where('parent_id', $this->province_id)->where('level', 2)->get();
    }

    public function updatedProvinceId($value)
    {
        if ($value) {
            $this->cities = Zone::where('parent_id', $value)->where('level', 2)->get();
            $this->city_id = null;
        } else {
            $this->cities = [];
            $this->city_id = null;
        }
    }

    public function save()
    {
        $this->validate();

        $hospital = Clinic::findOrFail($this->hospitalId);
        $hospital->update([
            'doctor_id' => $this->doctor_id,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'address' => $this->address,
        ]);

        $this->dispatch('toast', ['message' => 'بیمارستان با موفقیت ویرایش شد.', 'type' => 'success']);
    }

    public function render()
    {
        $doctors = Doctor::all();
        return view('livewire.admin.hospitals.hospitals-management.hospital-edit', compact('doctors'));
    }
}