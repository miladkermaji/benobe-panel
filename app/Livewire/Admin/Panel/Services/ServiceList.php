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

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedServices = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadServices()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getServicesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedServices = $value ? $currentPageIds : [];
    }

    public function updatedSelectedServices()
    {
        $currentPageIds = $this->getServicesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedServices) && count(array_diff($currentPageIds, $this->selectedServices)) === 0;
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

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getServicesQuery();
            $query->delete();
            $this->selectedServices = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
        $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه خدمات فیلترشده حذف شدند!');
            return;
        }
        if (empty($this->selectedServices)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ خدمتی انتخاب نشده است.');
            return;
        }
        Service::whereIn('id', $this->selectedServices)->delete();
        $this->selectedServices = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'خدمات انتخاب شده حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedServices) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ خدمتی انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        if ($this->applyToAllFiltered) {
            $query = $this->getServicesQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['status' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه خدمات فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه خدمات فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedServices = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            return;
        }
        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'status_active':
                $this->updateStatus(true);
                break;
            case 'status_inactive':
                $this->updateStatus(false);
                break;
        }
        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        Service::whereIn('id', $this->selectedServices)
            ->update(['status' => $status]);
        $this->selectedServices = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت خدمات انتخاب‌شده با موفقیت تغییر کرد.');
    }

    protected function getServicesQuery()
    {
        return Service::when($this->search, function ($query) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%") ;
            });
        })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('status', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('status', false);
                }
            })
            ->orderBy('name');
    }

    public function render()
    {
        $this->totalFilteredCount = $this->readyToLoad ? $this->getServicesQuery()->count() : 0;
        $items = $this->readyToLoad ? $this->getServicesQuery()->paginate($this->perPage) : null;
        return view('livewire.admin.panel.services.service-list', [
            'services' => $items,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
