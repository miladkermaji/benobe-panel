<?php

namespace App\Livewire\Admin\Panel\Managers;

use App\Models\Manager;
use App\Helpers\JalaliHelper;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ManagerEdit extends Component
{
    public $managerId;
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
        'national_code' => 'nullable|string|size:10',
        'gender' => 'nullable|in:male,female,other',
        'email' => 'required|email',
        'mobile' => 'nullable|string|max:15',
        'password' => 'nullable|string|min:8|confirmed',
        'two_factor_enabled' => 'boolean',
        'static_password_enabled' => 'boolean',
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
        'gender.in' => 'جنسیت باید یکی از مقادیر مرد، زن یا سایر باشد.',
        'email.required' => 'ایمیل الزامی است.',
        'email.email' => 'ایمیل باید معتبر باشد.',
        'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        'password.confirmed' => 'تکرار رمز عبور مطابقت ندارد.',
        'permission_level.required' => 'سطح دسترسی الزامی است.',
        'permission_level.in' => 'سطح دسترسی باید ۱ یا ۲ باشد.',
    ];

    public function mount($id)
    {
        $this->managerId = $id;
        $manager = Manager::findOrFail($id);

        $this->first_name = $manager->first_name;
        $this->last_name = $manager->last_name;
        // تبدیل تاریخ میلادی به جلالی برای نمایش
        $this->date_of_birth = $manager->date_of_birth ? JalaliHelper::toJalaliDate($manager->date_of_birth) : '';
        $this->national_code = $manager->national_code;
        $this->gender = $manager->gender;
        $this->email = $manager->email;
        $this->mobile = $manager->mobile;
        $this->two_factor_enabled = $manager->two_factor_enabled;
        $this->static_password_enabled = $manager->static_password_enabled;
        $this->static_password = $manager->static_password;
        $this->bio = $manager->bio;
        $this->address = $manager->address;
        $this->permission_level = $manager->permission_level;
        $this->is_active = $manager->is_active;
        $this->is_verified = $manager->is_verified;
    }

    public function updatedEmail()
    {
        $this->validateOnly('email', [
            'email' => 'required|email|unique:managers,email,' . $this->managerId,
        ], [
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
        ]);
    }

    public function updatedNationalCode()
    {
        if ($this->national_code) {
            $this->validateOnly('national_code', [
                'national_code' => 'string|size:10|unique:managers,national_code,' . $this->managerId,
            ], [
                'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            ]);
        }
    }

    public function updatedMobile()
    {
        if ($this->mobile) {
            $this->validateOnly('mobile', [
                'mobile' => 'string|max:15|unique:managers,mobile,' . $this->managerId,
            ], [
                'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            ]);
        }
    }

    public function updatedStaticPasswordEnabled($value)
    {
        if (!$value) {
            $this->static_password = '';
            $this->static_password_confirmation = '';
        }
    }

    public function save()
    {
        // اعتبارسنجی رمز عبور
        if ($this->static_password_enabled) {
            $this->rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $this->rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $this->validate();

        try {
            // تبدیل تاریخ جلالی به میلادی
            $gregorianDateOfBirth = null;
            if ($this->date_of_birth) {
                $gregorianDateOfBirth = JalaliHelper::jalaliToGregorian($this->date_of_birth);
                if (!$gregorianDateOfBirth) {
                    $this->addError('date_of_birth', 'تاریخ تولد نامعتبر است. لطفاً تاریخ جلالی معتبر وارد کنید (مثال: 1404/04/15).');
                    return;
                }
            }

            $manager = Manager::findOrFail($this->managerId);

            $updateData = [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'date_of_birth' => $gregorianDateOfBirth,
                'national_code' => $this->national_code ?: null,
                'gender' => $this->gender ?: null,
                'email' => $this->email,
                'mobile' => $this->mobile ?: null,
                'two_factor_enabled' => $this->two_factor_enabled,
                'static_password_enabled' => $this->static_password_enabled,
                'bio' => $this->bio ?: null,
                'address' => $this->address ?: null,
                'permission_level' => $this->permission_level,
                'is_active' => $this->is_active,
                'is_verified' => $this->is_verified,
            ];

            // بروزرسانی رمز عبور اگر وارد شده باشد
            if ($this->static_password_enabled && $this->password) {
                $updateData['password'] = Hash::make($this->password);
            }

            $manager->update($updateData);

            $this->dispatch('show-alert', type: 'success', message: 'مدیر با موفقیت بروزرسانی شد!');
            return redirect()->route('admin.panel.managers.index');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در بروزرسانی مدیر: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.managers.manager-edit');
    }
}
