<?php

namespace App\Livewire\Admin\Panel\Menus;

use App\Models\Admin\Dashboard\Menu\Menu;
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

    public $perPage       = 10;
    public $search        = '';
    public $readyToLoad   = false;
    public $selectedmenus = [];
    public $selectAll     = false;

    protected $queryString = [
        'search' => ['except' => ''],
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
        if (empty($this->selectedmenus)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ منویی انتخاب نشده است.');
            return;
        }
        $this->dispatch('confirm-delete-selected'); // رویداد برای تأیید حذف انتخاب‌شده‌ها
    }

    public function deleteSelectedConfirmed()
    {
        Menu::whereIn('id', $this->selectedmenus)->delete();
        $this->selectedmenus = [];
        $this->selectAll     = false;
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

    private function getmenusQuery()
    {
        return Menu::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('url', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getmenusQuery() : null;

        return view('livewire.admin.panel.menus.menu-list', [
            'menus' => $items,
        ]);
    }
}
