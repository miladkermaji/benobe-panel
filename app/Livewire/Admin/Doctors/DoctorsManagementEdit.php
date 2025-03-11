<?php
namespace App\Livewire\Admin\Doctors;

use App\Models\Admin\Dashboard\Cities\Zone;
use App\Models\Admin\Doctors\DoctorManagement\DoctorTariff;
use App\Models\Doctor;
use App\Models\Dr\Clinic;
use Livewire\Component;
use Livewire\WithFileUploads;

class DoctorsManagementEdit extends Component
{
    use WithFileUploads;

    public $doctorId;
    public $first_name;
    public $last_name;
    public $mobile;
    public $license_number;
    public $sex;
    public $avatar;
    public $currentAvatar;
    public $aboutme;
    public $important_points;
    public $clinic_tel;
    public $clinic_address;
    public $province_id;
    public $city_id;
    public $specialties         = [];
    public $security            = 0;
    public $price_doctor_nobat  = 0;
    public $price_per_nobatsite = 0;
    public $status_moshavere    = true;
    public $status_nobatdehi    = true;
    public $send_sms            = false;
    public $auth                = false;
    public $status;

    public $provinces       = [];
    public $cities          = [];
    public $specialtiesList = [];

    public function mount(Doctor $doctor)
    {
        $this->doctorId         = $doctor->id;
        $this->first_name       = $doctor->first_name;
        $this->last_name        = $doctor->last_name;
        $this->mobile           = $doctor->mobile;
        $this->license_number   = $doctor->license_number;
        $this->sex              = $doctor->sex;
        $this->currentAvatar    = $doctor->profile_photo_path;
        $this->aboutme          = $doctor->bio;
        $this->important_points = $doctor->description;

        $clinic               = $doctor->clinics->first();
        $this->clinic_tel     = $clinic->phone_number ?? $doctor->alternative_mobile;
        $this->clinic_address = $clinic->address ?? $doctor->address;
        $this->province_id    = $clinic->province_id ?? $doctor->province_id;
        $this->city_id        = $clinic->city_id ?? $doctor->city_id;

        $this->specialties         = $doctor->specialties->pluck('id')->toArray();
        $this->security            = $doctor->two_factor_secret_enabled ? 1 : 0;
        $this->price_doctor_nobat  = $doctor->tariff->visit_fee ?? 0;
        $this->price_per_nobatsite = $doctor->tariff->site_fee ?? 0;
        $this->status_moshavere    = $doctor->is_active;
        $this->status_nobatdehi    = $doctor->is_active;
        $this->send_sms            = false;
        $this->auth                = $doctor->is_verified;
        $this->status              = $doctor->status;

        $this->provinces       = Zone::where('level', 1)->get();
        $this->cities          = $this->province_id ? Zone::where('level', 2)->where('parent_id', $this->province_id)->get() : collect();
        $this->specialtiesList = \App\Models\Dr\SubSpecialty::all();
    }

    public function updatedProvinceId($value)
    {
        $this->cities  = $value ? Zone::where('level', 2)->where('parent_id', $value)->get() : collect();
        $this->city_id = null; // ریست کردن شهر انتخاب‌شده
    }

    public function update()
    {
        $this->validate([
            'first_name'          => 'required|string|max:255',
            'last_name'           => 'required|string|max:255',
            'mobile'              => 'required|string|max:11|unique:doctors,mobile,' . $this->doctorId,
            'license_number'      => 'required|string|max:255',
            'sex'                 => 'required|in:male,female,other',
            'avatar'              => 'nullable|image',
            'aboutme'             => 'nullable|string',
            'important_points'    => 'nullable|string',
            'clinic_tel'          => 'nullable|string|max:11',
            'clinic_address'      => 'required|string|max:255',
            'province_id'         => 'required|exists:zone,id',
            'city_id'             => 'nullable|exists:zone,id',
            'specialties'         => 'required|array',
            'security'            => 'required|in:0,1',
            'price_doctor_nobat'  => 'required|integer|min:0',
            'price_per_nobatsite' => 'required|integer|min:0',
            'status_moshavere'    => 'boolean',
            'status_nobatdehi'    => 'boolean',
            'send_sms'            => 'boolean',
            'auth'                => 'boolean',
            'status'              => 'required|in:0,1,2,3,4',
        ]);

        $doctor = Doctor::findOrFail($this->doctorId);

        if ($this->avatar) {
            $path                       = $this->avatar->store('avatars', 'public');
            $doctor->profile_photo_path = $path;
        }

        $doctor->update([
            'first_name'                => $this->first_name,
            'last_name'                 => $this->last_name,
            'mobile'                    => $this->mobile,
            'license_number'            => $this->license_number,
            'sex'                       => $this->sex,
            'bio'                       => $this->aboutme,
            'description'               => $this->important_points,
            'is_active'                 => $this->status_moshavere && $this->status_nobatdehi,
            'is_verified'               => $this->auth,
            'two_factor_secret_enabled' => $this->security,
            'status'                    => $this->status,
        ]);

        $clinic               = $doctor->clinics->first() ?? new Clinic(['doctor_id' => $doctor->id]);
        $clinic->phone_number = $this->clinic_tel;
        $clinic->address      = $this->clinic_address;
        $clinic->province_id  = $this->province_id;
        $clinic->city_id      = $this->city_id;
        $clinic->save();

        $doctor->specialties()->sync($this->specialties);

        $tariff            = $doctor->tariff ?? new DoctorTariff(['doctor_id' => $doctor->id]);
        $tariff->visit_fee = $this->price_doctor_nobat;
        $tariff->site_fee  = $this->price_per_nobatsite;
        $tariff->save();

        $this->dispatch('toast', ['message' => 'اطلاعات پزشک با موفقیت به‌روزرسانی شد.', 'type' => 'success']);
        $this->redirect(route('admin.doctors.doctors-management.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.doctors.doctors-management-edit');
    }
}
