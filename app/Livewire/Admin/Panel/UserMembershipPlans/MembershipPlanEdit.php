<?php

namespace App\Livewire\Admin\Panel\UserMembershipPlans;

use Livewire\Component;
use App\Models\UserMembershipPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MembershipPlanEdit extends Component
{
    public UserMembershipPlan $userMembershipPlan;
    public $name;
    public $type;
    public $price;
    public $discount;
    public $final_price;
    public $description;
    public $status;
    public $duration_days;
    public $duration_type;
    public $appointment_count;

    public function mount(UserMembershipPlan $userMembershipPlan)
    {
        if (!Auth::guard('manager')->user()->id) {
            abort(403, 'دسترسی غیرمجاز');
        }

        $this->userMembershipPlan = $userMembershipPlan;
        $this->name = $userMembershipPlan->name;
        $this->type = $userMembershipPlan->type;
        $this->price = $userMembershipPlan->price;
        $this->discount = $userMembershipPlan->discount;
        $this->final_price = $userMembershipPlan->final_price;
        $this->description = $userMembershipPlan->description;
        $this->status = $userMembershipPlan->status;
        $this->duration_days = $userMembershipPlan->duration_days;
        $this->duration_type = $userMembershipPlan->duration_type;
        $this->appointment_count = $userMembershipPlan->appointment_count;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['price', 'discount'])) {
            $this->calculateFinalPrice();
        }
    }

    protected function calculateFinalPrice()
    {
        if ($this->price && $this->discount) {
            $this->final_price = $this->price * (1 - $this->discount / 100);
        } else {
            $this->final_price = $this->price;
        }
    }

    public function save()
    {
        $validator = Validator::make([
            'name' => $this->name,
            'type' => $this->type,
            'price' => $this->price,
            'discount' => $this->discount,
            'final_price' => $this->final_price,
            'description' => $this->description,
            'status' => $this->status,
            'duration_days' => $this->duration_days,
            'duration_type' => $this->duration_type,
            'appointment_count' => $this->appointment_count,
        ], [
            'name' => 'required|string|min:3|max:255',
            'type' => 'required|in:gold,silver,bronze',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'final_price' => 'nullable|numeric|min:0',
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
            'type.required' => 'لطفاً نوع طرح را انتخاب کنید.',
            'type.in' => 'نوع طرح نامعتبر است.',
            'price.required' => 'لطفاً قیمت را وارد کنید.',
            'price.numeric' => 'قیمت باید عدد باشد.',
            'price.min' => 'قیمت نمی‌تواند منفی باشد.',
            'discount.numeric' => 'تخفیف باید عدد باشد.',
            'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
            'discount.max' => 'تخفیف نمی‌تواند بیشتر از ۱۰۰ درصد باشد.',
            'final_price.numeric' => 'قیمت نهایی باید عدد باشد.',
            'final_price.min' => 'قیمت نهایی نمی‌تواند منفی باشد.',
            'description.string' => 'توضیحات باید متن باشد.',
            'description.max' => 'توضیحات نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
            'status.required' => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
            'duration_days.required' => 'لطفاً مدت‌زمان را وارد کنید.',
            'duration_days.integer' => 'مدت‌زمان باید عدد صحیح باشد.',
            'duration_days.min' => 'مدت‌زمان باید حداقل ۱ باشد.',
            'appointment_count.min' => 'مدت‌زمان باید حداقل ۱ باشد.',
            'appointment_count.required' => 'لطفاً نوع مدت‌زمان را انتخاب کنید.',
            'duration_type.in' => 'نوع مدت‌زمان معتبر نیست.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $this->calculateFinalPrice();

        $this->userMembershipPlan->update([
            'name' => $this->name,
            'type' => $this->type,
            'price' => $this->price,
            'discount' => $this->discount,
            'final_price' => $this->final_price,
            'description' => $this->description,
            'status' => $this->status,
            'duration_days' => $this->duration_days,
            'duration_type' => $this->duration_type,
            'appointment_count' => $this->appointment_count,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'طرح عضویت با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.user-membership-plans.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-membership-plans.membership-plan-edit');
    }
}
