<?php
namespace App\Livewire\Admin\Dashboard\Cities;

use App\Models\Zone;
use Livewire\Component;
use Livewire\WithPagination;

class SearchZones extends Component
{
    use WithPagination;

    public $search       = '';
    public $selectedRows = []; // آرایه‌ای برای ذخیره ردیف‌های انتخاب‌شده
    public $selectAll    = false;
    protected $listeners = [
        'updateDeleteButton' => 'refreshDeleteButton',
        'doDeleteSelected'   => 'deleteSelected', // تست اجرای متد
    ];
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $cities = Zone::where('level', '1')
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.admin.dashboard.cities.search-zones', compact('cities'));
    }

    public function toggleStatus($id)
    {
        $city         = Zone::find($id);
        $city->status = $city->status == 1 ? 0 : 1;
        $city->save();

        $this->dispatch('show-toastr', type: $city->status == 1 ? 'success' : 'warning', message: 'وضعیت استان بروزرسانی شد.');
    }

    // ✅ متد حذف گروهی
    public function refreshDeleteButton()
    {
        $this->dispatch('refreshDeleteButton', hasSelectedRows: count($this->selectedRows) > 0);
    }

    public function confirmDelete()
    {
        if (count($this->selectedRows) > 0) {
            $this->dispatch('show-delete-confirmation');
        }
    }

    public function deleteSelected()
    {
        if (count($this->selectedRows) > 0) {
            Zone::whereIn('id', $this->selectedRows)->delete();
            $this->selectedRows = [];
            $this->selectAll    = false;
            $this->dispatch('refreshDeleteButton', hasSelectedRows: false);
            $this->dispatch('show-toastr', type: 'success', message: 'استان‌های انتخاب‌شده با موفقیت حذف شدند.');
        }
    }

    // ✅ مدیریت انتخاب همه
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = Zone::where('level', '1')->pluck('id')->toArray();
        } else {
            $this->selectedRows = [];
        }
        $this->dispatch('refreshDeleteButton', hasSelectedRows: count($this->selectedRows) > 0);
    }

    public function updatedSelectedRows()
    {
        $this->dispatch('refreshDeleteButton');
    }
    public function searchUpdated()
    {
        $this->resetPage(); // هنگام تغییر مقدار جستجو، صفحه را ریست می‌کند
    }

}
