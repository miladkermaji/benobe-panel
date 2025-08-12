<?php

namespace App\Livewire\Mc\Panel\Secretary;

use App\Models\Zone;
use Livewire\Component;
use App\Models\Secretary;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Traits\HasSelectedDoctor;

class SecretaryCreate extends Component
{
    use HasSelectedDoctor;

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
    public $medical_center_id;

    public function mount()
    {
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
            $this->medical_center_id = $this->getSelectedMedicalCenterId();
        }

        Log::info('mount medical_center_id', ['medical_center_id' => $this->medical_center_id]);
    }

    public function updatedProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function store()
    {
        if (Auth::guard('medical_center')->check()) {
            $clinicId = Auth::guard('medical_center')->id();
            $doctorId = $this->getSelectedDoctorId();
        } else {
            $doctorId = $this->getDoctor()?->id;
            $clinicId = $this->getSelectedMedicalCenterId();
        }

        if (!$clinicId) {
            $this->addError('medical_center_id', 'مرکز درمانی انتخاب نشده است.');
            return;
        }

        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|regex:/^09[0-9]{9}$/|unique:secretaries,mobile,NULL,id,doctor_id,' . ($doctorId ?? 'NULL') . ',medical_center_id,' . $clinicId,
            'national_code' => 'required|digits:10|unique:secretaries,national_code,NULL,id,doctor_id,' . ($doctorId ?? 'NULL') . ',medical_center_id,' . $clinicId,
            'gender' => 'required|in:male,female',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'password' => 'nullable|min:6',
        ]);

        Secretary::create([
            'doctor_id' => $doctorId,
            'medical_center_id' => $clinicId,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mobile' => $this->mobile,
            'national_code' => $this->national_code,
            'gender' => $this->gender,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'password' => $this->password ? Hash::make($this->password) : null,
            'status' => 1,
        ]);

        $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'منشی با موفقیت اضافه شد.']);
        return redirect()->route('mc-secretary-management');
    }

    public function render()
    {
        return view('livewire.mc.panel.secretary.secretary-create', [
            'provinces' => $this->provinces,
            'cities' => $this->cities,
        ]);
    }
}
