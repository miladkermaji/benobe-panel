<?php

namespace App\Livewire\Mc\Panel\Layouts\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorSpecialty;
use App\Models\Specialty;
use App\Models\DoctorPermission;

class McSidebar extends Component
{
   

    public function render()
    {
        $user = null;

        if (Auth::guard('medical_center')->check()) {
            $medical_center = Auth::guard('medical_center')->user();
        }

        return view('livewire.mc.panel.layouts.partials.mc-sidebar', [
            'medical_center' => $medical_center,
            
        ]);
    }
}
