<?php

namespace App\Livewire\Admin\Panel\UserAppointmentFees;

use Livewire\Component;
use App\Models\UserAppointmentFee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppointmentFeeEdit extends Component
{
    public UserAppointmentFee $userAppointmentFee;
    public $name;
    public $price;
    public $discount;
    public $description;
    public $status;

    public function mount(UserAppointmentFee $userAppointmentFee)
    {
        if ($userAppointmentFee->user_id !== Auth::guard('manager')->user()->id) {
            abort(403, 'دسترسی غیرمجاز');
        }

        $this->userAppointmentFee = $userAppointmentFee;
        $this->name = $userAppointmentFee->name;
        $this->price = $userAppointmentFee->price;
        $this->discount = $userAppointmentFee->discount;
        $this->description = $userAppointmentFee->description;
        $this->status = $userAppointmentFee->status;

        Log::info('Appointment Fee Edit Loaded', [
            'id' => $userAppointmentFee->id,
            'name' => $this->name,
            'price' => $this->price,
        ]);
    }

    public function save()
    {
        Log::info('Appointment Fee Edit Input', [
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
        ]);

        $validator = Validator::make([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
        ], [
            'name' => 'required|string|min:3|max:255',
            'price' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ], [
            'name.required' => 'لطفاً نام هزینه نوبت را وارد کنید.',
            'name.string' => 'نام هزینه نوبت باید متن باشد.',
            'name.min' => 'نام هزینه نوبت باید حداقل ۳ کاراکتر باشد.',
            'name.max' => 'نام هزینه نوبت نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'price.required' => 'لطفاً قیمت را وارد کنید.',
            'price.numeric' => 'قیمت باید عدد باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
            'discount.required' => 'لطفاً تخفیف را وارد کنید.',
            'discount.numeric' => 'تخفیف باید عدد باشد.',
            'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
            'discount.max' => 'تخفیف نمی‌تواند بیشتر از ۱۰۰ درصد باشد.',
            'description.string' => 'توضیحات باید متن باشد.',
            'description.max' => 'توضیحات نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'status.required' => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $this->userAppointmentFee->update([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'حق نوبت با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.user-appointment-fees.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-appointment-fees.appointment-fee-edit');
    }
}
