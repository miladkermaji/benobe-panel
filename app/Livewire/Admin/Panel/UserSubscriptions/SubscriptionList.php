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
    protected $listeners = [
        'deleteSubscriptionConfirmed' => 'delete',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'refreshList' => '$refresh'
    ];

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $readyToLoad = false;
    public $selectedSubscriptions = [];
    public $selectAll = false;
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

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

    public function toggleStatus($id)
    {
        $subscription = UserSubscription::findOrFail($id);
        $subscription->update(['status' => !$subscription->status]);
        $this->dispatch('show-alert', type: $subscription->status ? 'success' : 'info', message: $subscription->status ? 'فعال شد!' : 'غیرفعال شد!');
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
        $currentPageIds = $this->getSubscriptionsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedSubscriptions = $value ? $currentPageIds : [];
    }

    public function updatedSelectedSubscriptions()
    {
        $currentPageIds = $this->getSubscriptionsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedSubscriptions) && count(array_diff($currentPageIds, $this->selectedSubscriptions)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getSubscriptionsQuery();
            $query->delete();
            $this->selectedSubscriptions = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه اشتراک‌های فیلترشده حذف شدند!');
            return;
        }
        if (empty($this->selectedSubscriptions)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ اشتراکی انتخاب نشده است.');
            return;
        }
        UserSubscription::whereIn('id', $this->selectedSubscriptions)->delete();
        $this->selectedSubscriptions = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'اشتراک‌های انتخاب‌شده حذف شدند!');
        $this->resetPage();
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedSubscriptions) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ اشتراکی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getSubscriptionsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['status' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه اشتراک‌های فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه اشتراک‌های فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedSubscriptions = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'status_active':
                $this->updateStatus(true);
                break;
            case 'status_inactive':
                $this->updateStatus(false);
                break;
        }

        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        UserSubscription::whereIn('id', $this->selectedSubscriptions)
            ->update(['status' => $status]);

        $this->selectedSubscriptions = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت اشتراک‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    private function getSubscriptionsQuery()
    {
        return UserSubscription::query()
            ->with(['user', 'plan'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $search = trim($this->search);
                    $q->where('mobile', 'like', '%' . $search . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%']);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $query = $this->getSubscriptionsQuery();
        $this->totalFilteredCount = $this->readyToLoad ? $query->count() : 0;
        return view('livewire.admin.panel.user-subscriptions.subscription-list', [
            'subscriptions' => $this->readyToLoad ? $query->paginate($this->perPage) : [],
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}