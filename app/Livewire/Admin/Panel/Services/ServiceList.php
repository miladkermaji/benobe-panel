<?php

namespace App\Livewire\Admin\Panel\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteServiceConfirmed' => 'deleteService'];

    public $perPage          = 10;
    public $search           = '';
    public $readyToLoad      = false;
    public $selectedServices = [];
    public $selectAll        = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadServices()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = Service::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteService($id)
    {
        $item = Service::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'خدمت حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds         = $this->getServicesQuery()->pluck('id')->toArray();
        $this->selectedServices = $value ? $currentPageIds : [];
    }

    public function updatedSelectedServices()
    {
        $currentPageIds  = $this->getServicesQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedServices) && count(array_diff($currentPageIds, $this->selectedServices)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedServices)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ خدمتی انتخاب نشده است.');
            return;
        }

        Service::whereIn('id', $this->selectedServices)->delete();
        $this->selectedServices = [];
        $this->selectAll        = false;
        $this->dispatch('show-alert', type: 'success', message: 'خدمات انتخاب‌شده حذف شدند!');
    }

    private function getServicesQuery()
    {
        return Service::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getServicesQuery() : null;

        return view('livewire.admin.panel.services.service-list', [
            'services' => $items,
        ]);
    }
}
