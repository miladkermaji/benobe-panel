<?php

namespace App\Livewire\Admin\Panel\UserSubscriptions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteSubscriptionConfirmed' => 'delete', 'refreshList' => '$refresh'];

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $readyToLoad = false;
    public $selectedSubscriptions = [];
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

    public function loadSubscriptions()
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
        $subscription = UserSubscription::findOrFail($id);
        if (!Auth::guard('manager')->user()->id) {
            $this->dispatch('show-alert', type: 'error', message: 'دسترسی غیرمجاز برای حذف اشتراک.');
            return;
        }
        $subscription->delete();
        $this->dispatch('show-alert', type: 'success', message: 'اشتراک با موفقیت حذف شد!');
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getSubscriptionsQuery()->pluck('id')->toArray();
        $this->selectedSubscriptions = $value ? $currentPageIds : [];
    }

    public function updatedSelectedSubscriptions()
    {
        $currentPageIds = $this->getSubscriptionsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedSubscriptions) && count(array_diff($currentPageIds, $this->selectedSubscriptions)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedSubscriptions)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ اشتراکی انتخاب نشده است.');
            return;
        }

        UserSubscription::whereIn('id', $this->selectedSubscriptions)
            ->delete();
        $this->selectedSubscriptions = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'اشتراک‌های انتخاب‌شده حذف شدند!');
        $this->resetPage();
    }

    private function getSubscriptionsQuery()
    {
        return UserSubscription::query()
            ->with(['user', 'membershipPlan'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
                });
            })
            
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        $subscriptions = $this->readyToLoad ? $this->getSubscriptionsQuery() : null;
        return view('livewire.admin.panel.user-subscriptions.subscription-list', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
