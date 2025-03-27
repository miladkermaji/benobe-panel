<?php

namespace App\Livewire\Dr\Panel\Layouts\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DrSidebar extends Component
{
    public $specialtyName;

    public function mount()
    {
        if (Auth::guard('doctor')->check()) {
            $this->specialtyName = optional(Auth::guard('doctor')->user())->specialty?->title ?? 'نامشخص';
        } elseif (Auth::guard('secretary')->check()) {
            $this->specialtyName = 'منشی'; // Customize as needed
        }
    }

    public function render()
    {
        $user = Auth::guard('doctor')->check() ? Auth::guard('doctor')->user() : Auth::guard('secretary')->user();
        $permissions = [];

        if (Auth::guard('secretary')->check()) {
            $secretaryPermission = Auth::guard('secretary')->user()->permissions;
            $permissions = $secretaryPermission ? $secretaryPermission->permissions : [];
        }

        return view('livewire.dr.panel.layouts.partials.dr-sidebar', compact('user', 'permissions'));
    }
}
