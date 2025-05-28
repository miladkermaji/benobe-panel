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
    public $duration;
    public $duration_type = 'month';

    public function save()
    {
        Log::info('Membership Plan Create Input', [
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'duration' => $this->duration,
            'duration_type' => $this->duration_type,
        ]);

        $validator = Validator::make([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
            'duration' => $this->duration,
            'duration_type' => $this->duration_type,
        ], [
            'name' => 'required|string|min:3|max:255',
            'price' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
            'duration' => 'required|integer|min:1',
            'duration_type' => 'required|in:day,week,month,year',
        ], [
            'name.required' => 'لطفاً نام طرح را وارد کنید.',
            'name.string' => 'نام طرح باید متن باشد.',
            'name.min' => 'نام طرح باید حداقل ۳ کاراکتر باشد.',
            'name.max' => 'نام طرح نباید بیشتر از ۲۵۵ کاراکتر باشد.',
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
            'duration.required' => 'لطفاً مدت‌زمان را وارد کنید.',
            'duration.integer' => 'مدت‌زمان باید عدد صحیح باشد.',
            'duration.min' => 'مدت‌زمان باید حداقل ۱ باشد.',
            'duration_type.required' => 'لطفاً نوع مدت‌زمان را انتخاب کنید.',
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
            'duration' => $this->duration,
            'duration_type' => $this->duration_type,
            'user_id' => Auth::guard('manager')->user()->id,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'طرح عضویت با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.user-membership-plans.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-membership-plans.membership-plan-create');
    }
}
