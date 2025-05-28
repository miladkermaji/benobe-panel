<?php

namespace App\Livewire\Admin\Panel\UserAppointmentFees;

use Livewire\Component;
use App\Models\UserAppointmentFee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppointmentFeeCreate extends Component
{
    public $name;
    public $price;
    public $discount = 0;
    public $description;
    public $status = true;

    public function save()
    {
        Log::info('Appointment Fee Create Input', [
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

        UserAppointmentFee::create([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
            'user_id' => Auth::guard('manager')->user()->id,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'حق نوبت با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.user-appointment-fees.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-appointment-fees.appointment-fee-create');
    }
}
