<?php

namespace App\Livewire\Admin\Panel\Menus;

use App\Models\Menu;
use Livewire\Component;
use Livewire\WithPagination;

class MenuList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteMenuConfirmed'     => 'deleteMenu',
        'deleteSelectedConfirmed' => 'deleteSelectedConfirmed', // Listener جدید برای حذف انتخاب‌شده‌ها
    ];

    public $perPage       = 20;
    public $search        = '';
    public $readyToLoad   = false;
    public $selectedmenus = [];
    public $selectAll     = false;
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $statusFilter = '';
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadmenus()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = Menu::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteMenu($id)
    {
        $item = Menu::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'منو حذف شد!');
    }

    public function confirmDeleteSelected()
    {
        if (empty($this->selectedmenus) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ منویی انتخاب نشده است.');
            return;
        }
        $this->dispatch('confirm-delete-selected'); // رویداد برای تأیید حذف انتخاب‌شده‌ها
    }

    public function deleteSelectedConfirmed()
    {
        $ids = $this->applyToAllFiltered
            ? $this->getmenusQueryRaw()->pluck('id')->toArray()
            : $this->selectedmenus;
        Menu::whereIn('id', $ids)->delete();
        $this->selectedmenus = [];
        $this->selectAll     = false;
        $this->applyToAllFiltered = false;
        $this->dispatch('show-alert', type: 'success', message: 'منوهای انتخاب‌شده حذف شدند!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds      = $this->getmenusQuery()->pluck('id')->toArray();
        $this->selectedmenus = $value ? $currentPageIds : [];
    }

    public function updatedSelectedmenus()
    {
        $currentPageIds  = $this->getmenusQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedmenus) && count(array_diff($currentPageIds, $this->selectedmenus)) === 0;
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedGroupAction()
    {
        // Optional: reset selection or handle UI
    }

    public function executeGroupAction()
    {
        if ($this->groupAction === 'delete') {
            $this->confirmDeleteSelected();
        } elseif ($this->groupAction === 'status_active') {
            $this->updateSelectedStatus(true);
        } elseif ($this->groupAction === 'status_inactive') {
            $this->updateSelectedStatus(false);
        }
    }

    public function updateSelectedStatus($status)
    {
        $ids = $this->applyToAllFiltered
            ? $this->getmenusQueryRaw()->pluck('id')->toArray()
            : $this->selectedmenus;
        Menu::whereIn('id', $ids)->update(['status' => $status]);
        $this->selectedmenus = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت منوها بروزرسانی شد.');
    }

    private function getmenusQueryRaw()
    {
        $query = Menu::query();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('url', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter === 'active' ? 1 : 0);
        }
        return $query;
    }

    private function getmenusQuery()
    {
        return $this->getmenusQueryRaw()->paginate($this->perPage);
    }

    public function render()
    {
        $this->totalFilteredCount = $this->readyToLoad ? $this->getmenusQueryRaw()->count() : 0;
        $items = $this->readyToLoad ? $this->getmenusQuery() : null;
        return view('livewire.admin.panel.menus.menu-list', [
            'menus' => $items,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
