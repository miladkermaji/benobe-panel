<?php

namespace App\Livewire\Admin\Panel\MedicalCenters;

use App\Models\MedicalCenter;
use Livewire\Component;
use Livewire\WithPagination;

class MedicalCenterPermissions extends Component
{
    use WithPagination;

    public $search = '';
    public $openMedicalCenters = [];
    public $permissionsConfig;
    public $perPage = 50;

    public function mount()
    {
        $this->permissionsConfig = config('medical-center-permissions');
        $this->openMedicalCenters = session('openMedicalCenters', []);
    }

    public function toggleMedicalCenterRow($medicalCenterId)
    {
        if (in_array($medicalCenterId, $this->openMedicalCenters)) {
            $this->openMedicalCenters = array_diff($this->openMedicalCenters, [$medicalCenterId]);
        } else {
            $this->openMedicalCenters[] = $medicalCenterId;
        }
        session(['openMedicalCenters' => $this->openMedicalCenters]);
    }

    public function updatePermissions($medicalCenterId, $permissions)
    {
        $medicalCenter = MedicalCenter::findOrFail($medicalCenterId);
        $permissions = is_array($permissions) ? $permissions : json_decode($permissions, true);

        // اگر دسترسی‌ها قبلاً وجود نداشته باشند، یک رکورد جدید ایجاد می‌کنیم
        if (!$medicalCenter->permissions) {
            $medicalCenter->permissions()->create([
                'permissions' => $permissions
            ]);
        } else {
            // در غیر این صورت، دسترسی‌های موجود را به‌روزرسانی می‌کنیم
            $medicalCenter->permissions->update([
                'permissions' => $permissions
            ]);
        }

        $this->dispatch('show-alert', type: 'success', message: 'دسترسی‌ها با موفقیت به‌روزرسانی شدند.');
    }

    public function render()
    {
        $medicalCenters = MedicalCenter::with('permissions')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%')
                    ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            })
            ->paginate($this->perPage);

        return view('livewire.admin.panel.medical-centers.medical-center-permissions', [
            'medicalCenters' => $medicalCenters
        ]);
    }
}
