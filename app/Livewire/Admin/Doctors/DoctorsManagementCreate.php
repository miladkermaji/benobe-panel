<?php
namespace App\Livewire\Admin\Doctors;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Doctors\DoctorManagement\DoctorTariff;
use App\Models\Zone;
use Livewire\Component;
use Livewire\WithFileUploads;

class DoctorsManagementCreate extends Component
{
    use WithFileUploads;

    public $first_name;
    public $last_name;
    public $mobile;
    public $license_number;
    public $sex = 'male';
    public $avatar;
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
    public $status              = 0;

    public $provinces = [];
    public $cities    = [];

    public $specialtiesList = [];

    public function mount()
    {
        $this->provinces       = Zone::where('level', 1)->get();
        $this->cities          = collect(); // لیست شهرها ابتدا خالی است
        $this->specialtiesList = \App\Models\Specialty::all();
    }

    public function updatedProvinceId($value)
    {
        $this->cities  = $value ? Zone::where('level', 2)->where('parent_id', $value)->get() : collect();
        $this->city_id = null; // ریست کردن شهر انتخاب‌شده
    }

    public function create()
    {
        $this->validate([
            'first_name'          => 'required|string|max:255',
            'last_name'           => 'required|string|max:255',
            'mobile'              => 'required|string|max:11|unique:doctors,mobile',
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

        $doctor = new Doctor([
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

        if ($this->avatar) {
            $path                       = $this->avatar->store('avatars', 'public');
            $doctor->profile_photo_path = $path;
        }

        $doctor->save();

        $clinic = new Clinic([
            'doctor_id'    => $doctor->id,
            'phone_number' => $this->clinic_tel,
            'address'      => $this->clinic_address,
            'province_id'  => $this->province_id,
            'city_id'      => $this->city_id,
        ]);
        $clinic->save();

        $doctor->specialties()->sync($this->specialties);

        $tariff = new DoctorTariff([
            'doctor_id' => $doctor->id,
            'visit_fee' => $this->price_doctor_nobat,
            'site_fee'  => $this->price_per_nobatsite,
        ]);
        $tariff->save();

        $this->dispatch('toast', ['message' => 'پزشک با موفقیت ایجاد شد.', 'type' => 'success']);
        $this->redirect(route('admin.doctors.doctors-management.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.doctors.doctors-management-create');
    }
}
