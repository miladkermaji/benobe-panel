<?php

namespace App\Livewire\Admin\Panel\UserGroups;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Cache;

class UserGroupList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteUserGroupConfirmed' => 'deleteUserGroup', 'deleteSelectedConfirmed' => 'deleteSelected'];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedUserGroups = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadUserGroups()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $userGroup = UserGroup::findOrFail($id);
        $userGroup->update(['is_active' => !$userGroup->is_active]);
        $this->dispatch('show-alert', type: $userGroup->is_active ? 'success' : 'info', message: $userGroup->is_active ? 'فعال شد!' : 'غیرفعال شد!');
        Cache::forget('user_groups_' . $this->search . '_page_' . $this->getPage());
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteUserGroup($id)
    {
        $userGroup = UserGroup::findOrFail($id);
        $userGroup->delete();
        $this->dispatch('show-alert', type: 'success', message: 'گروه کاربری با موفقیت حذف شد!');
        Cache::forget('user_groups_' . $this->search . '_page_' . $this->getPage());
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getUserGroupsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedUserGroups = $value ? $currentPageIds : [];
    }

    public function updatedSelectedUserGroups()
    {
        $currentPageIds = $this->getUserGroupsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedUserGroups) && count(array_diff($currentPageIds, $this->selectedUserGroups)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getUserGroupsQuery();
            $query->delete();
            $this->selectedUserGroups = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه گروه‌های کاربری فیلترشده حذف شدند!');
            Cache::forget('user_groups_' . $this->search . '_page_' . $this->getPage());
            return;
        }
        if (empty($this->selectedUserGroups)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ گروه کاربری انتخاب نشده است.');
            return;
        }
        UserGroup::whereIn('id', $this->selectedUserGroups)->delete();
        $this->selectedUserGroups = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'گروه‌های کاربری انتخاب‌شده با موفقیت حذف شدند!');
        Cache::forget('user_groups_' . $this->search . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedUserGroups) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ گروه کاربری انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getUserGroupsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['is_active' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه گروه‌های کاربری فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['is_active' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه گروه‌های کاربری فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedUserGroups = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('user_groups_' . $this->search . '_page_' . $this->getPage());
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
        UserGroup::whereIn('id', $this->selectedUserGroups)
            ->update(['is_active' => $status]);

        $this->selectedUserGroups = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت گروه‌های کاربری انتخاب‌شده با موفقیت تغییر کرد.');
        Cache::forget('user_groups_' . $this->search . '_page_' . $this->getPage());
    }

    private function getUserGroupsQuery()
    {
        return UserGroup::query()
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $query = $this->getUserGroupsQuery();
        $this->totalFilteredCount = $this->readyToLoad ? $query->count() : 0;
        return view('livewire.admin.panel.user-groups.user-group-list', [
            'userGroups' => $this->readyToLoad ? $query->paginate($this->perPage) : [],
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
