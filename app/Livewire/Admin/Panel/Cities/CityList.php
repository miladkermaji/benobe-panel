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

    public $perPage        = 10;
    public $search         = '';
    public $readyToLoad    = false;
    public $selectedCities = [];
    public $selectAll      = false;
    public $province_id;
    public $debugCount;
    public $debugProvinceId;

    protected $queryString = [
        'search'      => ['except' => ''],
        'province_id' => ['except' => ''], // province_id توی URL می‌مونه
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

    private function getTotalCities()
    {
        $query = Zone::where('level', 2) // فقط شهرها
            ->where('name', 'like', '%' . $this->search . '%');

        if ($this->province_id) {
            $query->where('parent_id', $this->province_id);
        }

        return $query->count();
    }

    private function getCities()
    {
        $query = Zone::where('level', 2) // فقط شهرها
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('sort');

        if ($this->province_id) {
            $query->where('parent_id', $this->province_id);
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        $cities   = $this->readyToLoad ? $this->getCities() : null;
        $province = $this->province_id ? Zone::find($this->province_id) : null;

        $this->debugCount      = $this->getTotalCities();
        $this->debugProvinceId = $this->province_id;

        return view('livewire.admin.panel.cities.city-list', [
            'cities'          => $cities,
            'province'        => $province,
            'debugCount'      => $this->debugCount,
            'debugProvinceId' => $this->debugProvinceId,
        ]);
    }
}
