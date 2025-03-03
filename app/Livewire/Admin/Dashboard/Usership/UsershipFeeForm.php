<?php

namespace App\Livewire\Admin\Dashboard\Usership;

use Livewire\Component;
use App\Models\Admin\Dashboard\Membershipfee\MembershipFee;

class UsershipFeeForm extends Component
{
    public $name, $days, $price, $sort;
    public $successMessage = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'days' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
        'sort' => 'required|integer|min:1',
    ];

    public function store()
    {
        $this->validate();

        MembershipFee::create([
            'name' => $this->name,
            'days' => $this->days,
            'price' => $this->price,
            'sort' => $this->sort,
            'user_type' => 'normal',
        ]);

        // پاک کردن فرم پس از ذخیره‌سازی
        $this->reset(['name', 'days', 'price', 'sort']);

        // نمایش پیام موفقیت
        $this->successMessage = "تعرفه جدید با موفقیت ذخیره شد!";
    }

    public function render()
    {
        return view('livewire.admin.dashboard.usership.usership-fee-form');
    }
}
