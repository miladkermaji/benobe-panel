<?php

namespace App\Livewire\Admin\Panel\UserMembershipPlans;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserMembershipPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MembershipPlanList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deletePlanConfirmed' => 'delete', 'refreshList' => '$refresh'];

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $readyToLoad = false;
    public $selectedPlans = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadPlans()
    {
        $this->readyToLoad = true;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function delete($id)
    {
        $plan = UserMembershipPlan::findOrFail($id);
        if ($plan->user_id !== Auth::guard('manager')->user()->id) {
            $this->dispatch('show-alert', type: 'error', message: 'دسترسی غیرمجاز برای حذف طرح عضویت.');
            return;
        }
        $plan->delete();
        $this->dispatch('show-alert', type: 'success', message: 'طرح عضویت با موفقیت حذف شد!');
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getPlansQuery()->pluck('id')->toArray();
        $this->selectedPlans = $value ? $currentPageIds : [];
    }

    public function updatedSelectedPlans()
    {
        $currentPageIds = $this->getPlansQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedPlans) && count(array_diff($currentPageIds, $this->selectedPlans)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedPlans)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ طرح عضویتی انتخاب نشده است.');
            return;
        }

        UserMembershipPlan::whereIn('id', $this->selectedPlans)
            ->where('user_id', Auth::guard('manager')->user()->id)
            ->delete();
        $this->selectedPlans = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'طرح‌های عضویت انتخاب‌شده حذف شدند!');
        $this->resetPage();
    }

    private function getPlansQuery()
    {
        return UserMembershipPlan::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->where('user_id', Auth::guard('manager')->user()->id)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        $plans = $this->readyToLoad ? $this->getPlansQuery() : null;
        return view('livewire.admin.panel.user-membership-plans.membership-plan-list', [
            'plans' => $plans,
        ]);
    }
}
