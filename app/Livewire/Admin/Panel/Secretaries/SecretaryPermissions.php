<?php

namespace App\Livewire\Admin\Panel\Secretaries;

use App\Models\Doctor;
use Livewire\Component;
use App\Models\Secretary;
use App\Models\SecretaryPermission;
use Illuminate\Support\Facades\Auth;

class SecretaryPermissions extends Component
{
    public $expandedSecretaries = [];
    public $search = '';
    public $permissionsConfig;

    public function mount()
    {
        // چک کردن دسترسی مدیر
        if (!Auth::guard('manager')->check()) {
            $this->redirectRoute('manager.login');
            return;
        }

        $this->permissionsConfig = config('permissions');
        $this->expandedSecretaries = session('expandedSecretaries', []);
    }

    public function toggleSecretary($secretaryId)
    {
        if (in_array($secretaryId, $this->expandedSecretaries)) {
            $this->expandedSecretaries = array_diff($this->expandedSecretaries, [$secretaryId]);
        } else {
            $this->expandedSecretaries[] = $secretaryId;
        }
        session(['expandedSecretaries' => $this->expandedSecretaries]);
    }

    public function updatePermissions($secretaryId, $permissions, $clinicId = null)
    {
        // چون پنل مدیریته، فرض می‌کنیم مدیر می‌تونه برای هر دکتری مجوز آپدیت کنه
        $secretary = Secretary::findOrFail($secretaryId);
        $doctorId = $secretary->doctor_id;

        $permission = SecretaryPermission::where('doctor_id', $doctorId)
            ->where('secretary_id', $secretaryId)
            ->where(function ($query) use ($clinicId) {
                if ($clinicId !== null && $clinicId !== 'null') {
                    $query->where('clinic_id', $clinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })->first();

        if ($permission) {
            $permission->update([
                'permissions' => json_encode($permissions),
                'has_access' => !empty($permissions),
            ]);
        } else {
            SecretaryPermission::create([
                'doctor_id' => $doctorId,
                'secretary_id' => $secretaryId,
                'clinic_id' => $clinicId === 'null' ? null : $clinicId,
                'permissions' => json_encode($permissions),
                'has_access' => !empty($permissions),
            ]);
        }

        $this->dispatch('show-alert', type: 'success', message: 'دسترسی‌های منشی با موفقیت به‌روزرسانی شد.');
    }

    public function render()
    {
        // چک کردن دسترسی مدیر
        if (!Auth::guard('manager')->check()) {
            $this->redirectRoute('manager.login');
            return view('livewire.admin.panel.secretaries.secretary-permissions', ['doctors' => collect()]);
        }

        $doctors = Doctor::with(['secretaries.permissions', 'secretaries.clinic'])
            ->whereHas('secretaries', function ($query) {
                if (str_contains($this->search, ' ')) {
                    [$firstName, $lastName] = explode(' ', $this->search, 2);
                    $query->where('first_name', 'like', '%' . $firstName . '%')
                          ->where('last_name', 'like', '%' . $lastName . '%');
                } else {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('mobile', 'like', '%' . $this->search . '%')
                          ->orWhereHas('clinic', function ($query) {
                              $query->where('name', 'like', '%' . $this->search . '%');
                          });
                }
            })
            ->get();

        return view('livewire.admin.panel.secretaries.secretary-permissions', [
            'doctors' => $doctors,
        ]);
    }
}
