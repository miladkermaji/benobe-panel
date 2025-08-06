<?php

namespace App\Livewire\Mc\Panel\Doctors;

use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Zone;
use App\Models\Specialty;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;

class DoctorList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $perPage = 50;
    public $selectedDoctors = [];
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
        'deleteDoctorConfirmed' => 'deleteDoctor',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed'
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadDoctors()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = Doctor::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک یافت نشد.');
            return;
        }
        $doctorName = $item->first_name . ' ' . $item->last_name;
        $action = $item->is_active ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $doctorName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        // Check if doctor is associated with this medical center
        if (!$medicalCenter->doctors()->where('doctors.id', $id)->exists()) {
            $this->dispatch('show-alert', type: 'error', message: 'این پزشک در مرکز درمانی شما ثبت نشده است.');
            return;
        }

        $doctor = Doctor::find($id);
        if (!$doctor) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک مورد نظر یافت نشد.');
            return;
        }

        // Toggle status
        $doctor->update(['is_active' => !$doctor->is_active]);

        // If doctor is not active, detach from medical center
        if (!$doctor->is_active) {
            $medicalCenter->doctors()->detach($id);
        }

        // Clear cache
        Cache::forget('mc_doctors_' . $medicalCenter->id . '_*');

        $this->dispatch('show-alert', type: 'success', message: "وضعیت پزشک {$doctor->first_name} {$doctor->last_name} تغییر کرد.");
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctor($id)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $doctor = Doctor::find($id);

        if (!$doctor) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک مورد نظر یافت نشد.');
            return;
        }

        // Check if doctor is associated with this medical center
        if (!$medicalCenter->doctors()->where('doctors.id', $id)->exists()) {
            $this->dispatch('show-alert', type: 'error', message: 'این پزشک در مرکز درمانی شما ثبت نشده است.');
            return;
        }

        // Remove doctor from medical center (don't delete the doctor completely)
        $medicalCenter->doctors()->detach($id);

        $this->dispatch('show-alert', type: 'success', message: "پزشک {$doctor->first_name} {$doctor->last_name} از مرکز درمانی شما حذف شد.");

        // Clear cache
        Cache::forget('mc_doctors_' . $medicalCenter->id . '_*');
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedDoctors = $this->getDoctorsQuery()->pluck('doctors.id')->map(fn ($id) => (string) $id);
        } else {
            $this->selectedDoctors = [];
        }
    }

    public function updatedSelectedDoctors()
    {
        $this->selectAll = false;
    }

    public function deleteSelected($allFiltered = null)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        if ($allFiltered === 'allFiltered') {
            // Delete all filtered doctors
            $doctorsToRemove = $this->getDoctorsQuery()->pluck('doctors.id')->toArray();
        } else {
            // Get doctors that belong to this medical center
            $doctorsToRemove = $medicalCenter->doctors()->whereIn('doctors.id', $this->selectedDoctors)->pluck('doctors.id')->toArray();
        }

        if (empty($doctorsToRemove)) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی برای حذف یافت نشد.');
            return;
        }

        // Remove doctors from medical center
        $medicalCenter->doctors()->detach($doctorsToRemove);

        $this->selectedDoctors = [];
        $this->selectAll = false;

        // Clear cache
        Cache::forget('mc_doctors_' . $medicalCenter->id . '_*');

        $this->dispatch('show-alert', type: 'success', message: count($doctorsToRemove) . ' پزشک از مرکز درمانی شما حذف شد.');
    }

    public function executeGroupAction()
    {
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'error', message: 'لطفاً یک عملیات انتخاب کنید.');
            return;
        }

        if ($this->groupAction === 'delete') {
            $this->dispatch('confirm-delete-selected', allFiltered: $this->applyToAllFiltered);
        } elseif (in_array($this->groupAction, ['status_active', 'status_inactive'])) {
            $status = $this->groupAction === 'status_active';
            $this->updateStatus($status);
        }

        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        if ($this->applyToAllFiltered) {
            // Update all filtered doctors
            $doctorsToUpdate = $this->getDoctorsQuery()->pluck('doctors.id')->toArray();
        } else {
            // Update selected doctors
            $doctorsToUpdate = $medicalCenter->doctors()->whereIn('doctors.id', $this->selectedDoctors)->pluck('doctors.id')->toArray();
        }

        if (empty($doctorsToUpdate)) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی برای به‌روزرسانی یافت نشد.');
            return;
        }

        // Update doctors status
        Doctor::whereIn('id', $doctorsToUpdate)->update(['is_active' => $status]);

        // If status is false, detach from medical center
        if (!$status) {
            $medicalCenter->doctors()->detach($doctorsToUpdate);
        }

        $this->selectedDoctors = [];
        $this->selectAll = false;

        // Clear cache
        Cache::forget('mc_doctors_' . $medicalCenter->id . '_*');

        $statusText = $status ? 'فعال' : 'غیرفعال';
        $this->dispatch('show-alert', type: 'success', message: count($doctorsToUpdate) . " پزشک {$statusText} شد.");
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->selectedDoctors = [];
        $this->selectAll = false;
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->selectedDoctors = [];
        $this->selectAll = false;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->selectedDoctors = [];
        $this->selectAll = false;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->selectedDoctors = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    private function getDoctorsQuery()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        return $medicalCenter->doctors()
            ->with(['province' => fn ($q) => $q->select('id', 'name'), 'city' => fn ($q) => $q->select('id', 'name')])
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                return $query->where(function ($q) use ($search) {
                    $q->where('doctors.first_name', 'like', "%{$search}%")
                      ->orWhere('doctors.last_name', 'like', "%{$search}%")
                      ->orWhere('doctors.mobile', 'like', "%{$search}%")
                      ->orWhere('doctors.email', 'like', "%{$search}%");
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                return $query->where('doctors.is_active', $this->statusFilter);
            })
            ->orderBy('doctors.created_at', 'desc');
    }

    public function render()
    {
        if (!$this->readyToLoad) {
            return view('livewire.mc.panel.doctors.doctor-list', [
                'doctors' => $this->getDoctorsQuery()->paginate($this->perPage),
                'totalFilteredCount' => 0
            ]);
        }

        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $cacheKey = "mc_doctors_{$medicalCenter->id}_" . md5($this->search . $this->statusFilter . $this->perPage);

        $doctors = Cache::remember($cacheKey, 300, function () {
            return $this->getDoctorsQuery()->paginate($this->perPage);
        });

        $this->totalFilteredCount = $doctors->total();

        return view('livewire.mc.panel.doctors.doctor-list', [
            'doctors' => $doctors
        ]);
    }
}
