<?php

namespace App\Livewire\Mc\Panel\Services;

use App\Models\Service;
use App\Models\MedicalCenter;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ServiceCreate extends Component
{
    public $selectedServiceIds = [];
    public $availableServices = [];

    protected $rules = [
        'selectedServiceIds' => 'required|array|min:1',
        'selectedServiceIds.*' => 'exists:services,id'
    ];

    protected $messages = [
        'selectedServiceIds.required' => 'لطفاً حداقل یک خدمت را انتخاب کنید.',
        'selectedServiceIds.array' => 'خدمت‌ها باید به صورت آرایه باشند.',
        'selectedServiceIds.min' => 'حداقل یک خدمت باید انتخاب شود.',
        'selectedServiceIds.*.exists' => 'خدمت انتخاب شده معتبر نیست.'
    ];

    public function mount()
    {
        $this->loadAvailableServices();
    }

    public function loadAvailableServices()
    {
        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();
        $currentServiceIds = $medicalCenter->service_ids ?? [];

        $this->availableServices = Service::where('status', 1)
            ->whereNotIn('id', $currentServiceIds)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function store()
    {
        $this->validate();

        /** @var MedicalCenter $medicalCenter */
        $medicalCenter = Auth::guard('medical_center')->user();

        // Get current service IDs
        $currentServiceIds = $medicalCenter->service_ids ?? [];

        // Add new service IDs
        $newServiceIds = array_merge($currentServiceIds, $this->selectedServiceIds);

        // Remove duplicates
        $newServiceIds = array_unique($newServiceIds);

        // Update medical center
        $medicalCenter->update(['service_ids' => array_values($newServiceIds)]);

        $this->dispatch('show-alert', type: 'success', message: 'خدمت‌ها با موفقیت اضافه شدند.');

        return redirect()->route('mc.panel.services.index');
    }

    public function render()
    {
        return view('livewire.mc.panel.services.service-create');
    }
}
