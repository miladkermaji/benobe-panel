<?php

namespace App\Livewire\Mc\Panel\Doctors;

use App\Models\Doctor;
use App\Models\Zone;
use App\Models\Specialty;
use App\Models\MedicalCenter;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class DoctorEdit extends Component
{
    use WithFileUploads;

    public $doctorId;
    public $doctor;

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
    public $current_photo = '';

    // Dropdown data
    public $provinces = [];
    public $cities = [];
    public $specialties = [];

    public function mount($id)
    {
        $this->doctorId = $id;
        $this->doctor = Doctor::findOrFail($id);

        // Check if doctor belongs to this medical center
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        if (!$medicalCenter->doctors()->where('doctors.id', $id)->exists()) {
            abort(403, 'این پزشک در مرکز درمانی شما ثبت نشده است.');
        }

        // Populate form fields
        $this->first_name = $this->doctor->first_name;
        $this->last_name = $this->doctor->last_name;
        $this->email = $this->doctor->email;
        $this->mobile = $this->doctor->mobile;
        $this->national_code = $this->doctor->national_code;

        // Convert date_of_birth to Jalali format for display
        if ($this->doctor->date_of_birth) {
            try {
                // Always convert to Jalali for display, regardless of storage format
                $carbonDate = Carbon::parse($this->doctor->date_of_birth);
                $jalaliDate = Jalalian::fromDateTime($carbonDate);
                $this->date_of_birth = $jalaliDate->format('Y/m/d');
            } catch (\Exception $e) {
                // If conversion fails, try to handle as string
                if (strpos($this->doctor->date_of_birth, 'T') !== false) {
                    // ISO format - extract date part
                    $datePart = explode('T', $this->doctor->date_of_birth)[0];
                    $carbonDate = Carbon::parse($datePart);
                    $jalaliDate = Jalalian::fromDateTime($carbonDate);
                    $this->date_of_birth = $jalaliDate->format('Y/m/d');
                } else {
                    // Keep original if all conversions fail
                    $this->date_of_birth = $this->doctor->date_of_birth;
                }
            }
        } else {
            $this->date_of_birth = '';
        }

        $this->sex = $this->doctor->sex;
        $this->province_id = $this->doctor->province_id;
        $this->city_id = $this->doctor->city_id;
        $this->address = $this->doctor->address;
        $this->postal_code = $this->doctor->postal_code;
        $this->license_number = $this->doctor->license_number;
        $this->specialty_id = $this->doctor->specialty_id;
        $this->bio = $this->doctor->bio;
        $this->description = $this->doctor->description;
        $this->current_photo = $this->doctor->photo;

        // Load dropdown data
        $this->provinces = Zone::where('level', 1)->get();
        $this->specialties = Specialty::all();

        if ($this->province_id) {
            $this->cities = Zone::where('level', 2)->where('parent_id', $this->province_id)->get();
        }
    }

    public function updatedProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities);
    }

    public function getPhotoPreviewProperty()
    {
        if ($this->photo) {
            return $this->photo->temporaryUrl();
        }
        return $this->current_photo ? asset('storage/' . $this->current_photo) : null;
    }

    public function update()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:doctors,email,' . $this->doctorId,
            'mobile' => 'required|string|max:20|unique:doctors,mobile,' . $this->doctorId,
            'password' => 'nullable|string|min:8',
            'national_code' => 'required|string|max:20|unique:doctors,national_code,' . $this->doctorId,
            'date_of_birth' => 'required|string|max:255',
            'sex' => 'required|in:male,female',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'license_number' => 'required|string|max:255|unique:doctors,license_number,' . $this->doctorId,
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
            // Convert Jalali date to Gregorian for database storage
            $gregorianDate = null;
            if ($this->date_of_birth) {
                try {
                    // Parse Jalali date (Y/m/d format) and convert to Gregorian
                    $jalaliDate = Jalalian::fromFormat('Y/m/d', $this->date_of_birth);
                    $gregorianDate = $jalaliDate->toCarbon();
                } catch (\Exception $e) {
                    // If conversion fails, try to parse as Carbon
                    $gregorianDate = Carbon::parse($this->date_of_birth);
                }
            }

            // Update doctor data
            $updateData = [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'mobile' => $this->mobile,
                'national_code' => $this->national_code,
                'date_of_birth' => $gregorianDate,
                'sex' => $this->sex,
                'province_id' => $this->province_id,
                'city_id' => $this->city_id,
                'address' => $this->address,
                'postal_code' => $this->postal_code,
                'license_number' => $this->license_number,
                'specialty_id' => $this->specialty_id,
                'bio' => $this->bio,
                'description' => $this->description,
            ];

            // Update password if provided
            if ($this->password) {
                $updateData['password'] = Hash::make($this->password);
            }

            $this->doctor->update($updateData);

            // Handle photo upload
            if ($this->photo) {
                // Delete old photo if exists
                if ($this->current_photo && file_exists(storage_path('app/public/' . $this->current_photo))) {
                    unlink(storage_path('app/public/' . $this->current_photo));
                }

                $photoPath = $this->photo->store('doctors/photos', 'public');
                $this->doctor->update(['photo' => $photoPath]);
                $this->current_photo = $photoPath;
            }

            // Clear cache
            /** @var MedicalCenter $medicalCenter */
            $medicalCenter = Auth::guard('medical_center')->user();
            Cache::forget('mc_doctors_' . $medicalCenter->id . '_*');

            $this->dispatch('show-toastr', ['message' => 'اطلاعات پزشک با موفقیت به‌روزرسانی شد.', 'type' => 'success']);

            // Redirect to doctors index page
            return redirect()->route('mc.panel.doctors.index');

        } catch (\Exception $e) {
            $this->dispatch('show-toastr', ['message' => 'خطا در به‌روزرسانی پزشک: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function render()
    {
        return view('livewire.mc.panel.doctors.doctor-edit');
    }
}
