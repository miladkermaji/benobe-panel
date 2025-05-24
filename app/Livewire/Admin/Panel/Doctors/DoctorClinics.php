<?php

namespace App\Livewire\Admin\Panel\Doctors;

use App\Models\Doctor;
use App\Models\Clinic;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorClinics extends Component
{
    use WithPagination;

    public $doctor;
    public $selectedClinics = [];
    public $availableClinics = [];
    public $search = '';

    public function mount($doctorId)
    {
        $this->doctor = Doctor::findOrFail($doctorId);
        $this->loadAvailableClinics();
    }

    public function loadAvailableClinics()
    {
        $this->availableClinics = Clinic::whereDoesntHave('doctors', function ($query) {
            $query->where('doctors.id', $this->doctor->id);
        })->get();
    }

    public function attachClinic($clinicId)
    {
        $this->doctor->clinics()->attach($clinicId);
        $this->loadAvailableClinics();
        $this->dispatch('show-alert', type: 'success', message: 'کلینیک با موفقیت به پزشک اضافه شد!');
    }

    public function detachClinic($clinicId)
    {
        $this->doctor->clinics()->detach($clinicId);
        $this->loadAvailableClinics();
        $this->dispatch('show-alert', type: 'success', message: 'کلینیک با موفقیت از پزشک حذف شد!');
    }

    public function render()
    {
        $doctorClinics = $this->doctor->clinics()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.admin.panel.doctors.doctor-clinics', [
            'doctorClinics' => $doctorClinics,
        ]);
    }
}
