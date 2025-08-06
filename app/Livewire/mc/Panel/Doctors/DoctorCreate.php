<?php

namespace App\Livewire\Mc\Panel\Doctors;

use App\Models\Doctor;
use App\Models\Zone;
use App\Models\Specialty;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;
use App\Models\MedicalCenter;

class DoctorCreate extends Component
{
    use WithFileUploads;

    // Personal Information
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $mobile = '';
    public $password = '';
    public $national_code = '';
    public $date_of_birth = '';
    public $sex = '';

    // Location Information
    public $province_id = '';
    public $city_id = '';
    public $address = '';
    public $postal_code = '';

    // Professional Information
    public $license_number = '';
    public $specialty_id = '';
    public $bio = '';
    public $description = '';

    // Photo
    public $photo;

    // Dropdown data
    public $provinces = [];
    public $cities = [];
    public $specialties = [];

    public function mount()
    {
        $this->provinces = Zone::where('level', 1)->get();
        $this->specialties = Specialty::all();
    }

    public function updatedProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities);
    }

    public function getPhotoPreviewProperty()
    {
        return $this->photo ? $this->photo->temporaryUrl() : null;
    }

    public function store()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email',
            'mobile' => 'required|string|max:20|unique:doctors,mobile',
            'password' => 'required|string|min:8',
            'national_code' => 'required|string|max:20|unique:doctors,national_code',
            'date_of_birth' => 'required|string|max:255',
            'sex' => 'required|in:male,female',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'license_number' => 'required|string|max:255|unique:doctors,license_number',
            'specialty_id' => 'required|exists:specialties,id',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|max:2048',
        ], [
            'first_name.required' => 'نام الزامی است.',
            'last_name.required' => 'نام خانوادگی الزامی است.',
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'mobile.required' => 'موبایل الزامی است.',
            'mobile.unique' => 'این موبایل قبلاً ثبت شده است.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'date_of_birth.required' => 'تاریخ تولد الزامی است.',
            'sex.required' => 'جنسیت الزامی است.',
            'province_id.required' => 'استان الزامی است.',
            'city_id.required' => 'شهر الزامی است.',
            'license_number.required' => 'کد نظام پزشکی الزامی است.',
            'license_number.unique' => 'این کد نظام پزشکی قبلاً ثبت شده است.',
            'specialty_id.required' => 'تخصص الزامی است.',
            'photo.image' => 'فایل باید تصویر باشد.',
            'photo.max' => 'حجم تصویر نباید بیشتر از 2 مگابایت باشد.',
        ]);

        try {
            // Create doctor
            $doctor = Doctor::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'mobile' => $this->mobile,
                'password' => Hash::make($this->password),
                'national_code' => $this->national_code,
                'date_of_birth' => $this->date_of_birth,
                'sex' => $this->sex,
                'province_id' => $this->province_id,
                'city_id' => $this->city_id,
                'address' => $this->address,
                'postal_code' => $this->postal_code,
                'license_number' => $this->license_number,
                'specialty_id' => $this->specialty_id,
                'bio' => $this->bio,
                'description' => $this->description,
                'is_active' => false, // Default to inactive as requested
            ]);

            // Store photo if uploaded
            if ($this->photo) {
                $photoPath = $this->photo->store('doctors/photos', 'public');
                $doctor->update(['photo' => $photoPath]);
            }

            // Associate doctor with medical center
            /** @var MedicalCenter $medicalCenter */
            $medicalCenter = Auth::guard('medical_center')->user();
            $medicalCenter->doctors()->attach($doctor->id);

            // Clear cache
            Cache::forget('mc_doctors_' . $medicalCenter->id . '_*');

            $this->dispatch('show-toastr', ['message' => 'پزشک با موفقیت ایجاد شد.', 'type' => 'success']);

            // Redirect to doctors index page
            return redirect()->route('mc.panel.doctors.index');

        } catch (\Exception $e) {
            $this->dispatch('show-toastr', ['message' => 'خطا در ایجاد پزشک: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function render()
    {
        return view('livewire.mc.panel.doctors.doctor-create');
    }
}
