<?php

namespace App\Livewire\Admin\Panel\ClinicDepositSettings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Doctor;
use App\Models\ClinicDepositSetting;

class ClinicDepositSettingsList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $readyToLoad = false;
    public $perPage = 100;
    public $expandedDoctors = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = ['deleteClinicDepositSettingConfirmed' => 'deleteClinicDepositSetting'];

    public function mount()
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

    public function deleteClinicDepositSetting($id)
    {
        $item = ClinicDepositSetting::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تنظیم بیعانه حذف شد!');
    }

    public function toggleStatus($id)
    {
        $item = ClinicDepositSetting::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت تنظیم بیعانه تغییر کرد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $doctors = $this->readyToLoad ? Doctor::where(function ($query) {
            $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
                  ->orWhere('mobile', 'like', '%' . $this->search . '%');
        })->with(['depositSettings' => function ($query) {
            $query->with('clinic');
        }])->paginate($this->perPage) : collect();

        return view('livewire.admin.panel.clinic-deposit-settings.clinic-deposit-setting-list', [
            'doctors' => $doctors,
        ]);
    }
}
