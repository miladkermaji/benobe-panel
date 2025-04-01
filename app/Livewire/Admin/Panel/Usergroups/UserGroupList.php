<?php

namespace App\Livewire\Admin\Panel\UserGroups;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserGroup;

class UserGroupList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteUserGroupConfirmed' => 'deleteUserGroup'];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedusergroups = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadusergroups()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = UserGroup::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        $this->dispatch('show-alert', type: $item->is_active ? 'success' : 'info', message: $item->is_active ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteUserGroup($id)
    {
        $item = UserGroup::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'usergroup حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getusergroupsQuery()->pluck('id')->toArray();
        $this->selectedusergroups = $value ? $currentPageIds : [];
    }

    public function updatedSelectedusergroups()
    {
        $currentPageIds = $this->getusergroupsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedusergroups) && count(array_diff($currentPageIds, $this->selectedusergroups)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedusergroups)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ usergroup انتخاب نشده است.');
            return;
        }

        UserGroup::whereIn('id', $this->selectedusergroups)->delete();
        $this->selectedusergroups = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'usergroups انتخاب‌شده حذف شدند!');
    }

    private function getusergroupsQuery()
    {
        return UserGroup::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc') // اضافه کردن مرتب‌سازی برای ثبات
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getusergroupsQuery() : null;

        return view('livewire.admin.panel.user-groups.user-group-list', [
            'usergroups' => $items,
        ]);
    }
}
