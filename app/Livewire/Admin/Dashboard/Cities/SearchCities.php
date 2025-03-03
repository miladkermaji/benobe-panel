<?php

namespace App\Livewire\Admin\Dashboard\Cities;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Dashboard\Cities\Zone;

class SearchCities extends Component
{
    use WithPagination;

    public $provinceId;
    public $search = ''; // مقدار جستجو
    public $selectedRows = [];
    public $selectAll = false;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['updateDeleteButton' => 'refreshDeleteButton', 'doDeleteSelected' => 'deleteSelected'];

    public function mount($provinceId)
    {
        $this->provinceId = $provinceId;
    }

    // جستجو در هنگام تایپ کردن
    public function updatingSearch()
    {
        $this->resetPage(); // هنگام تغییر مقدار جستجو، صفحه را ریست می‌کند
    }

    public function updatedSelectAll($value)
    {
        $this->selectedRows = $value ? Zone::where('parent_id', $this->provinceId)->pluck('id')->toArray() : [];
        $this->dispatch('refreshDeleteButton', hasSelectedRows: count($this->selectedRows) > 0);
    }

    public function toggleStatus($id)
    {
        $city = Zone::find($id);
        $city->status = !$city->status;
        $city->save();

        $this->dispatch('show-toastr', type: 'success', message: 'وضعیت شهر با موفقیت تغییر کرد.');
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
            $this->selectAll = false;
            $this->dispatch('refreshDeleteButton', hasSelectedRows: false);
            $this->dispatch('show-toastr', type: 'success', message: 'شهرهای انتخاب‌شده با موفقیت حذف شدند.');
        }
    }
    public function searchUpdated()
    {
        \Log::info("Search Query in searchUpdated: " . $this->search);
        $this->resetPage(); // هنگام تایپ در فیلد جستجو، صفحه را ریست می‌کند تا نتایج صحیح نمایش داده شوند.
    }

    public function render()
    {
        \Log::info("Search Query in Render: " . $this->search); // تست مقدار جستجو در render

        $cities = Zone::where('parent_id', $this->provinceId)
            ->when(!empty($this->search), function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.admin.dashboard.cities.search-cities', compact('cities'));
    }


 
    public function updatedSearch($value)
    {
    }



}
