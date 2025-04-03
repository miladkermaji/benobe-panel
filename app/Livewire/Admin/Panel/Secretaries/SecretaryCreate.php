<?php

namespace App\Livewire\Admin\Panel\Secretaries;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Secretary;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\SecretaryPermission;
use Illuminate\Support\Facades\Hash;

class SecretaryCreate extends Component
{
    use WithFileUploads;

    public $first_name;
    public $last_name;
    public $mobile;
    public $national_code;
    public $gender = 'male';
    public $email;
    public $password;
    public $doctor_id;
    public $clinic_id;
    public $is_active = true;
    public $profile_photo;

    public $doctors = [];
    public $clinics = [];

    public function mount()
    {
        $this->doctors = Doctor::all();
        $this->clinics = Clinic::all();
    }

    public function getPhotoPreviewProperty()
    {
        return $this->profile_photo ? $this->profile_photo->temporaryUrl() : asset('admin-assets/images/default-avatar.png');
    }

    public function store()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|regex:/^09[0-9]{9}$/|unique:secretaries,mobile',
            'national_code' => 'required|digits:10|unique:secretaries,national_code',
            'gender' => 'required|in:male,female',
            'email' => 'nullable|email|unique:secretaries,email',
            'password' => 'nullable|min:6',
            'doctor_id' => 'nullable|exists:doctors,id',
            'clinic_id' => 'nullable|exists:clinics,id',
            'is_active' => 'boolean',
            'profile_photo' => 'nullable|image|max:2048',
        ], [
            'first_name.required' => 'لطفاً نام را وارد کنید.',
            'first_name.max' => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'last_name.required' => 'لطفاً نام خانوادگی را وارد کنید.',
            'last_name.max' => 'نام خانوادگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
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
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
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
            'password' => $this->password ? Hash::make($this->password) : null,
            'doctor_id' => $this->doctor_id,
            'clinic_id' => $this->clinic_id,
            'is_active' => $this->is_active,
        ];

        if ($this->profile_photo) {
            $data['profile_photo_path'] = $this->profile_photo->store('photos', 'public');
        }

        $secretary = Secretary::create($data);

        // ذخیره دسترسی‌های پیش‌فرض
        SecretaryPermission::create([
            'secretary_id' => $secretary->id,
            'doctor_id' => $this->doctor_id,
            'clinic_id' => $this->clinic_id,
            'permissions' => json_encode([
                "dashboard",
                "appointments",
                "dr-appointments",
                "dr-workhours",
                "dr-mySpecialDays",
                "dr-manual_nobat_setting",
                "dr-manual_nobat",
                "dr-scheduleSetting",
                "consult",
                "dr-moshavere_setting",
                "dr-moshavere_waiting",
                "consult-term.index",
                "dr-mySpecialDays-counseling",
                "prescription",
                "prescription.index",
                "providers.index",
                "favorite.templates.index",
                "templates.favorite.service.index",
                "patient_records",
                "dr-patient-records",
                "clinic_management",
                "dr-clinic-management",
                "dr-office-gallery",
                "dr-office-medicalDoc",
                "messages",
                "dr-panel-tickets",
            ]),
            'has_access' => true,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'منشی با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.secretaries.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.secretaries.secretary-create');
    }
}
