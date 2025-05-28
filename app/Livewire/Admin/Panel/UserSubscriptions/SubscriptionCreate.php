<?php

namespace App\Livewire\Admin\Panel\UserSubscriptions;

use App\Models\User;
use App\Models\UserMembershipPlan;
use App\Models\UserSubscription;
use Livewire\Component;

class SubscriptionCreate extends Component
{
    public $user_id;
    public $membership_plan_id;
    public $start_date;
    public $end_date;
    public $status = true;
    public $description;

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'membership_plan_id' => 'required|exists:user_membership_plans,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'status' => 'boolean',
        'description' => 'nullable|max:1000'
    ];

    public function save()
    {
        $this->validate();

        UserSubscription::create([
            'user_id' => $this->user_id,
            'membership_plan_id' => $this->membership_plan_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'description' => $this->description,
            'admin_id' => auth('admin')->id()
        ]);

        session()->flash('success', 'اشتراک با موفقیت ایجاد شد.');
        return redirect()->route('admin.user-subscriptions.index');
    }

    public function render()
    {
        $users = User::where('user_id', auth('admin')->id())->get();
        $plans = UserMembershipPlan::where('user_id', auth('admin')->id())->where('status', true)->get();

        return view('livewire.admin.panel.user-subscriptions.subscription-create', [
            'users' => $users,
            'plans' => $plans
        ]);
    }
}
