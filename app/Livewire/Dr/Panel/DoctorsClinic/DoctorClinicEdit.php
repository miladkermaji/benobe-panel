<?php

namespace App\Livewire\Dr\Panel\DoctorsClinic;

use App\Models\Zone;
use App\Models\Clinic;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class DoctorClinicEdit extends Component
{
    public $clinic;
    public $name;
    public $title;
    public $phone_numbers = [''];
    public $secretary_phone;
    public $phone_number;
    public $province_id;
    public $city_id;
    public $postal_code;
    public $address;
    public $description;
    public $provinces;
    public $cities;
    public $medical_center_id;
    public $prescription_tariff = null;
    public $type = 'policlinic';

    public function mount($id)
    {
        $this->clinic = \App\Models\MedicalCenter::findOrFail($id);
        $this->medical_center_id = $id;
        $this->name = $this->clinic->name;
        $this->title = $this->clinic->title;

        // Handle phone_numbers - could be JSON string or array
        $phoneNumbers = $this->clinic->phone_numbers;
        if (is_string($phoneNumbers)) {
            $phoneNumbers = json_decode($phoneNumbers, true);
        }
        $this->phone_numbers = is_array($phoneNumbers) && !empty($phoneNumbers) ? $phoneNumbers : [''];

        $this->secretary_phone = $this->clinic->secretary_phone;
        $this->phone_number = $this->clinic->phone_number;
        $this->province_id = $this->clinic->province_id;
        $this->city_id = $this->clinic->city_id;
        $this->postal_code = $this->clinic->postal_code;
        $this->address = $this->clinic->address;
        $this->description = $this->clinic->description;
        $this->prescription_tariff = $this->clinic->prescription_tariff;
        $this->type = $this->clinic->type ?? 'policlinic';

        $zones = Cache::remember('zones', 86400, function () {
            return \App\Models\Zone::where('status', 1)
                ->orderBy('sort')
                ->get(['id', 'name', 'parent_id', 'level']);
        });
        $this->provinces = $zones->where('level', 1)->values();
        $this->cities = collect(); // مقداردهی اولیه صحیح
        if ($this->province_id) {
            $this->cities = \App\Models\Zone::where('level', 2)->where('parent_id', $this->province_id)->get();
        }
    }

    public function updatedProvinceId($value)
    {
        $this->cities = \App\Models\Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function update()
    {
        $validator = Validator::make([
            'name' => $this->name,
            'title' => $this->title,
            'phone_numbers' => $this->phone_numbers,
            'secretary_phone' => $this->secretary_phone,
            'phone_number' => $this->phone_number,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'description' => $this->description,
            'prescription_tariff' => $this->prescription_tariff,
            'type' => $this->type,
        ], [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'phone_numbers' => 'required|array|min:1',
            'phone_numbers.*' => 'required|string|max:15',
            'secretary_phone' => 'nullable|string|max:15',
            'phone_number' => 'nullable|string|max:15',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'postal_code' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'prescription_tariff' => 'nullable|numeric|min:0',
            'type' => 'required|in:hospital,treatment_centers,clinic,imaging_center,laboratory,pharmacy,policlinic',
        ], [
            'name.required' => 'وارد کردن نام مطب الزامی است.',
            'phone_numbers.required' => 'وارد کردن حداقل یک شماره موبایل الزامی است.',
            'phone_numbers.*.required' => 'وارد کردن شماره موبایل الزامی است.',
            'province_id.required' => 'انتخاب استان الزامی است.',
            'city_id.required' => 'انتخاب شهر الزامی است.',
            'prescription_tariff.numeric' => 'تعرفه نسخه باید عددی باشد.',
            'prescription_tariff.min' => 'تعرفه نسخه نمی‌تواند منفی باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $this->clinic->update([
            'name' => $this->name,
            'title' => $this->title,
            'phone_numbers' => $this->phone_numbers,
            'secretary_phone' => $this->secretary_phone,
            'phone_number' => $this->phone_number,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'description' => $this->description,
            'prescription_tariff' => $this->prescription_tariff,
            'type' => $this->type,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'مطب با موفقیت ویرایش شد!');
        return redirect()->route('dr-clinic-management');
    }

    public function render()
    {
        return view('livewire.dr.panel.doctors-clinic.doctor-clinic-edit');
    }
}
