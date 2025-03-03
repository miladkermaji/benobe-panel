<?php

namespace App\Livewire\Admin\Dashboard\Specialties;

use Livewire\Component;
use App\Models\Dr\Specialty;
;

class SpecialtyCreate extends Component
{
    public $name;
    public $level = 1;  // مقدار پیش‌فرض

    public $successMessage = '';

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function store()
    {
        // اطمینان از اینکه مقدار `level` به درستی عددی است
        if (!is_numeric($this->level)) {
            $this->level = 1;
        }

        // اعتبارسنجی فیلدهای ورودی
        $this->validate();

        // ذخیره در دیتابیس
        Specialty::create([
            'name' => $this->name,
            'level' => (int) $this->level, // تبدیل مقدار level به عدد صحیح
        ]);

        // پیام موفقیت
        $this->successMessage = 'تخصص جدید با موفقیت اضافه شد.';

        // هدایت کاربر بعد از 5 ثانیه به صفحه لیست تخصص‌ها
        $this->dispatch('redirectToIndex')->self();

        // ریست کردن فیلدها بعد از ذخیره
        $this->reset(['name']);
    }

    public function render()
    {
        return view('livewire.admin.dashboard.specialties.specialty-create');
    }
}
