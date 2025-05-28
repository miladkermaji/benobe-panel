<?php

namespace App\Livewire\Admin\Panel\UserMembershipPlans;

use Livewire\Component;
use App\Models\UserMembershipPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MembershipPlanCreate extends Component
{
    public $name;
    public $price;
    public $discount = 0;
    public $description;
    public $status = true;
    public $duration_days;
    public $duration_type = 'month';
    public $appointment_count;

    public function save()
    {
 

        $validator = Validator::make([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
            'duration_days' => $this->duration_days,
            'duration_type' => $this->duration_type,
            'appointment_count' => $this->appointment_count,
        ], [
            'name' => 'required|string|min:3|max:255',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
            'duration_days' => 'required|integer|min:1',
            'appointment_count' => 'required|integer|min:1',
            'duration_type' => 'required|in:day,week,month,year',
        ], [
            'name.required' => 'لطفاً نام طرح را وارد کنید.',
            'name.string' => 'نام طرح باید متن باشد.',
            'name.min' => 'نام طرح باید حداقل ۳ کاراکتر باشد.',
            'name.max' => 'نام طرح نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'price.required' => 'لطفاً قیمت را وارد کنید.',
            'price.numeric' => 'قیمت باید عدد باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
            
            'discount.numeric' => 'تخفیف باید عدد باشد.',
            'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
            'discount.max' => 'تخفیف نمی‌تواند بیشتر از ۱۰۰ درصد باشد.',
            'description.string' => 'توضیحات باید متن باشد.',
            'description.max' => 'توضیحات نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'status.required' => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
            'duration_days.required' => 'لطفاً مدت‌زمان را وارد کنید.',
            'duration_days.integer' => 'مدت‌زمان باید عدد صحیح باشد.',
            'duration_days.min' => 'مدت‌زمان باید حداقل ۱ باشد.',
            'duration_days.integer' => 'مدت‌زمان باید عدد صحیح باشد.',
            'appointment_count.min' => 'مدت‌زمان باید حداقل ۱ باشد.',
            'appointment_count.required' => 'لطفاً نوع مدت‌زمان را انتخاب کنید.',
            'duration_type.in' => 'نوع مدت‌زمان معتبر نیست.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        UserMembershipPlan::create([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
            'duration_days' => $this->duration_days,
            'duration_type' => $this->duration_type,
            'appointment_count' => $this->appointment_count,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'طرح عضویت با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.user-membership-plans.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-membership-plans.membership-plan-create');
    }
}
