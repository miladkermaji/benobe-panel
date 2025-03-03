<?php

namespace App\Livewire\Admin\Dashboard\Membershipfee;

use Livewire\Component;
use App\Models\Admin\Dashboard\Membershipfee\MembershipFee;

class MembershipFeeEdit extends Component
{
    public $membershipFeeId;
    public $name, $days, $price, $sort,$user_type;
    public $successMessage = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'days' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
        'sort' => 'required|integer|min:1',
    ];

    public function mount($membershipFeeId)
    {
        $fee = MembershipFee::findOrFail($membershipFeeId);
        $this->membershipFeeId = $fee->id;
        $this->name = $fee->name;
        $this->days = $fee->days;
        $this->price = $fee->price;
        $this->sort = $fee->sort;
        $this->user_type = $fee->user_type;
    }

    public function update()
    {
        $this->validate();

        $fee = MembershipFee::findOrFail($this->membershipFeeId);
        $fee->update([
            'name' => $this->name,
            'days' => $this->days,
            'price' => $this->price,
            'sort' => $this->sort,
            'user_type' => 'doctor',
        ]);

        $this->successMessage = 'بروزرسانی با موفقیت انجام شد.';

        // بعد از 5 ثانیه به صفحه لیست تعرفه‌ها برمی‌گردد
        $this->dispatch('redirectToIndex');
    }

    public function render()
    {
        return view('livewire.admin.dashboard.membershipfee.membership-fee-edit');
    }
}

