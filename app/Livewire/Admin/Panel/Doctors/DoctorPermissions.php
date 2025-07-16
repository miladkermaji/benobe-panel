<?php

namespace App\Livewire\Admin\Panel\Doctors;

use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorPermissions extends Component
{
    use WithPagination;

    public $search = '';
    public $openDoctors = [];
    public $permissionsConfig;
    public $perPage = 50;

    public function mount()
    {
        $this->permissionsConfig = config('doctor-permissions');
        $this->openDoctors = session('openDoctors', []);
    }

    public function toggleDoctorRow($doctorId)
    {
        if (in_array($doctorId, $this->openDoctors)) {
            $this->openDoctors = array_diff($this->openDoctors, [$doctorId]);
        } else {
            $this->openDoctors[] = $doctorId;
        }
        session(['openDoctors' => $this->openDoctors]);
    }

    public function updatePermissions($doctorId, $permissions)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $permissions = is_array($permissions) ? $permissions : json_decode($permissions, true);

        // اگر دسترسی‌ها قبلاً وجود نداشته باشند، یک رکورد جدید ایجاد می‌کنیم
        if (!$doctor->permissions) {
            $doctor->permissions()->create([
                'permissions' => $permissions
            ]);
        } else {
            // در غیر این صورت، دسترسی‌های موجود را به‌روزرسانی می‌کنیم
            $doctor->permissions->update([
                'permissions' => $permissions
            ]);
        }

        $this->dispatch('show-alert', type: 'success', message: 'دسترسی‌ها با موفقیت به‌روزرسانی شدند.');
    }

    public function render()
    {
        $doctors = Doctor::with(['permissions', 'secretaries'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%')
                        ->orWhereHas('secretaries', function ($sq) {
                            $sq->where('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->paginate($this->perPage);

        return view('livewire.admin.panel.doctors.doctor-permissions', [
            'doctors' => $doctors
        ]);
    }
}
