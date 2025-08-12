<?php

namespace App\Livewire\Mc\Panel\Specialties;

use App\Models\Specialty;
use App\Models\MedicalCenter;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SpecialtyEdit extends Component
{
    public $specialtyId;
    public $selectedSpecialtyIds = [];
    public $availableSpecialties = [];
    public $currentSpecialty;

    protected $rules = [
        'selectedSpecialtyIds' => 'required|array|min:1',
        'selectedSpecialtyIds.*' => 'exists:specialties,id'
    ];

    protected $messages = [
        'selectedSpecialtyIds.required' => 'لطفاً حداقل یک تخصص را انتخاب کنید.',
        'selectedSpecialtyIds.array' => 'تخصص‌ها باید به صورت آرایه باشند.',
        'selectedSpecialtyIds.min' => 'حداقل یک تخصص باید انتخاب شود.',
        'selectedSpecialtyIds.*.exists' => 'تخصص انتخاب شده معتبر نیست.'
    ];

    public function mount($id)
    {
        $this->specialtyId = $id;
        $this->loadCurrentSpecialty();
        $this->loadAvailableSpecialties();
    }

    public function loadCurrentSpecialty()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentSpecialtyIds = $medicalCenter->specialty_ids ?? [];

        if (in_array($this->specialtyId, $currentSpecialtyIds)) {
            $this->currentSpecialty = Specialty::find($this->specialtyId);
            $this->selectedSpecialtyIds = [$this->specialtyId];
        } else {
            $this->dispatch('show-alert', type: 'error', message: 'این تخصص در مرکز درمانی شما وجود ندارد.');
            return redirect()->route('mc.panel.specialties.index');
        }
    }

    public function loadAvailableSpecialties()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentSpecialtyIds = $medicalCenter->specialty_ids ?? [];

        $this->availableSpecialties = Specialty::where('status', 1)
            ->whereNotIn('id', $currentSpecialtyIds)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function update()
    {
        $this->validate();

        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        // Get current specialty IDs
        $currentSpecialtyIds = $medicalCenter->specialty_ids ?? [];

        // Remove the current specialty ID
        $currentSpecialtyIds = array_diff($currentSpecialtyIds, [$this->specialtyId]);

        // Add new specialty IDs
        $newSpecialtyIds = array_merge($currentSpecialtyIds, $this->selectedSpecialtyIds);

        // Remove duplicates
        $newSpecialtyIds = array_unique($newSpecialtyIds);

        // Update medical center
        $medicalCenter->update(['specialty_ids' => array_values($newSpecialtyIds)]);

        $this->dispatch('show-alert', type: 'success', message: 'تخصص‌ها با موفقیت به‌روزرسانی شدند.');

        return redirect()->route('mc.panel.specialties.index');
    }

    public function render()
    {
        return view('livewire.mc.panel.specialties.specialty-edit');
    }
}
