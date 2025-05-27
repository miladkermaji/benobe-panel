<?php

namespace App\Livewire\Admin\Panel\Doctors;

use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorPermissions extends Component
{
    use WithPagination;

    public $search = '';
    public $expandedDoctors = [];
    public $permissionsConfig;

    public function mount()
    {
        $this->permissionsConfig = config('doctor-permissions');
        $this->expandedDoctors = session('expandedDoctors', []);
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
        }
        session(['expandedDoctors' => $this->expandedDoctors]);
    }

    public function updatePermissions($doctorId, $permissions)
    {
        $doctor = Doctor::findOrFail($doctorId);

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
            ->paginate(10);

        return view('livewire.admin.panel.doctors.doctor-permissions', [
            'doctors' => $doctors
        ]);
    }
}
