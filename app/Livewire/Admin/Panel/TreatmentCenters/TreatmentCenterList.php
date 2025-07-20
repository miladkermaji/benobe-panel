<?php

namespace App\Livewire\Admin\Panel\TreatmentCenters;

use Livewire\Component;
use App\Models\Insurance;
use App\Models\Specialty;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use App\Models\TreatmentCenter;
use Illuminate\Support\Facades\Storage;

class TreatmentCenterList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteTreatmentCenterConfirmed' => 'deleteTreatmentCenter'];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedTreatmentCenters = [];
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

    public function loadTreatmentCenters()
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
        $currentPageIds = $this->getTreatmentCentersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedTreatmentCenters = $value ? $currentPageIds : [];
    }

    public function updatedSelectedTreatmentCenters()
    {
        $currentPageIds = $this->getTreatmentCentersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedTreatmentCenters) && count(array_diff($currentPageIds, $this->selectedTreatmentCenters)) === 0;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteTreatmentCenter($id)
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
        $this->dispatch('show-alert', type: 'success', message: 'درمانگاه حذف شد!');
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getTreatmentCentersQuery();
            $query->delete();
            $this->selectedTreatmentCenters = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه درمانگاه‌های فیلترشده حذف شدند!');
            return;
        }
        if (empty($this->selectedTreatmentCenters)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ درمانگاهی انتخاب نشده است.');
            return;
        }
        MedicalCenter::whereIn('id', $this->selectedTreatmentCenters)->delete();
        $this->selectedTreatmentCenters = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'درمانگاه‌های انتخاب شده حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedTreatmentCenters) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ درمانگاهی انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        if ($this->applyToAllFiltered) {
            $query = $this->getTreatmentCentersQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['is_active' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه درمانگاه‌های فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['is_active' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه درمانگاه‌های فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedTreatmentCenters = [];
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
        MedicalCenter::whereIn('id', $this->selectedTreatmentCenters)
            ->update(['is_active' => $status]);
        $this->selectedTreatmentCenters = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت درمانگاه‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    protected function getTreatmentCentersQuery()
    {
        return MedicalCenter::where('type', 'treatment_centers')
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
        $this->totalFilteredCount = $this->readyToLoad ? $this->getTreatmentCentersQuery()->count() : 0;
        $items = $this->readyToLoad ? $this->getTreatmentCentersQuery()->paginate($this->perPage) : null;
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');
        $services = \App\Models\Service::pluck('name', 'id');
        return view('livewire.admin.panel.treatment-centers.treatment-centers-list', [
            'treatmentCenters' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
            'services' => $services,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
