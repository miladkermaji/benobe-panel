<?php

namespace App\Livewire\Admin\Panel\UserSubscriptions;

use App\Models\User;
use Livewire\Component;
use App\Models\UserSubscription;
use App\Models\UserMembershipPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class SubscriptionCreate extends Component
{
    public $user_id;
    public $membership_plan_id;
    public $start_date;
    public $end_date;
    public $status = true;
    public $description;
    public $users = [];
    public $plans = [];

    public function mount()
    {
        $this->users = User::select('id', 'first_name','last_name')->get();
        $this->plans = UserMembershipPlan::where('status', true)->select('id', 'name')->get();
    }

    public function save()
    {
        Log::info('Subscription Create Input', [
            'user_id' => $this->user_id,
            'membership_plan_id' => $this->membership_plan_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $validator = Validator::make([
            'user_id' => $this->user_id,
            'membership_plan_id' => $this->membership_plan_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'description' => $this->description,
        ], [
            'user_id' => 'required|exists:users,id',
            'membership_plan_id' => 'required|exists:user_membership_plans,id',
            'start_date' => 'required|string|max:10',
            'end_date' => 'required|string|max:10|after:start_date',
            'status' => 'required|boolean',
            'description' => 'nullable|string|max:1000',
        ], [
            'user_id.required' => 'لطفاً کاربر را انتخاب کنید.',
            'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
            'membership_plan_id.required' => 'لطفاً طرح عضویت را انتخاب کنید.',
            'membership_plan_id.exists' => 'طرح عضویت انتخاب‌شده معتبر نیست.',
            'start_date.required' => 'لطفاً تاریخ شروع را وارد کنید.',
            'start_date.string' => 'تاریخ شروع باید به فرمت صحیح باشد.',
            'start_date.max' => 'تاریخ شروع نباید بیشتر از ۱۰ کاراکتر باشد.',
            'end_date.required' => 'لطفاً تاریخ پایان را وارد کنید.',
            'end_date.string' => 'تاریخ پایان باید به فرمت صحیح باشد.',
            'end_date.max' => 'تاریخ پایان نباید بیشتر از ۱۰ کاراکتر باشد.',
            'end_date.after' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
            'status.required' => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
            'description.string' => 'توضیحات باید متن باشد.',
            'description.max' => 'توضیحات نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $startDateMiladi = null;
        $endDateMiladi = null;
        try {
            $startDateMiladi = Jalalian::fromFormat('Y/m/d', $this->start_date)->toCarbon()->toDateString();
            $endDateMiladi = Jalalian::fromFormat('Y/m/d', $this->end_date)->toCarbon()->toDateString();
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'تاریخ‌های واردشده نامعتبر هستند. لطفاً به فرمت ۱۴۰۳/۱۲/۱۳ وارد کنید.');
            return;
        }

        UserSubscription::create([
            'user_id' => $this->user_id,
            'membership_plan_id' => $this->membership_plan_id,
            'start_date' => $startDateMiladi,
            'end_date' => $endDateMiladi,
            'status' => $this->status,
            'description' => $this->description,
            'admin_id' => Auth::guard('manager')->user()->id,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'اشتراک با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.user-subscriptions.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-subscriptions.subscription-create');
    }
}
