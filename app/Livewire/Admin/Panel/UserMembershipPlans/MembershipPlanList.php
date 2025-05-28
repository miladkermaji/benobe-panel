<?php

namespace App\Livewire\Admin\Panel\UserMembershipPlans;

use App\Models\UserMembershipPlan;
use Livewire\Component;
use Livewire\WithPagination;

class MembershipPlanList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $listeners = ['refreshList' => '$refresh'];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(UserMembershipPlan $plan)
    {
        $plan->delete();
        $this->dispatch('refreshList');
        session()->flash('success', 'طرح عضویت با موفقیت حذف شد.');
    }

    public function render()
    {
        $plans = UserMembershipPlan::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->where('user_id', auth('admin')->id())
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.panel.user-membership-plans.membership-plan-list', [
            'plans' => $plans
        ]);
    }
}
