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

class SubscriptionEdit extends Component
{
    public UserSubscription $userSubscription;
    public $user_id;
    public $plan_id;
    public $start_date;
    public $end_date;
    public $status;
    public $description;
    public $plans = [];

    public function mount(UserSubscription $userSubscription)
    {
        if ($userSubscription->admin_id !== Auth::guard('manager')->user()->id) {
            abort(403, 'دسترسی غیرمجاز');
        }

        $this->userSubscription = $userSubscription;
        $this->user_id = $userSubscription->user_id;
        $this->plan_id = $userSubscription->plan_id;
        $this->start_date = $userSubscription->start_date
            ? Jalalian::fromCarbon(\Carbon\Carbon::parse($userSubscription->start_date))->format('Y/m/d')
            : null;
        $this->end_date = $userSubscription->end_date
            ? Jalalian::fromCarbon(\Carbon\Carbon::parse($userSubscription->end_date))->format('Y/m/d')
            : null;
        $this->status = $userSubscription->status;
        $this->description = $userSubscription->description;

        $this->plans = cache()->remember('active_membership_plans', 60, function () {
            return UserMembershipPlan::where('status', true)->select('id', 'name')->get();
        });

        Log::info('Subscription Edit Loaded', [
            'id' => $userSubscription->id,
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
        ]);
    }

    public function save()
    {
        Log::info('Subscription Edit Input', [
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $validator = Validator::make([
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'description' => $this->description,
        ], [
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:user_membership_plans,id',
            'start_date' => 'required|string|max:10',
            'end_date' => 'required|string|max:10|after:start_date',
            'status' => 'required|boolean',
            'description' => 'nullable|string|max:1000',
        ], [
            'user_id.required' => 'لطفاً کاربر را انتخاب کنید.',
            'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
            'plan_id.required' => 'لطفاً طرح عضویت را انتخاب کنید.',
            'plan_id.exists' => 'طرح عضویت انتخاب‌شده معتبر نیست.',
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

        $this->userSubscription->update([
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'start_date' => $startDateMiladi,
            'end_date' => $endDateMiladi,
            'status' => $this->status,
            'description' => $this->description,
            'subscribable_id' => $this->user_id,
            'subscribable_type' => \App\Models\User::class,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'اشتراک با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.user-subscriptions.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-subscriptions.subscription-edit');
    }
}
