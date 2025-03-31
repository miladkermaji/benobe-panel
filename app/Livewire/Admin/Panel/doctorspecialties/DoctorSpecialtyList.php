<?php

namespace App\Livewire\Admin\Panel\DoctorSpecialties;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Doctor;
use App\Models\DoctorSpecialty;

class DoctorSpecialtyList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorSpecialtyConfirmed' => 'deleteDoctorSpecialty'];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorSpecialties = [];
    public $expandedDoctors = [];
    public $selectAll = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadDoctorSpecialties()
    {
        $this->readyToLoad = true;
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorSpecialty($id)
    {
        $specialty = DoctorSpecialty::findOrFail($id);
        $specialty->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تخصص پزشک با موفقیت حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value, $doctorId)
    {
        $specialtyIds = $this->getSpecialtiesQuery($doctorId)->pluck('id')->toArray();
        if ($value) {
            $this->selectedDoctorSpecialties = array_unique(array_merge($this->selectedDoctorSpecialties, $specialtyIds));
        } else {
            $this->selectedDoctorSpecialties = array_diff($this->selectedDoctorSpecialties, $specialtyIds);
        }
    }

    public function updatedSelectedDoctorSpecialties()
    {
        foreach ($this->getDoctors() as $doctor) {
            $doctorId = $doctor->id;
            $specialtyIds = $this->getSpecialtiesQuery($doctorId)->pluck('id')->toArray();
            $this->selectAll[$doctorId] = !empty($specialtyIds) && empty(array_diff($specialtyIds, $this->selectedDoctorSpecialties));
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedDoctorSpecialties)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ تخصصی انتخاب نشده است.');
            return;
        }

        DoctorSpecialty::whereIn('id', $this->selectedDoctorSpecialties)->delete();
        $this->selectedDoctorSpecialties = [];
        $this->selectAll = [];
        $this->dispatch('show-alert', type: 'success', message: 'تخصص‌های انتخاب‌شده حذف شدند!');
    }

    private function getDoctors()
    {
        $query = Doctor::with('doctorSpecialties.specialty')
            ->whereHas('doctorSpecialties', function ($q) {
                $q->where('specialty_title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('specialty', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            })
            ->orWhere(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });

        return $query->orderBy('created_at', 'desc')->get();
    }

    private function getSpecialtiesQuery($doctorId)
    {
        $query = DoctorSpecialty::where('doctor_id', $doctorId)
            ->with('specialty')
            ->where(function ($q) {
                $q->where('specialty_title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('specialty', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $doctors = $this->readyToLoad ? $this->getDoctors() : [];

        return view('livewire.admin.panel.doctorspecialties.doctorspecialty-list', [
            'doctors' => $doctors,
        ]);
    }
}
