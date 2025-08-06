<?php

namespace App\Livewire\Mc\Panel\Layouts\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorSpecialty;
use App\Models\Specialty;
use App\Models\DoctorPermission;
use App\Models\MedicalCenterPermission;

class McSidebar extends Component
{
    public $permissions = [];

    public function mount()
    {
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();

            // دریافت دسترسی‌ها از دیتابیس
            $permissionRecord = MedicalCenterPermission::where('medical_center_id', $medicalCenter->id)->first();
            $this->permissions = $permissionRecord ? ($permissionRecord->permissions ?? []) : [];
        }
    }

    public function hasPermission($permission)
    {
        // اگر مدیر وارد شده باشد، همه دسترسی‌ها را دارد
        if (Auth::guard('manager')->check()) {
            return true;
        }

        // بررسی دسترسی در آرایه permissions
        return in_array($permission, $this->permissions, true);
    }

    public function render()
    {
        $medical_center = null;

        if (Auth::guard('medical_center')->check()) {
            $medical_center = Auth::guard('medical_center')->user();
        }

        return view('livewire.mc.panel.layouts.partials.mc-sidebar', [
            'medical_center' => $medical_center,
        ]);
    }
}
