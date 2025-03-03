<?php

namespace App\Livewire\Admin\Dashboard\HomePage;

use Livewire\Component;
use App\Models\Hospital;
use App\Models\Dr\Doctor;
use App\Models\Admin\Dashboard\HomePage\BestDoctor;

class EditBestDoctor extends Component
{
    public $bestDoctorId; // ID پزشک برتر برای ویرایش
    public $doctor_id; // ID پزشک
    public $hospital_id; // ID بیمارستان
    public $best_doctor = false; // پزشک برتر
    public $best_consultant = false; // مشاور تلفنی برتر

    public function mount($bestDoctorId)
    {
        $this->bestDoctorId = $bestDoctorId;
        $bestDoctor = BestDoctor::findOrFail($bestDoctorId);
        $this->doctor_id = $bestDoctor->doctor_id;
        $this->hospital_id = $bestDoctor->hospital_id;
        $this->best_doctor = $bestDoctor->best_doctor;
        $this->best_consultant = $bestDoctor->best_consultant;
    }

    public function update()
    {
        $this->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'hospital_id' => 'nullable|exists:hospitals,id',
            'best_doctor' => 'boolean',
            'best_consultant' => 'boolean',
        ]);

        $bestDoctor = BestDoctor::findOrFail($this->bestDoctorId);
        $bestDoctor->update([
            'doctor_id' => $this->doctor_id,
            'hospital_id' => $this->hospital_id,
            'best_doctor' => $this->best_doctor,
            'best_consultant' => $this->best_consultant,
        ]);

        $this->dispatch('show-toastr', type: 'success', message: 'پزشک برتر با موفقیت ویرایش شد.');

        return redirect()->route('admin.Dashboard.home_page.index');
    }

    public function render()
    {
        return view('livewire.admin.dashboard.home_page.edit-best-doctor', [
            'doctors' => Doctor::all(),
            'hospitals' => Hospital::all(),
        ]);
    }
}