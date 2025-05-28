<?php

namespace App\Livewire\Admin\Panel\UserAppointmentFees;

use Livewire\Component;
use App\Models\UserAppointmentFee;
use Illuminate\Support\Facades\Auth;

class AppointmentFeeEdit extends Component
{
    public UserAppointmentFee $userAppointmentFee;
    public $name;
    public $price;
    public $discount;
    public $description;
    public $status;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'price' => 'required|numeric|min:0',
        'discount' => 'required|numeric|min:0|max:100',
        'description' => 'nullable|max:1000',
        'status' => 'boolean'
    ];

    public function mount(UserAppointmentFee $userAppointmentFee)
    {
        if ($userAppointmentFee->user_id !== Auth::guard('manager')->user()->id) {
            abort(403);
        }

        $this->userAppointmentFee = $userAppointmentFee;
        $this->name = $userAppointmentFee->name;
        $this->price = $userAppointmentFee->price;
        $this->discount = $userAppointmentFee->discount;
        $this->description = $userAppointmentFee->description;
        $this->status = $userAppointmentFee->status;
    }

    public function save()
    {
        $this->validate();

        $this->userAppointmentFee->update([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status
        ]);

        session()->flash('success', 'حق نوبت با موفقیت ویرایش شد.');
        return redirect()->route('admin.user-appointment-fees.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-appointment-fees.appointment-fee-edit');
    }
}
