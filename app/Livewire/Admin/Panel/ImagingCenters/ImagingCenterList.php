<?php

namespace App\Livewire\Admin\Panel\ImagingCenters;

use App\Models\ImagingCenter;
use Livewire\Component;
use Livewire\WithPagination;

class ImagingCenterList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteImagingCenterConfirmed' => 'deleteImagingCenter'];

    public $perPage                = 10;
    public $search                 = '';
    public $readyToLoad            = false;
    public $selectedImagingCenters = [];
    public $selectAll              = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadImagingCenters()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = ImagingCenter::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
        $this->dispatch('show-alert', type: $item->is_active ? 'success' : 'info', message: $item->is_active ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteImagingCenter($id)
    {
        $item = ImagingCenter::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'مرکز تصویربرداری حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds               = $this->getImagingCentersQuery()->pluck('id')->toArray();
        $this->selectedImagingCenters = $value ? $currentPageIds : [];
    }

    public function updatedSelectedImagingCenters()
    {
        $currentPageIds  = $this->getImagingCentersQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedImagingCenters) && count(array_diff($currentPageIds, $this->selectedImagingCenters)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedImagingCenters)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ مرکز تصویربرداری انتخاب نشده است.');
            return;
        }

        ImagingCenter::whereIn('id', $this->selectedImagingCenters)->delete();
        $this->selectedImagingCenters = [];
        $this->selectAll              = false;
        $this->dispatch('show-alert', type: 'success', message: 'مراکز تصویربرداری انتخاب‌شده حذف شدند!');
    }

    private function getImagingCentersQuery()
    {
        return ImagingCenter::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->with([
                'doctor'   => fn ($query) => $query->select('id', 'first_name', 'last_name'),
                'province' => fn ($query) => $query->select('id', 'name'),
                'city'     => fn ($query) => $query->select('id', 'name'),
                'galleries',
            ])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getImagingCentersQuery() : null;
        return view('livewire.admin.panel.imaging-centers.imaging-center-list', [
            'imaging_centers' => $items,
        ]);
    }
}
