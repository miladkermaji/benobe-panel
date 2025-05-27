<?php

namespace App\Livewire\Dr\Panel\Layouts\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DrSidebar extends Component
{
    public $specialtyName;
    public $permissions = [];

    public function mount()
    {
        if (Auth::guard('doctor')->check()) {
            $doctor = Auth::guard('doctor')->user();
            $this->specialtyName = optional($doctor)->specialty?->name ?? 'نامشخص';
            // Get permissions from database
            $this->permissions = $doctor->permissions ? $doctor->permissions->permissions : [];
        } elseif (Auth::guard('secretary')->check()) {
            $this->specialtyName = 'منشی';
            $secretary = Auth::guard('secretary')->user();
            $this->permissions = $secretary->permissions ? $secretary->permissions->permissions : [];
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

        // If this is a parent permission, check if any of its children are enabled
        if (isset($permissionsConfig[$permission])) {
            // Check if any child route is enabled
            foreach ($permissionsConfig[$permission]['routes'] ?? [] as $routeKey => $routeTitle) {
                if (in_array($routeKey, $this->permissions)) {
                    return true;
                }
            }
        }

        // If this is a child permission, check if its parent is enabled
        foreach ($permissionsConfig as $parentKey => $parentData) {
            if (isset($parentData['routes'][$permission]) && in_array($parentKey, $this->permissions)) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        $user = Auth::guard('doctor')->check() ? Auth::guard('doctor')->user() : Auth::guard('secretary')->user();

        return view('livewire.dr.panel.layouts.partials.dr-sidebar', [
            'user' => $user,
            'permissions' => $this->permissions
        ]);
    }
}
