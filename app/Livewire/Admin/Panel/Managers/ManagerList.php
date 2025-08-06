<?php

namespace App\Livewire\Admin\Panel\Managers;

use App\Models\Manager;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class ManagerList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteManagerConfirmed' => 'deleteManager', 'deleteSelectedConfirmed' => 'deleteSelected'];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedManagers = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $permissionLevelFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'permissionLevelFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadManagers()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $manager = Manager::findOrFail($id);
        $manager->update(['is_active' => !$manager->is_active]);
        $this->dispatch('show-alert', type: $manager->is_active ? 'success' : 'info', message: $manager->is_active ? 'مدیر فعال شد!' : 'مدیر غیرفعال شد!');
        Cache::forget('managers_' . $this->search . '_page_' . $this->getPage());
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteManager($id)
    {
        $manager = Manager::findOrFail($id);
        $manager->delete();
        $this->dispatch('show-alert', type: 'success', message: 'مدیر با موفقیت حذف شد!');
        Cache::forget('managers_' . $this->search . '_page_' . $this->getPage());
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPermissionLevelFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getManagersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedManagers = $value ? $currentPageIds : [];
    }

    public function updatedSelectedManagers()
    {
        $currentPageIds = $this->getManagersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedManagers) && count(array_diff($currentPageIds, $this->selectedManagers)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getManagersQuery();
            $query->delete();
            $this->selectedManagers = [];
            $this->selectAll = false;
            $this->dispatch('show-alert', type: 'success', message: 'تمام مدیران انتخاب شده حذف شدند!');
        } else {
            if (empty($this->selectedManagers)) {
                $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک مدیر را انتخاب کنید!');
                return;
            }

            Manager::whereIn('id', $this->selectedManagers)->delete();
            $this->selectedManagers = [];
            $this->selectAll = false;
            $this->dispatch('show-alert', type: 'success', message: 'مدیران انتخاب شده حذف شدند!');
        }
        Cache::forget('managers_' . $this->search . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً یک عملیات انتخاب کنید!');
            return;
        }

        if (empty($this->selectedManagers) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک مدیر را انتخاب کنید!');
            return;
        }

        switch ($this->groupAction) {
            case 'activate':
                $this->updateStatus(true);
                break;
            case 'deactivate':
                $this->updateStatus(false);
                break;
            case 'delete':
                if ($this->applyToAllFiltered) {
                    $this->dispatch('confirm-delete-selected', allFiltered: 'allFiltered');
                } else {
                    $this->dispatch('confirm-delete-selected');
                }
                break;
            default:
                $this->dispatch('show-alert', type: 'warning', message: 'عملیات نامعتبر!');
                break;
        }

        $this->groupAction = '';
        $this->applyToAllFiltered = false;
    }

    private function updateStatus($status)
    {
        if ($this->applyToAllFiltered) {
            $query = $this->getManagersQuery();
            $query->update(['is_active' => $status]);
            $this->dispatch('show-alert', type: 'success', message: $status ? 'تمام مدیران فعال شدند!' : 'تمام مدیران غیرفعال شدند!');
        } else {
            Manager::whereIn('id', $this->selectedManagers)->update(['is_active' => $status]);
            $this->dispatch('show-alert', type: 'success', message: $status ? 'مدیران انتخاب شده فعال شدند!' : 'مدیران انتخاب شده غیرفعال شدند!');
        }
        $this->selectedManagers = [];
        $this->selectAll = false;
        Cache::forget('managers_' . $this->search . '_page_' . $this->getPage());
    }

    protected function getManagersQuery()
    {
        $query = Manager::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%')
                  ->orWhere('national_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        if ($this->permissionLevelFilter !== '') {
            $query->where('permission_level', $this->permissionLevelFilter);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $managers = collect();
        $totalCount = 0;

        if ($this->readyToLoad) {
            $query = $this->getManagersQuery();
            $totalCount = $query->count();
            $this->totalFilteredCount = $totalCount;
            $managers = $query->paginate($this->perPage);
        }

        return view('livewire.admin.panel.managers.manager-list', [
            'managers' => $managers,
            'totalCount' => $totalCount,
        ]);
    }
}
