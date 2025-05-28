<?php

namespace App\Livewire\Admin\Panel\UserSubscriptions;

use App\Models\User;
use Livewire\Component;
use App\Models\UserSubscription;
use App\Models\UserMembershipPlan;
use Illuminate\Support\Facades\Auth;

class SubscriptionEdit extends Component
{
    public UserSubscription $userSubscription;
    public $user_id;
    public $membership_plan_id;
    public $start_date;
    public $end_date;
    public $status;
    public $description;

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'membership_plan_id' => 'required|exists:user_membership_plans,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'status' => 'boolean',
        'description' => 'nullable|max:1000'
    ];

    public function mount(UserSubscription $userSubscription)
    {
        if ($userSubscription->admin_id !== Auth::guard('manager')->user()->id) {
            abort(403);
        }

        $this->userSubscription = $userSubscription;
        $this->user_id = $userSubscription->user_id;
        $this->membership_plan_id = $userSubscription->membership_plan_id;
        $this->start_date = $userSubscription->start_date;
        $this->end_date = $userSubscription->end_date;
        $this->status = $userSubscription->status;
        $this->description = $userSubscription->description;
    }

    public function save()
    {
        $this->validate();

        $this->userSubscription->update([
            'user_id' => $this->user_id,
            'membership_plan_id' => $this->membership_plan_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'description' => $this->description
        ]);

        session()->flash('success', 'اشتراک با موفقیت ویرایش شد.');
        return redirect()->route('admin.panel.user-subscriptions.index');
    }

    public function render()
    {
        $users = User::where('user_id', Auth::guard('manager')->user()->id)->get();
        $plans = UserMembershipPlan::where('user_id', Auth::guard('manager')->user()->id)->where('status', true)->get();

        return view('livewire.admin.panel.user-subscriptions.subscription-edit', [
            'users' => $users,
            'plans' => $plans
        ]);
    }
}
