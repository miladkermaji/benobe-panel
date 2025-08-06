<?php

namespace App\Livewire\Admin\Panel\Managers;

use App\Models\Manager;
use App\Helpers\JalaliHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ManagerCreate extends Component
{
    public $first_name = '';
    public $last_name = '';
    public $date_of_birth = '';
    public $national_code = '';
    public $gender = '';
    public $email = '';
    public $mobile = '';
    public $password = '';
    public $password_confirmation = '';
    public $two_factor_enabled = false;
    public $static_password_enabled = false;
    public $static_password = '';
    public $static_password_confirmation = '';
    public $bio = '';
    public $address = '';
    public $permission_level = 1;
    public $is_active = true;
    public $is_verified = false;

    protected $rules = [
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'date_of_birth' => 'nullable|date|before:today',
        'national_code' => 'nullable|string|size:10|unique:managers,national_code',
        'gender' => 'nullable|in:male,female,other',
        'email' => 'required|email|unique:managers,email',
        'mobile' => 'nullable|string|max:15|unique:managers,mobile',
        'password' => 'nullable|string|min:8|confirmed',
        'two_factor_enabled' => 'boolean',
        'static_password_enabled' => 'boolean',
        'static_password' => 'nullable|string|min:6|confirmed',
        'bio' => 'nullable|string',
        'address' => 'nullable|string',
        'permission_level' => 'required|integer|in:1,2',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    protected $messages = [
        'first_name.required' => 'نام الزامی است.',
        'first_name.max' => 'نام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
        'last_name.required' => 'نام خانوادگی الزامی است.',
        'last_name.max' => 'نام خانوادگی نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
        'date_of_birth.date' => 'تاریخ تولد باید معتبر باشد.',
        'date_of_birth.before' => 'تاریخ تولد باید قبل از امروز باشد.',
        'national_code.size' => 'کد ملی باید ۱۰ رقم باشد.',
        'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
        'gender.in' => 'جنسیت باید یکی از مقادیر مرد، زن یا سایر باشد.',
        'email.required' => 'ایمیل الزامی است.',
        'email.email' => 'ایمیل باید معتبر باشد.',
        'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
        'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
        'password.required' => 'رمز عبور الزامی است.',
        'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد.',
        'static_password.min' => 'رمز عبور ثابت باید حداقل ۶ کاراکتر باشد.',
        'static_password.confirmed' => 'تکرار رمز عبور ثابت مطابقت ندارد.',
        'permission_level.required' => 'سطح دسترسی الزامی است.',
        'permission_level.in' => 'سطح دسترسی باید ۱ یا ۲ باشد.',
    ];

    public function updatedStaticPasswordEnabled($value)
    {
        if (!$value) {
            $this->static_password = '';
            $this->static_password_confirmation = '';
        }
    }

    public function save()
    {
        // اعتبارسنجی رمز عبور ثابت
        if ($this->static_password_enabled) {
            $this->rules['static_password'] = 'required|string|min:6|confirmed';
            $this->messages['static_password.required'] = 'رمز عبور ثابت الزامی است.';
        } else {
            $this->rules['static_password'] = 'nullable|string|min:6|confirmed';
        }

        $this->validate();

        try {
            // تبدیل تاریخ جلالی به میلادی
            $gregorianDateOfBirth = null;
            if ($this->date_of_birth) {
                $gregorianDateOfBirth = JalaliHelper::jalaliToGregorian($this->date_of_birth);
                if (!$gregorianDateOfBirth) {
                    $this->addError('date_of_birth', 'تاریخ تولد نامعتبر است.');
                    return;
                }
            }

            $manager = Manager::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'date_of_birth' => $gregorianDateOfBirth,
                'national_code' => $this->national_code ?: null,
                'gender' => $this->gender ?: null,
                'email' => $this->email,
                'mobile' => $this->mobile ?: null,
                'password' => Hash::make('default123'),
                'two_factor_enabled' => $this->two_factor_enabled,
                'static_password_enabled' => $this->static_password_enabled,
                'static_password' => $this->static_password_enabled ? $this->static_password : null,
                'bio' => $this->bio ?: null,
                'address' => $this->address ?: null,
                'permission_level' => $this->permission_level,
                'is_active' => $this->is_active,
                'is_verified' => $this->is_verified,
                'profile_completed' => true,
            ]);

            $this->dispatch('show-alert', type: 'success', message: 'مدیر با موفقیت ایجاد شد!');
            return redirect()->route('admin.panel.managers.index');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ایجاد مدیر: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.managers.manager-create');
    }
}
