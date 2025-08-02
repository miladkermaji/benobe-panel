<?php

namespace App\Livewire\Mc\Panel\Layouts\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorSpecialty;
use App\Models\Specialty;
use App\Models\DoctorPermission;

class McSidebar extends Component
{
    public $specialtyName;
    public $permissions = [];

    protected $listeners = ['specialtyUpdated' => 'updateSpecialtyName'];

    public function updateSpecialtyName()
    {
        $this->mount();
    }

    public function mount()
    {
        if (Auth::guard('doctor')->check()) {
            $doctor = Auth::guard('doctor')->user();

            // دریافت تخصص اصلی از جدول doctor_specialty
            $mainSpecialty = DoctorSpecialty::where('doctor_id', $doctor->id)->where('is_main', true)->first();

            if ($mainSpecialty) {
                // دریافت نام تخصص
                $specialty = Specialty::find($mainSpecialty->specialty_id);
                $this->specialtyName = $specialty ? $specialty->name : 'نامشخص';
            } else {
                $this->specialtyName = 'نامشخص';
            }

            // Get permissions from database
            $permissionRecord = DoctorPermission::where('doctor_id', $doctor->id)->first();
            $this->permissions = $permissionRecord ? ($permissionRecord->permissions ?? []) : [];

            // اضافه کردن مجوز نسخه های من
            if (!in_array('my-prescriptions', $this->permissions)) {
                $this->permissions[] = 'my-prescriptions';
            }
        } elseif (Auth::guard('secretary')->check()) {
            $this->specialtyName = 'منشی';
            $secretary = Auth::guard('secretary')->user();
            $permissionRecord = DoctorPermission::where('doctor_id', $secretary->doctor_id)->first();
            $this->permissions = $permissionRecord ? json_decode($permissionRecord->permissions ?? '[]', true) : [];
        }
    }

    public function hasPermission($permission)
    {
        // Get all permissions from config
        $permissionsConfig = config('doctor-permissions');

        // Check if user has the specific permission
        if (in_array($permission, $this->permissions)) {
            return true;
        }

        // If this is a parent permission
        if (isset($permissionsConfig[$permission])) {
            // Check if any child route is enabled
            foreach ($permissionsConfig[$permission]['routes'] ?? [] as $routeKey => $routeTitle) {
                if (in_array($routeKey, $this->permissions)) {
                    return true;
                }
            }
            return false;
        }

        // If this is a child permission (route)
        foreach ($permissionsConfig as $parentKey => $parentData) {
            if (isset($parentData['routes'][$permission])) {
                // Only check this specific route, regardless of parent status
                return in_array($permission, $this->permissions);
            }
        }

        return false;
    }

    public function shouldShowChildMenu($parentKey, $childKey)
    {
        // Only show child menu if user has direct permission for it
        return in_array($childKey, $this->permissions);
    }

    public function render()
    {
        $user = null;

        if (Auth::guard('doctor')->check()) {
            $user = Auth::guard('doctor')->user();
        } elseif (Auth::guard('secretary')->check()) {
            $user = Auth::guard('secretary')->user();
        }

        return view('livewire.mc.panel.layouts.partials.mc-sidebar', [
            'user' => $user,
            'permissions' => $this->permissions
        ]);
    }
}
