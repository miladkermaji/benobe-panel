<?php

namespace App\Livewire\Admin\Panel\DoctorServices;

use App\Models\DoctorService;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorServiceList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteDoctorServiceConfirmed' => 'deleteDoctorService',
        'deleteDoctorServiceGroupConfirmed' => 'deleteSelected',
    ];

    public $perPage = 50;
    public $servicesPerPage = 5;
    public $search = '';
    public $readyToLoad = false;
    public $expandedDoctors = [];
    public $doctorPages = [];
    public $selectedDoctorServices = [];
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
        $this->servicesPerPage = max($this->servicesPerPage, 1);
    }

    public function loadDoctorServices()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->doctorPages = [];
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->doctorPages = [];
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getDoctorServicesQuery()->pluck('id')->toArray();
        $this->selectedDoctorServices = $value ? $currentPageIds : [];
    }

    public function updatedSelectedDoctorServices()
    {
        $currentPageIds = $this->getDoctorServicesQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedDoctorServices) && count(array_diff($currentPageIds, $this->selectedDoctorServices)) === 0;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorService($id)
    {
        $item = DoctorService::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'خدمت پزشک حذف شد!');
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getDoctorServicesQuery();
            $query->delete();
            $this->selectedDoctorServices = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه خدمات پزشک فیلترشده حذف شدند!');
            return;
        }
        if (empty($this->selectedDoctorServices)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ خدمتی انتخاب نشده است.');
            return;
        }
        DoctorService::whereIn('id', $this->selectedDoctorServices)->delete();
        $this->selectedDoctorServices = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'خدمات پزشک انتخاب شده حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedDoctorServices) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ خدمتی انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        if ($this->applyToAllFiltered) {
            $query = $this->getDoctorServicesQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['status' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه خدمات پزشک فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه خدمات پزشک فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedDoctorServices = [];
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
        DoctorService::whereIn('id', $this->selectedDoctorServices)
            ->update(['status' => $status]);
        $this->selectedDoctorServices = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت خدمات پزشک انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
            if (!isset($this->doctorPages[$doctorId])) {
                $this->doctorPages[$doctorId] = 1;
            }
        }
    }

    public function setDoctorPage($doctorId, $page)
    {
        $this->doctorPages[$doctorId] = max(1, $page);
    }

    protected function getDoctorServicesQuery()
    {
        return DoctorService::with(['doctor', 'service', 'insurance', 'parent'])
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search) {
                    $q->whereHas('doctor', function ($qq) use ($search) {
                        $qq->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                    })
                    ->orWhereHas('service', function ($qq) use ($search) {
                        $qq->where('name', 'like', "%$search%") ;
                    })
                    ->orWhere('name', 'like', "%$search%")
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
            ->orderBy('id', 'desc');
    }

    public function render()
    {
        $this->totalFilteredCount = $this->readyToLoad ? $this->getDoctorServicesQuery()->count() : 0;
        $grouped = $this->readyToLoad ? $this->getDoctorServicesQuery()
            ->get()
            ->groupBy('doctor_id')
            ->map(function ($services, $doctorId) {
                $currentPage = $this->doctorPages[$doctorId] ?? 1;
                $paginatedServices = $services->forPage($currentPage, $this->servicesPerPage);
                return [
                    'doctor' => $services->first()->doctor,
                    'services' => $paginatedServices->values(),
                    'totalServices' => $services->count(),
                    'currentPage' => $currentPage,
                    'lastPage' => ceil($services->count() / $this->servicesPerPage),
                ];
            }) : [];
        return view('livewire.admin.panel.doctor-services.doctor-service-list', [
            'doctors' => $grouped,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
