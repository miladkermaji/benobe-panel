<?php

namespace App\Livewire\Admin\Panel\DoctorServices;

use App\Models\DoctorService;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorServiceList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorServiceConfirmed' => 'deleteDoctorService'];

    public $perPage = 100; // پیجینیشن اصلی صفحه (در صورت نیاز)
    public $servicesPerPage = 5; // پیجینیشن محلی برای خدمات هر پزشک
    public $search = '';
    public $readyToLoad = false;
    public $expandedDoctors = [];
    public $doctorPages = []; // آرایه برای ذخیره صفحه فعلی خدمات برای هر پزشک

    protected $queryString = [
        'search' => ['except' => ''],
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

    public function toggleStatus($id)
    {
        $item = DoctorService::findOrFail($id);
        $item->update(['status' => !$item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
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

    public function updatedSearch()
    {
        $this->resetPage();
        $this->doctorPages = []; // ریست کردن پیجینیشن محلی هنگام جستجو
    }

    private function getDoctorServicesQuery()
    {
        return DoctorService::with(['doctor', 'parent'])
            ->where(function ($query) {
                $query->whereHas('doctor', function ($q) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
                })
                ->orWhere('name', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc');
    }

    public function render()
    {
        $doctors = $this->readyToLoad ? $this->getDoctorServicesQuery()
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
            'doctors' => $doctors,
        ]);
    }
}
