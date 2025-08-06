<?php

namespace App\Livewire\Mc\Panel\Insurances;

use App\Models\Insurance;
use App\Models\MedicalCenter;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class InsuranceList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $perPage = 50;
    public $selectedInsurances = [];
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
        'deleteInsuranceConfirmed' => 'deleteInsurance',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed'
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadInsurances()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = Insurance::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'بیمه یافت نشد.');
            return;
        }
        $insuranceName = $item->name;
        $action = $item->status ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $insuranceName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $insurance = Insurance::find($id);
        if (!$insurance) {
            $this->dispatch('show-alert', type: 'error', message: 'بیمه مورد نظر یافت نشد.');
            return;
        }

        // Check if insurance is in medical center's insurances
        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];

        if ($insurance->status) {
            // If insurance is active, remove it from medical center
            $currentInsuranceIds = array_diff($currentInsuranceIds, [$insurance->id]);
            $action = 'حذف شد';
        } else {
            // If insurance is inactive, add it to medical center
            if (!in_array($insurance->id, $currentInsuranceIds)) {
                $currentInsuranceIds[] = $insurance->id;
            }
            $action = 'اضافه شد';
        }

        // Update medical center insurances
        $medicalCenter->update(['insurance_ids' => array_values($currentInsuranceIds)]);

        $this->dispatch('show-alert', type: 'success', message: "بیمه {$insurance->name} {$action}.");
        $this->refreshKey++;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteInsurance($id)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $insurance = Insurance::find($id);
        if (!$insurance) {
            $this->dispatch('show-alert', type: 'error', message: 'بیمه مورد نظر یافت نشد.');
            return;
        }

        // Remove insurance from medical center
        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];
        $currentInsuranceIds = array_diff($currentInsuranceIds, [$insurance->id]);
        $medicalCenter->update(['insurance_ids' => array_values($currentInsuranceIds)]);

        $this->dispatch('show-alert', type: 'success', message: "بیمه {$insurance->name} با موفقیت حذف شد.");
        $this->refreshKey++;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Get all filtered insurance IDs for selection
            /** @var MedicalCenter $medicalCenter */
            $medicalCenter = Auth::guard('medical_center')->user();
            $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];

            $query = Insurance::whereIn('id', $currentInsuranceIds);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('calculation_method', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->statusFilter !== '') {
                $query->where('status', $this->statusFilter);
            }

            $this->selectedInsurances = $query->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedInsurances = [];
        }
    }

    public function updatedSelectedInsurances()
    {
        $this->selectAll = false;
    }

    public function executeGroupAction()
    {
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً یک عملیات را انتخاب کنید.');
            return;
        }

        if (empty($this->selectedInsurances) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک بیمه را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                if ($this->applyToAllFiltered) {
                    // Delete all filtered insurances
                    $this->deleteAllFiltered();
                } else {
                    // Delete only selected insurances
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
        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];

        // Get all filtered insurance IDs
        $query = Insurance::whereIn('id', $currentInsuranceIds);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('calculation_method', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $filteredInsuranceIds = $query->pluck('id')->toArray();
        $newInsuranceIds = array_diff($currentInsuranceIds, $filteredInsuranceIds);
        $medicalCenter->update(['insurance_ids' => array_values($newInsuranceIds)]);

        $this->selectedInsurances = [];
        $this->selectAll = false;
        $this->applyToAllFiltered = false;

        $this->dispatch('show-alert', type: 'success', message: 'همه بیمه‌های فیلترشده با موفقیت حذف شدند.');
        $this->refreshKey++;
    }

    public function confirmDeleteSelected()
    {
        if (empty($this->selectedInsurances)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک بیمه را انتخاب کنید.');
            return;
        }

        $count = count($this->selectedInsurances);
        $this->dispatch('confirm-delete-selected', count: $count);
    }

    public function deleteSelected()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];
        $currentInsuranceIds = array_diff($currentInsuranceIds, $this->selectedInsurances);
        $medicalCenter->update(['insurance_ids' => array_values($currentInsuranceIds)]);

        $this->selectedInsurances = [];
        $this->selectAll = false;

        $this->dispatch('show-alert', type: 'success', message: 'بیمه‌های انتخاب شده با موفقیت حذف شدند.');
        $this->refreshKey++;
    }

    public function confirmToggleStatusSelected()
    {
        if (empty($this->selectedInsurances)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک بیمه را انتخاب کنید.');
            return;
        }

        $count = count($this->selectedInsurances);
        $this->dispatch('confirm-toggle-status-selected', count: $count);
    }

    public function toggleStatusSelected()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];

        foreach ($this->selectedInsurances as $insuranceId) {
            $insurance = Insurance::find($insuranceId);
            if ($insurance) {
                if ($insurance->status) {
                    // Remove from medical center if active
                    $currentInsuranceIds = array_diff($currentInsuranceIds, [$insuranceId]);
                } else {
                    // Add to medical center if inactive
                    if (!in_array($insuranceId, $currentInsuranceIds)) {
                        $currentInsuranceIds[] = $insuranceId;
                    }
                }
            }
        }

        $medicalCenter->update(['insurance_ids' => array_values($currentInsuranceIds)]);

        $this->selectedInsurances = [];
        $this->selectAll = false;

        $this->dispatch('show-alert', type: 'success', message: 'وضعیت بیمه‌های انتخاب شده با موفقیت تغییر کرد.');
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
            return view('livewire.mc.panel.insurances.insurance-list', [
                'insurances' => collect(),
                'totalFilteredCount' => 0,
            ]);
        }

        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];

        $query = Insurance::whereIn('id', $currentInsuranceIds);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('calculation_method', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $insurances = $query->orderBy('name', 'asc')->paginate($this->perPage);
        $this->totalFilteredCount = $insurances->total();

        return view('livewire.mc.panel.insurances.insurance-list', [
            'insurances' => $insurances,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
