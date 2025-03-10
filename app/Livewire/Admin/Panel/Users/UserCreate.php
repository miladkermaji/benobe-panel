<?php

namespace App\Livewire\Admin\Panel\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Dashboard\Cities\Zone;

class UserCreate extends Component
{
    use WithFileUploads;

    public $first_name;
    public $last_name;
    public $email;
    public $mobile;
    public $password;
    public $national_code;
    public $date_of_birth;
    public $sex = 'male';
    public $activation = true;
    public $photo;
    public $zone_province_id;
    public $zone_city_id;

    public $provinces = [];
    public $cities = [];

    public function mount()
    {
        $this->provinces = Zone::where('level', 1)->get();
        $this->cities = [];
    }

    public function updatedZoneProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->zone_city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }
    public function getPhotoPreviewProperty()
    {
        return $this->photo ? $this->photo->temporaryUrl() : asset('admin-assets/images/default-avatar.png');
    }
    public function store()
    {
        $validator = Validator::make([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'password' => $this->password,
            'national_code' => $this->national_code,
            'date_of_birth' => $this->date_of_birth,
            'sex' => $this->sex,
            'activation' => $this->activation,
            'photo' => $this->photo,
            'zone_province_id' => $this->zone_province_id,
            'zone_city_id' => $this->zone_city_id,
        ], [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|unique:users,mobile',
            'password' => 'required|string|min:8',
            'national_code' => 'nullable|string|unique:users,national_code',
            'date_of_birth' => 'nullable|string',
            'sex' => 'required|in:male,female',
            'activation' => 'required|boolean',
            'photo' => 'nullable|image|max:2048',
            'zone_province_id' => 'required|exists:zone,id',
            'zone_city_id' => 'required|exists:zone,id',
        ], [
            'first_name.required' => 'وارد کردن نام الزامی است.',
            'first_name.string' => 'نام باید رشته باشد.',
            'first_name.max' => 'نام نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'last_name.required' => 'وارد کردن نام خانوادگی الزامی است.',
            'last_name.string' => 'نام خانوادگی باید رشته باشد.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'email.required' => 'وارد کردن ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'mobile.required' => 'وارد کردن شماره موبایل الزامی است.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'password.required' => 'وارد کردن رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'sex.required' => 'انتخاب جنسیت الزامی است.',
            'sex.in' => 'جنسیت باید مرد یا زن باشد.',
            'activation.required' => 'وضعیت کاربر باید مشخص شود.',
            'photo.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'photo.max' => 'حجم تصویر نمی‌تواند بیش از ۲ مگابایت باشد.',
            'zone_province_id.required' => 'انتخاب استان الزامی است.',
            'zone_province_id.exists' => 'استان انتخاب‌شده معتبر نیست.',
            'zone_city_id.required' => 'انتخاب شهر الزامی است.',
            'zone_city_id.exists' => 'شهر انتخاب‌شده معتبر نیست.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $photoPath = $this->photo ? $this->photo->store('profile-photos', 'public') : null;

        User::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'password' => bcrypt($this->password),
            'national_code' => $this->national_code,
            'date_of_birth' => $this->date_of_birth,
            'sex' => $this->sex,
            'activation' => $this->activation,
            'profile_photo_path' => $photoPath,
            'zone_province_id' => $this->zone_province_id,
            'zone_city_id' => $this->zone_city_id,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'کاربر با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.users.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.users.user-create');
    }
}