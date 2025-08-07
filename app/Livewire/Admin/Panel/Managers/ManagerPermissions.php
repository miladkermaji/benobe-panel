<?php

namespace App\Livewire\Admin\Panel\Managers;

use Livewire\Component;
use App\Models\Manager;
use App\Models\ManagerPermission;
use Livewire\WithPagination;

class ManagerPermissions extends Component
{
    use WithPagination;

    public $search = '';
    public $openManagers = [];
    public $permissionsConfig;
    public $perPage = 50;

    public function mount()
    {
        $this->permissionsConfig = config('admin-permissions');
        $this->openManagers = session('openManagers', []);
    }

    public function toggleManagerRow($managerId)
    {
        if (in_array($managerId, $this->openManagers)) {
            $this->openManagers = array_diff($this->openManagers, [$managerId]);
        } else {
            $this->openManagers[] = $managerId;
        }
        session(['openManagers' => $this->openManagers]);
    }

    public function updatePermissions($managerId, $permissions)
    {
        $manager = Manager::findOrFail($managerId);
        $permissions = is_array($permissions) ? $permissions : json_decode($permissions, true);

        if (!$manager->permissions) {
            $manager->permissions()->create([
                'permissions' => $permissions
            ]);
        } else {
            $manager->permissions->update([
                'permissions' => $permissions
            ]);
        }

        $this->dispatch('show-alert', type: 'success', message: 'دسترسی‌ها با موفقیت به‌روزرسانی شدند.');
    }

    public function render()
    {
        $currentManager = auth('manager')->user();
        $managers = Manager::with(['permissions'])
            ->when($currentManager && $currentManager->permission_level == 1, function ($query) use ($currentManager) {
                $query->where('permission_level', 2)
                      ->where('id', '!=', $currentManager->id);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%');
                });
            })
            ->paginate($this->perPage);

        return view('livewire.admin.panel.managers.manager-permissions', [
            'managers' => $managers
        ]);
    }
}
