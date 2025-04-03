<?php

namespace App\Livewire\Admin\Panel\Secretaries;

use Livewire\Component;
use App\Models\Doctor;
use App\Models\Secretary;

class SecretaryList extends Component
{
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

    public function loadSecretaries()
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

    public function toggleStatus($id)
    {
        $secretary = Secretary::findOrFail($id);
        $secretary->update(['is_active' => !$secretary->is_active]);
        $this->dispatch('show-alert', type: $secretary->is_active ? 'success' : 'info', message: $secretary->is_active ? 'منشی فعال شد!' : 'منشی غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteSecretary($id)
    {
        $secretary = Secretary::findOrFail($id);
        $secretary->delete();
        $this->dispatch('show-alert', type: 'success', message: 'منشی با موفقیت حذف شد!');
    }

    public function render()
    {
        $doctors = $this->readyToLoad
            ? Doctor::with(['secretaries' => function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%')
                        ->orWhere('national_code', 'like', '%' . $this->search . '%');
                });
            }])
            ->whereHas('secretaries', function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('mobile', 'like', '%' . $this->search . '%')
                    ->orWhere('national_code', 'like', '%' . $this->search . '%');
            })
            ->orWhere(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('mobile', 'like', '%' . $this->search . '%');
            })
            ->get()
            : [];

        return view('livewire.admin.panel.secretaries.secretary-list', [
            'doctors' => $doctors,
        ]);
    }
}
