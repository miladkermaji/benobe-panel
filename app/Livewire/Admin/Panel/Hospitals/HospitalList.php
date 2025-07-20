<?php

namespace App\Livewire\Admin\Panel\Hospitals;

use Livewire\Component;
use App\Models\Hospital;
use App\Models\Insurance;
use App\Models\Specialty;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Storage;

class HospitalList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteHospitalConfirmed' => 'deleteHospital'];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedHospitals = [];
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

    public function loadHospitals()
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
        $currentPageIds = $this->getHospitalsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedHospitals = $value ? $currentPageIds : [];
    }

    public function updatedSelectedHospitals()
    {
        $currentPageIds = $this->getHospitalsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedHospitals) && count(array_diff($currentPageIds, $this->selectedHospitals)) === 0;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteHospital($id)
    {
        $item = MedicalCenter::findOrFail($id);
        if ($item->avatar) {
            Storage::disk('public')->delete($item->avatar);
        }
        if ($item->documents) {
            foreach ($item->documents as $document) {
                Storage::disk('public')->delete($document);
            }
        }
        if ($item->galleries) {
            foreach ($item->galleries as $gallery) {
                Storage::disk('public')->delete($gallery['image_path']);
            }
        }
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'بیمارستان حذف شد!');
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getHospitalsQuery();
            $query->delete();
            $this->selectedHospitals = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه بیمارستان‌های فیلترشده حذف شدند!');
            return;
        }
        if (empty($this->selectedHospitals)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ بیمارستانی انتخاب نشده است.');
            return;
        }
        MedicalCenter::whereIn('id', $this->selectedHospitals)->delete();
        $this->selectedHospitals = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'بیمارستان‌های انتخاب شده حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedHospitals) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ بیمارستانی انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        if ($this->applyToAllFiltered) {
            $query = $this->getHospitalsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['is_active' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه بیمارستان‌های فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['is_active' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه بیمارستان‌های فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedHospitals = [];
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
        MedicalCenter::whereIn('id', $this->selectedHospitals)
            ->update(['is_active' => $status]);
        $this->selectedHospitals = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت بیمارستان‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    protected function getHospitalsQuery()
    {
        return MedicalCenter::where('type', 'hospital')
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%")
                      ->orWhere('title', 'like', "%$search%")
                      ->orWhereHas('doctors', function ($qq) use ($search) {
                          $qq->where('first_name', 'like', "%$search%")
                             ->orWhere('last_name', 'like', "%$search%") ;
                      })
                      ->orWhereHas('province', function ($qq) use ($search) {
                          $qq->where('name', 'like', "%$search%") ;
                      })
                      ->orWhereHas('city', function ($qq) use ($search) {
                          $qq->where('name', 'like', "%$search%") ;
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $this->totalFilteredCount = $this->readyToLoad ? $this->getHospitalsQuery()->count() : 0;
        $items = $this->readyToLoad ? $this->getHospitalsQuery()->paginate($this->perPage) : null;
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');
        $services = \App\Models\Service::pluck('name', 'id');
        return view('livewire.admin.panel.hospitals.hospital-list', [
            'hospitals' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
            'services' => $services,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
