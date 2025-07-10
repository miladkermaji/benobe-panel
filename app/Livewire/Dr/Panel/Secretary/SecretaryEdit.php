<?php

namespace App\Livewire\Dr\Panel\Secretary;

use Livewire\Component;
use App\Models\Secretary;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Traits\HasSelectedClinic;

class SecretaryEdit extends Component
{
    use HasSelectedClinic;
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
    public $clinic_id;

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
        $this->clinic_id = $this->getSelectedClinicId();
    }

    public function updatedProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function update()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|regex:/^09[0-9]{9}$/',
            'national_code' => 'required|digits:10',
            'gender' => 'required|in:male,female',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'password' => 'nullable|min:6',
        ]);
        $this->secretary->update([
            'clinic_id' => $this->clinic_id,
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
        return redirect()->route('dr-secretary-management');
    }

    public function render()
    {
        return view('livewire.dr.panel.secretary.secretary-edit', [
            'provinces' => $this->provinces,
            'cities' => $this->cities,
        ]);
    }
}
