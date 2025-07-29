<?php

namespace App\Livewire\Dr\Panel\DoctorsClinic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Auth;

class DoctorClinicList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteClinicConfirmed' => 'deleteClinic',
        'executeGroupAction' => 'executeGroupAction',
    ];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedClinics = [];
    public $selectAll = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadClinics()
    {
        $this->readyToLoad = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteClinic($id)
    {
        $item = MedicalCenter::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'مطب حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getClinicsQuery()->pluck('id')->toArray();
        $this->selectedClinics = $value ? $currentPageIds : [];
    }

    public function updatedSelectedClinics()
    {
        $currentPageIds = $this->getClinicsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedClinics) && count(array_diff($currentPageIds, $this->selectedClinics)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedClinics)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ مطبی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->deleteSelected();
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
        MedicalCenter::whereIn('id', $this->selectedClinics)
            ->update(['is_active' => $status]);

        $this->selectedClinics = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت مطب‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedClinics)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ مطبی انتخاب نشده است.');
            return;
        }

        MedicalCenter::whereIn('id', $this->selectedClinics)->delete();
        $this->selectedClinics = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'مطب‌های انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $clinic = MedicalCenter::findOrFail($id);
        $clinic->is_active = !$clinic->is_active;
        $clinic->save();

        $this->dispatch('show-alert', type: 'success', message: 'وضعیت مطب با موفقیت تغییر کرد.');
    }

    public function confirmGroupDelete()
    {
        if ($this->groupAction === 'delete') {
            $this->dispatch('confirm-group-delete');
        } else {
            $this->executeGroupAction();
        }
    }

    private function getClinicsQuery()
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        return MedicalCenter::whereHas('doctors', function($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })
        ->where('type', 'clinic')
        ->where(function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('address', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
        })
        ->with(['province', 'city'])
        ->orderByDesc('id');
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getClinicsQuery()->paginate($this->perPage) : null;

        return view('livewire.dr.panel.doctors-clinic.doctor-clinic-list', [
            'clinics' => $items,
        ]);
    }
}
