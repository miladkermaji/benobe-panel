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
    public $phone_numbers = [''];
    public $province_id;
    public $city_id;
    public $postal_code;
    public $address;
    public $description;
    public $provinces;
    public $cities;
    public $clinic_id;
    public $prescription_fee = null;

    public function mount($id)
    {
        $this->clinic = \App\Models\Clinic::findOrFail($id);
        $this->clinic_id = $id;
        $this->name = $this->clinic->name;
        $this->phone_numbers = json_decode($this->clinic->phone_numbers, true) ?? [''];
        $this->province_id = $this->clinic->province_id;
        $this->city_id = $this->clinic->city_id;
        $this->postal_code = $this->clinic->postal_code;
        $this->address = $this->clinic->address;
        $this->description = $this->clinic->description;
        $this->prescription_fee = $this->clinic->prescription_fee;

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
            'phone_numbers' => $this->phone_numbers,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'description' => $this->description,
            'prescription_fee' => $this->prescription_fee,
        ], [
            'name' => 'required|string|max:255',
            'phone_numbers' => 'required|array|min:1',
            'phone_numbers.*' => 'required|string|max:15',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'postal_code' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'prescription_fee' => 'nullable|numeric|min:0',
        ], [
            'name.required' => 'وارد کردن نام مطب الزامی است.',
            'phone_numbers.required' => 'وارد کردن حداقل یک شماره موبایل الزامی است.',
            'phone_numbers.*.required' => 'وارد کردن شماره موبایل الزامی است.',
            'province_id.required' => 'انتخاب استان الزامی است.',
            'city_id.required' => 'انتخاب شهر الزامی است.',
            'prescription_fee.numeric' => 'تعرفه نسخه باید عددی باشد.',
            'prescription_fee.min' => 'تعرفه نسخه نمی‌تواند منفی باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $this->clinic->update([
            'name' => $this->name,
            'phone_numbers' => json_encode($this->phone_numbers),
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'description' => $this->description,
            'prescription_fee' => $this->prescription_fee,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'مطب با موفقیت ویرایش شد!');
        return redirect()->route('dr-clinic-management');
    }

    public function render()
    {
        return view('livewire.dr.panel.doctors-clinic.doctor-clinic-edit');
    }
}
