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
        // Check if user has the specific permission
        return in_array($permission, $this->permissions);
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
