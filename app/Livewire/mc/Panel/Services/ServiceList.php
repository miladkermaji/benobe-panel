<?php

namespace App\Livewire\Mc\Panel\Services;

use App\Models\Service;
use App\Models\MedicalCenter;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ServiceList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $perPage = 50;
    public $selectedServices = [];
    public $selectAll = false;
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $readyToLoad = false;
    public $refreshKey = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'deleteServiceConfirmed' => 'deleteService',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed'
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadServices()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = Service::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'خدمت یافت نشد.');
            return;
        }
        $serviceName = $item->name;
        $action = $item->status ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $serviceName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $service = Service::find($id);
        if (!$service) {
            $this->dispatch('show-alert', type: 'error', message: 'خدمت مورد نظر یافت نشد.');
            return;
        }

        // Check if service is in medical center's services
        $currentServiceIds = $medicalCenter->service_ids ?? [];

        if ($service->status) {
            // If service is active, remove it from medical center
            $currentServiceIds = array_diff($currentServiceIds, [$service->id]);
            $action = 'حذف شد';
        } else {
            // If service is inactive, add it to medical center
            if (!in_array($service->id, $currentServiceIds)) {
                $currentServiceIds[] = $service->id;
            }
            $action = 'اضافه شد';
        }

        // Update medical center services
        $medicalCenter->update(['service_ids' => array_values($currentServiceIds)]);

        $this->dispatch('show-alert', type: 'success', message: "خدمت {$service->name} {$action}.");
        $this->refreshKey++;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteService($id)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $service = Service::find($id);
        if (!$service) {
            $this->dispatch('show-alert', type: 'error', message: 'خدمت مورد نظر یافت نشد.');
            return;
        }

        // Remove service from medical center
        $currentServiceIds = $medicalCenter->service_ids ?? [];
        $currentServiceIds = array_diff($currentServiceIds, [$service->id]);
        $medicalCenter->update(['service_ids' => array_values($currentServiceIds)]);

        $this->dispatch('show-alert', type: 'success', message: "خدمت {$service->name} با موفقیت حذف شد.");
        $this->refreshKey++;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Get all filtered service IDs for selection
            /** @var MedicalCenter $medicalCenter */
            $medicalCenter = Auth::guard('medical_center')->user();
            $currentServiceIds = $medicalCenter->service_ids ?? [];

            $query = Service::whereIn('id', $currentServiceIds);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->statusFilter !== '') {
                $query->where('status', $this->statusFilter);
            }

            $this->selectedServices = $query->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedServices = [];
        }
    }

    public function updatedSelectedServices()
    {
        $this->selectAll = false;
    }

    public function executeGroupAction()
    {
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً یک عملیات را انتخاب کنید.');
            return;
        }

        if (empty($this->selectedServices) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک خدمت را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                if ($this->applyToAllFiltered) {
                    // Delete all filtered services
                    $this->deleteAllFiltered();
                } else {
                    // Delete only selected services
                    $this->confirmDeleteSelected();
                }
                break;
            default:
                $this->dispatch('show-alert', type: 'warning', message: 'عملیات انتخاب شده معتبر نیست.');
                break;
        }

        $this->groupAction = '';
    }

    public function deleteAllFiltered()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentServiceIds = $medicalCenter->service_ids ?? [];

        // Get all filtered service IDs
        $query = Service::whereIn('id', $currentServiceIds);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $filteredServiceIds = $query->pluck('id')->toArray();
        $newServiceIds = array_diff($currentServiceIds, $filteredServiceIds);
        $medicalCenter->update(['service_ids' => array_values($newServiceIds)]);

        $this->selectedServices = [];
        $this->selectAll = false;
        $this->applyToAllFiltered = false;

        $this->dispatch('show-alert', type: 'success', message: 'همه خدمت‌های فیلترشده با موفقیت حذف شدند.');
        $this->refreshKey++;
    }

    public function confirmDeleteSelected()
    {
        if (empty($this->selectedServices)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک خدمت را انتخاب کنید.');
            return;
        }

        $count = count($this->selectedServices);
        $this->dispatch('confirm-delete-selected', count: $count);
    }

    public function deleteSelected()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $currentServiceIds = $medicalCenter->service_ids ?? [];
        $currentServiceIds = array_diff($currentServiceIds, $this->selectedServices);
        $medicalCenter->update(['service_ids' => array_values($currentServiceIds)]);

        $this->selectedServices = [];
        $this->selectAll = false;

        $this->dispatch('show-alert', type: 'success', message: 'خدمت‌های انتخاب شده با موفقیت حذف شدند.');
        $this->refreshKey++;
    }

    public function confirmToggleStatusSelected()
    {
        if (empty($this->selectedServices)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک خدمت را انتخاب کنید.');
            return;
        }

        $count = count($this->selectedServices);
        $this->dispatch('confirm-toggle-status-selected', count: $count);
    }

    public function toggleStatusSelected()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $currentServiceIds = $medicalCenter->service_ids ?? [];

        foreach ($this->selectedServices as $serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                if ($service->status) {
                    // Remove from medical center if active
                    $currentServiceIds = array_diff($currentServiceIds, [$serviceId]);
                } else {
                    // Add to medical center if inactive
                    if (!in_array($serviceId, $currentServiceIds)) {
                        $currentServiceIds[] = $serviceId;
                    }
                }
            }
        }

        $medicalCenter->update(['service_ids' => array_values($currentServiceIds)]);

        $this->selectedServices = [];
        $this->selectAll = false;

        $this->dispatch('show-alert', type: 'success', message: 'وضعیت خدمت‌های انتخاب شده با موفقیت تغییر کرد.');
        $this->refreshKey++;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (!$this->readyToLoad) {
            return view('livewire.mc.panel.services.service-list', [
                'services' => collect(),
                'totalFilteredCount' => 0,
            ]);
        }

        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentServiceIds = $medicalCenter->service_ids ?? [];

        $query = Service::whereIn('id', $currentServiceIds);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $services = $query->orderBy('name', 'asc')->paginate($this->perPage);
        $this->totalFilteredCount = $services->total();

        return view('livewire.mc.panel.services.service-list', [
            'services' => $services,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
