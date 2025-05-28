<?php

namespace App\Livewire\Admin\Panel\UserAppointmentFees;

use App\Models\UserAppointmentFee;
use Livewire\Component;

class AppointmentFeeCreate extends Component
{
    public $name;
    public $price;
    public $discount = 0;
    public $description;
    public $status = true;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'price' => 'required|numeric|min:0',
        'discount' => 'required|numeric|min:0|max:100',
        'description' => 'nullable|max:1000',
        'status' => 'boolean'
    ];

    public function save()
    {
        $this->validate();

        UserAppointmentFee::create([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
            'user_id' => auth('admin')->id()
        ]);

        session()->flash('success', 'حق نوبت با موفقیت ایجاد شد.');
        return redirect()->route('admin.user-appointment-fees.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-appointment-fees.appointment-fee-create');
    }
}
