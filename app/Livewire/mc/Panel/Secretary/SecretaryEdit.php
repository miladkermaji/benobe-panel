<?php

namespace App\Livewire\Mc\Panel\Secretary;

use Livewire\Component;
use App\Models\Secretary;
use App\Models\Zone;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecretaryEdit extends Component
{
    public $secretary;
    public $first_name;
    public $last_name;
    public $mobile;
    public $national_code;
    public $gender;
    public $password;
    public $province_id;
    public $city_id;
    public $provinces;
    public $cities;
    public $secretary_id;
    public $medical_center_id;

    public function mount($id)
    {
        $this->secretary = Secretary::findOrFail($id);
        $this->secretary_id = $id;
        $this->first_name = $this->secretary->first_name;
        $this->last_name = $this->secretary->last_name;
        $this->mobile = $this->secretary->mobile;
        $this->national_code = $this->secretary->national_code;
        $this->gender = $this->secretary->gender;
        $this->province_id = $this->secretary->province_id;
        $this->city_id = $this->secretary->city_id;
        $zones = Cache::remember('zones', 86400, function () {
            return Zone::where('status', 1)
                ->orderBy('sort')
                ->get(['id', 'name', 'parent_id', 'level']);
        });
        $this->provinces = $zones->where('level', 1)->values();
        $this->cities = collect();
        if ($this->province_id) {
            $this->cities = Zone::where('level', 2)->where('parent_id', $this->province_id)->get();
        }
        if (Auth::guard('medical_center')->check()) {
            $this->medical_center_id = Auth::guard('medical_center')->id();
        } else {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $doctor = Doctor::find($doctorId);
            $this->medical_center_id = $doctor?->selectedMedicalCenter?->medical_center_id;
        }
        Log::info('edit mount medical_center_id', ['medical_center_id' => $this->medical_center_id]);
    }

    public function updatedProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function update()
    {
        if (Auth::guard('medical_center')->check()) {
            $clinicId = Auth::guard('medical_center')->id();
            $doctorId = null;
        } else {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $doctor = Doctor::find($doctorId);
            $clinicId = $doctor?->selectedMedicalCenter?->medical_center_id;
        }
        if (!$clinicId) {
            $this->addError('medical_center_id', 'مرکز درمانی انتخاب نشده است.');
            return;
        }
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|regex:/^09[0-9]{9}$/|unique:secretaries,mobile,' . $this->secretary_id . ',id,doctor_id,' . ($doctorId ?? 'NULL') . ',medical_center_id,' . $clinicId,
            'national_code' => 'required|digits:10|unique:secretaries,national_code,' . $this->secretary_id . ',id,doctor_id,' . ($doctorId ?? 'NULL') . ',medical_center_id,' . $clinicId,
            'gender' => 'required|in:male,female',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'password' => 'nullable|min:6',
        ]);
        $this->secretary->update([
            'medical_center_id' => $clinicId,
            'doctor_id' => $doctorId,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mobile' => $this->mobile,
            'national_code' => $this->national_code,
            'gender' => $this->gender,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'password' => $this->password ? Hash::make($this->password) : $this->secretary->password,
        ]);
        $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'منشی با موفقیت ویرایش شد.']);
        return redirect()->route('mc-secretary-management');
    }

    public function render()
    {
        return view('livewire.mc.panel.secretary.secretary-edit', [
            'provinces' => $this->provinces,
            'cities' => $this->cities,
        ]);
    }
}
