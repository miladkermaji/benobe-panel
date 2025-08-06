<?php

namespace App\Livewire\Mc\Panel\Specialties;

use App\Models\Specialty;
use App\Models\MedicalCenter;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class SpecialtyList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $perPage = 50;
    public $selectedSpecialties = [];
    public $selectAll = false;
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'deleteSpecialtyConfirmed' => 'deleteSpecialty',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed'
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadSpecialties()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = Specialty::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'تخصص یافت نشد.');
            return;
        }
        $specialtyName = $item->name;
        $action = $item->status ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $specialtyName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $specialty = Specialty::find($id);
        if (!$specialty) {
            $this->dispatch('show-alert', type: 'error', message: 'تخصص مورد نظر یافت نشد.');
            return;
        }

        // Check if specialty is in medical center's specialties
        $currentSpecialtyIds = $medicalCenter->specialty_ids ?? [];

        if ($specialty->status) {
            // If specialty is active, remove it from medical center
            $currentSpecialtyIds = array_diff($currentSpecialtyIds, [$specialty->id]);
            $action = 'حذف شد';
        } else {
            // If specialty is inactive, add it to medical center
            if (!in_array($specialty->id, $currentSpecialtyIds)) {
                $currentSpecialtyIds[] = $specialty->id;
            }
            $action = 'اضافه شد';
        }

        // Update medical center specialties
        $medicalCenter->update(['specialty_ids' => array_values($currentSpecialtyIds)]);

        $this->dispatch('show-alert', type: 'success', message: "تخصص {$specialty->name} {$action}.");
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteSpecialty($id)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $specialty = Specialty::find($id);
        if (!$specialty) {
            $this->dispatch('show-alert', type: 'error', message: 'تخصص مورد نظر یافت نشد.');
            return;
        }

        // Remove specialty from medical center
        $currentSpecialtyIds = $medicalCenter->specialty_ids ?? [];
        $currentSpecialtyIds = array_diff($currentSpecialtyIds, [$specialty->id]);
        $medicalCenter->update(['specialty_ids' => array_values($currentSpecialtyIds)]);

        $this->dispatch('show-alert', type: 'success', message: "تخصص {$specialty->name} حذف شد.");
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedSpecialties = $this->getSpecialtiesQuery()->pluck('specialties.id')->toArray();
        } else {
            $this->selectedSpecialties = [];
        }
    }

    public function updatedSelectedSpecialties()
    {
        $this->selectAll = false;
    }

    public function deleteSelected($allFiltered = null)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        if ($this->applyToAllFiltered) {
            $specialtiesToDelete = $this->getSpecialtiesQuery()->pluck('specialties.id')->toArray();
        } else {
            $specialtiesToDelete = $this->selectedSpecialties;
        }

        if (empty($specialtiesToDelete)) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ تخصصی برای حذف انتخاب نشده است.');
            return;
        }

        // Remove specialties from medical center
        $currentSpecialtyIds = $medicalCenter->specialty_ids ?? [];
        $currentSpecialtyIds = array_diff($currentSpecialtyIds, $specialtiesToDelete);
        $medicalCenter->update(['specialty_ids' => array_values($currentSpecialtyIds)]);

        $this->selectedSpecialties = [];
        $this->selectAll = false;

        $this->dispatch('show-alert', type: 'success', message: count($specialtiesToDelete) . " تخصص حذف شد.");
    }

    public function executeGroupAction()
    {
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'error', message: 'لطفاً عملیات مورد نظر را انتخاب کنید.');
            return;
        }

        if (empty($this->selectedSpecialties) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'error', message: 'لطفاً حداقل یک تخصص را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', allFiltered: $this->applyToAllFiltered);
                break;
            default:
                $this->dispatch('show-alert', type: 'error', message: 'عملیات نامعتبر است.');
        }

        $this->groupAction = '';
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->selectedSpecialties = [];
        $this->selectAll = false;
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->selectedSpecialties = [];
        $this->selectAll = false;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->selectedSpecialties = [];
        $this->selectAll = false;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->selectedSpecialties = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    private function getSpecialtiesQuery()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentSpecialtyIds = $medicalCenter->specialty_ids ?? [];

        return Specialty::whereIn('id', $currentSpecialtyIds)
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->orderBy('name', 'asc');
    }

    public function render()
    {
        if (!$this->readyToLoad) {
            return view('livewire.mc.panel.specialties.specialty-list', [
                'specialties' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'totalFilteredCount' => 0
            ]);
        }

        $specialties = $this->getSpecialtiesQuery()->paginate($this->perPage);
        $this->totalFilteredCount = $specialties->total();

        return view('livewire.mc.panel.specialties.specialty-list', [
            'specialties' => $specialties
        ]);
    }
}
