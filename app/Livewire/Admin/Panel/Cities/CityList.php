<?php

namespace App\Livewire\Admin\Panel\Cities;

use App\Models\Zone;
use Livewire\Component;
use Livewire\WithPagination;

class CityList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteCityConfirmed' => 'deleteCity'];

    public $perPage        = 40;
    public $search         = '';
    public $readyToLoad    = false;
    public $selectedCities = [];
    public $selectAll      = false;
    public $province_id;
    public $debugCount;
    public $debugProvinceId;
    public $statusFilter = '';
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'province_id' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
        // گرفتن province_id از URL به جای پارامتر مستقیم
        $this->province_id     = request()->query('province_id');
        $this->debugProvinceId = $this->province_id; // دیباگ مقدار اولیه
        $this->debugCount      = $this->getTotalCities();
    }

    public function loadCities()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = Zone::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteCity($id)
    {
        $item = Zone::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'شهر حذف شد!');
        $this->debugCount = $this->getTotalCities();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->debugCount = $this->getTotalCities();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds       = $this->getCities()->pluck('id')->toArray();
        $this->selectedCities = $value ? $currentPageIds : [];
    }

    public function updatedSelectedCities()
    {
        $currentPageIds  = $this->getCities()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedCities) && count(array_diff($currentPageIds, $this->selectedCities)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedCities)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ شهری انتخاب نشده است.');
            return;
        }

        Zone::whereIn('id', $this->selectedCities)->delete();
        $this->selectedCities = [];
        $this->selectAll      = false;
        $this->dispatch('show-alert', type: 'success', message: 'شهرهای انتخاب‌شده حذف شدند!');
        $this->debugCount = $this->getTotalCities();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->debugCount = $this->getTotalCities();
    }

    public function updatedGroupAction()
    {
        // Optional: reset selection or handle UI
    }

    public function executeGroupAction()
    {
        if ($this->groupAction === 'delete') {
            if ($this->applyToAllFiltered) {
                $query = $this->getCities(false);
                $query->delete();
                $this->selectedCities = [];
                $this->selectAll = false;
                $this->applyToAllFiltered = false;
                $this->groupAction = '';
                $this->resetPage();
                $this->dispatch('show-alert', type: 'success', message: 'همه شهرهای فیلترشده حذف شدند!');
                $this->debugCount = $this->getTotalCities();
                return;
            }
            if (empty($this->selectedCities)) {
                return;
            }
            \App\Models\Zone::whereIn('id', $this->selectedCities)->delete();
            $this->selectedCities = [];
            $this->selectAll = false;
            $this->dispatch('show-alert', type: 'success', message: 'شهرهای انتخاب شده با موفقیت حذف شدند!');
            $this->debugCount = $this->getTotalCities();
        } elseif ($this->groupAction === 'status_active') {
            $query = $this->applyToAllFiltered ? $this->getCities(false) : \App\Models\Zone::whereIn('id', $this->selectedCities);
            $query->update(['status' => true]);
            $this->dispatch('show-alert', type: 'success', message: 'شهرها فعال شدند!');
        } elseif ($this->groupAction === 'status_inactive') {
            $query = $this->applyToAllFiltered ? $this->getCities(false) : \App\Models\Zone::whereIn('id', $this->selectedCities);
            $query->update(['status' => false]);
            $this->dispatch('show-alert', type: 'info', message: 'شهرها غیرفعال شدند!');
        }
        $this->groupAction = '';
        $this->applyToAllFiltered = false;
        $this->selectedCities = [];
        $this->selectAll = false;
        $this->resetPage();
        $this->debugCount = $this->getTotalCities();
    }

    public function updatedApplyToAllFiltered($value)
    {
        // No-op, just for UI
    }

    private function getTotalCities()
    {
        $query = Zone::where('level', 2) // فقط شهرها
            ->where('name', 'like', '%' . $this->search . '%');

        if ($this->province_id) {
            $query->where('parent_id', $this->province_id);
        }

        return $query->count();
    }

    private function getCities($paginate = true)
    {
        $query = \App\Models\Zone::query()->where('level', 2);
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        if ($this->province_id) {
            $query->where('parent_id', $this->province_id);
        }
        if ($this->statusFilter === 'active') {
            $query->where('status', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('status', false);
        }
        $query->orderBy('sort');
        return $paginate ? $query->paginate($this->perPage) : $query;
    }

    public function render()
    {
        $cities   = $this->readyToLoad ? $this->getCities() : null;
        $province = $this->province_id ? \App\Models\Zone::find($this->province_id) : null;
        $this->debugCount      = $this->getTotalCities();
        $this->debugProvinceId = $this->province_id;
        $this->totalFilteredCount = $this->readyToLoad ? $this->getCities(false)->count() : 0;
        return view('livewire.admin.panel.cities.city-list', [
            'cities'          => $cities,
            'province'        => $province,
            'debugCount'      => $this->debugCount,
            'debugProvinceId' => $this->debugProvinceId,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
