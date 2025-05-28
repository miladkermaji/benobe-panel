<?php

namespace App\Livewire\Admin\Panel\UserMembershipPlans;

use Livewire\Component;
use App\Models\UserMembershipPlan;
use Illuminate\Support\Facades\Auth;

class MembershipPlanEdit extends Component
{
    public UserMembershipPlan $userMembershipPlan;
    public $name;
    public $price;
    public $discount;
    public $description;
    public $status;
    public $duration;
    public $duration_type;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'price' => 'required|numeric|min:0',
        'discount' => 'required|numeric|min:0|max:100',
        'description' => 'nullable|max:1000',
        'status' => 'boolean',
        'duration' => 'required|integer|min:1',
        'duration_type' => 'required|in:day,week,month,year'
    ];

    public function mount(UserMembershipPlan $userMembershipPlan)
    {
        if ($userMembershipPlan->user_id !== Auth::guard('manager')->user()->id) {
            abort(403);
        }

        $this->userMembershipPlan = $userMembershipPlan;
        $this->name = $userMembershipPlan->name;
        $this->price = $userMembershipPlan->price;
        $this->discount = $userMembershipPlan->discount;
        $this->description = $userMembershipPlan->description;
        $this->status = $userMembershipPlan->status;
        $this->duration = $userMembershipPlan->duration;
        $this->duration_type = $userMembershipPlan->duration_type;
    }

    public function save()
    {
        $this->validate();

        $this->userMembershipPlan->update([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
            'duration' => $this->duration,
            'duration_type' => $this->duration_type
        ]);

        session()->flash('success', 'طرح عضویت با موفقیت ویرایش شد.');
        return redirect()->route('admin.panel.user-membership-plans.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-membership-plans.membership-plan-edit');
    }
}
