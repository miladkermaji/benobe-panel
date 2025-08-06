<?php

namespace App\Livewire\Mc\Panel\Insurances;

use App\Models\Insurance;
use App\Models\MedicalCenter;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class InsuranceEdit extends Component
{
    public $insuranceId;
    public $selectedInsuranceIds = [];
    public $availableInsurances = [];
    public $currentInsurance;

    protected $rules = [
        'selectedInsuranceIds' => 'required|array|min:1',
        'selectedInsuranceIds.*' => 'exists:insurances,id'
    ];

    protected $messages = [
        'selectedInsuranceIds.required' => 'لطفاً حداقل یک بیمه را انتخاب کنید.',
        'selectedInsuranceIds.array' => 'بیمه‌ها باید به صورت آرایه باشند.',
        'selectedInsuranceIds.min' => 'حداقل یک بیمه باید انتخاب شود.',
        'selectedInsuranceIds.*.exists' => 'بیمه انتخاب شده معتبر نیست.'
    ];

    public function mount($id)
    {
        $this->insuranceId = $id;
        $this->loadCurrentInsurance();
        $this->loadAvailableInsurances();
    }

    public function loadCurrentInsurance()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];

        if (in_array($this->insuranceId, $currentInsuranceIds)) {
            $this->currentInsurance = Insurance::find($this->insuranceId);
            $this->selectedInsuranceIds = [$this->insuranceId];
        } else {
            $this->dispatch('show-alert', type: 'error', message: 'این بیمه در مرکز درمانی شما وجود ندارد.');
            return redirect()->route('mc.panel.insurances.index');
        }
    }

    public function loadAvailableInsurances()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];

        $this->availableInsurances = Insurance::where('status', 1)
            ->whereNotIn('id', $currentInsuranceIds)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function update()
    {
        $this->validate();

        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        // Get current insurance IDs
        $currentInsuranceIds = $medicalCenter->insurance_ids ?? [];

        // Remove the current insurance ID
        $currentInsuranceIds = array_diff($currentInsuranceIds, [$this->insuranceId]);

        // Add new insurance IDs
        $newInsuranceIds = array_merge($currentInsuranceIds, $this->selectedInsuranceIds);

        // Remove duplicates
        $newInsuranceIds = array_unique($newInsuranceIds);

        // Update medical center
        $medicalCenter->update(['insurance_ids' => array_values($newInsuranceIds)]);

        $this->dispatch('show-alert', type: 'success', message: 'بیمه‌ها با موفقیت به‌روزرسانی شدند.');

        return redirect()->route('mc.panel.insurances.index');
    }

    public function render()
    {
        return view('livewire.mc.panel.insurances.insurance-edit');
    }
}
