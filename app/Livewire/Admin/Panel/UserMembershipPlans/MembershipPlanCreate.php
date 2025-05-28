<?php

namespace App\Livewire\Admin\Panel\UserMembershipPlans;

use Livewire\Component;
use App\Models\UserMembershipPlan;
use Illuminate\Support\Facades\Auth;

class MembershipPlanCreate extends Component
{
    public $name;
    public $price;
    public $discount = 0;
    public $description;
    public $status = true;
    public $duration;
    public $duration_type = 'month';

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'price' => 'required|numeric|min:0',
        'discount' => 'required|numeric|min:0|max:100',
        'description' => 'nullable|max:1000',
        'status' => 'boolean',
        'duration' => 'required|integer|min:1',
        'duration_type' => 'required|in:day,week,month,year'
    ];

    public function save()
    {
        $this->validate();

        UserMembershipPlan::create([
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'status' => $this->status,
            'duration' => $this->duration,
            'duration_type' => $this->duration_type,
            'user_id' => Auth::guard('manager')->user()->id
        ]);

        session()->flash('success', 'طرح عضویت با موفقیت ایجاد شد.');
        return redirect()->route('admin.panel.user-membership-plans.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-membership-plans.membership-plan-create');
    }
}
