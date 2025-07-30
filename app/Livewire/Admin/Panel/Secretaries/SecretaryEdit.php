<?php

namespace App\Livewire\Admin\Panel\Secretaries;

use App\Models\Doctor;
use Livewire\Component;
use App\Models\Secretary;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\MedicalCenter;

class SecretaryEdit extends Component
{
    use WithFileUploads;

    public $secretary;
    public $first_name;
    public $last_name;
    public $mobile;
    public $national_code;
    public $gender;
    public $email;
    public $password;
    public $doctor_id;
    public $medical_center_id;
    public $is_active;
    public $profile_photo;

    public $doctors = [];
    public $clinics = [];

    public function mount($id)
    {
        $this->secretary = Secretary::findOrFail($id);
        $this->first_name = $this->secretary->first_name;
        $this->last_name = $this->secretary->last_name;
        $this->mobile = $this->secretary->mobile;
        $this->national_code = $this->secretary->national_code;
        $this->gender = $this->secretary->gender;
        $this->email = $this->secretary->email;
        $this->doctor_id = $this->secretary->doctor_id;
        $this->medical_center_id = $this->secretary->medical_center_id;
        $this->is_active = $this->secretary->is_active;

        $this->doctors = Doctor::all();
        $this->clinics = MedicalCenter::where('type', 'policlinic')->get();
    }

    public function updatedDoctorId($value)
    {
        $this->clinics = MedicalCenter::whereHas('doctors', function ($query) use ($value) {
            $query->where('doctor_id', $value);
        })->where('type', 'policlinic')->get();
        $this->medical_center_id = null;
        $this->dispatch('refresh-clinic-select2', clinics: $this->clinics->toArray());
    }

    public function getPhotoPreviewProperty()
    {
        return $this->profile_photo
            ? $this->profile_photo->temporaryUrl()
            : ($this->secretary->profile_photo_path
                ? Storage::url($this->secretary->profile_photo_path)
                : asset('admin-assets/images/default-avatar.png'));
    }

    public function update()
    {
        Log::info('update secretary', [
            'doctor_id' => $this->doctor_id,
            'medical_center_id' => $this->medical_center_id,
        ]);
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|regex:/^09[0-9]{9}$/|unique:secretaries,mobile,' . $this->secretary->id,
            'national_code' => 'required|digits:10|unique:secretaries,national_code,' . $this->secretary->id,
            'gender' => 'required|in:male,female',
            'email' => 'nullable|email|unique:secretaries,email,' . $this->secretary->id,
            'password' => 'nullable|min:6',
            'doctor_id' => 'nullable|exists:doctors,id',
            'medical_center_id' => 'required|exists:medical_centers,id',
            'is_active' => 'boolean',
            'profile_photo' => 'nullable|image|max:2048',
        ], [
            'first_name.required' => 'لطفاً نام را وارد کنید.',
            'first_name.max' => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'last_name.required' => 'لطفاً نام خانوادگی را وارد کنید.',
            'last_name.max' => 'نام خانوادگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
            'medical_center_id.required' => 'لطفاً  مطب را انتخاب کنید.',
            'mobile.regex' => 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'national_code.required' => 'لطفاً کد ملی را وارد کنید.',
            'national_code.digits' => 'کد ملی باید ۱۰ رقم باشد.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'gender.required' => 'لطفاً جنسیت را انتخاب کنید.',
            'gender.in' => 'جنسیت باید "مرد" یا "زن" باشد.',
            'email.email' => 'ایمیل واردشده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
            'doctor_id.exists' => 'دکتر انتخاب‌شده معتبر نیست.',
            'medical_center_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'profile_photo.image' => 'فایل باید تصویر باشد.',
            'profile_photo.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
        ]);

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mobile' => $this->mobile,
            'national_code' => $this->national_code,
            'gender' => $this->gender,
            'email' => $this->email,
            'doctor_id' => $this->doctor_id,
            'medical_center_id' => $this->medical_center_id,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->profile_photo) {
            if ($this->secretary->profile_photo_path) {
                Storage::disk('public')->delete($this->secretary->profile_photo_path);
            }
            $data['profile_photo_path'] = $this->profile_photo->store('photos', 'public');
        }

        $this->secretary->update($data);

        $this->dispatch('show-alert', type: 'success', message: 'منشی با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.secretaries.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.secretaries.secretary-edit');
    }
}
