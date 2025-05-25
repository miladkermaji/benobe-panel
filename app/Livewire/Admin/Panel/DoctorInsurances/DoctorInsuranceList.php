<?php

namespace App\Livewire\Admin\Panel\DoctorInsurances;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Doctor;
use App\Models\Insurance;
use App\Models\DoctorInsurance;

class DoctorInsuranceList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteInsuranceConfirmed' => 'deleteInsurance'];

    public $search = '';
    public $readyToLoad = false;
    public $expandedDoctors = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->readyToLoad = false;
    }

    public function loadInsurances()
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

    public function deleteInsurance($id)
    {
        $insurance = Insurance::findOrFail($id);
        DoctorInsurance::where('insurance_id', $insurance->id)->delete();
        $insurance->delete();
        $this->dispatch('show-alert', type: 'success', message: 'بیمه با موفقیت حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $doctors = $this->readyToLoad
            ? Doctor::where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
            })->with('insurances')->paginate(100)
            : collect();

        return view('livewire.admin.panel.doctor-insurances.doctor-insurance-list', [
            'doctors' => $doctors,
        ]);
    }
}
