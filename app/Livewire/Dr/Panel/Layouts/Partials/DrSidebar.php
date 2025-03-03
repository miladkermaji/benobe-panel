<?php
namespace App\Livewire\Dr\Panel\Layouts\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DrSidebar extends Component
{
    public $specialtyName;

    public function mount()
    {
        $this->specialtyName = optional(Auth::guard('doctor')->user())->specialty?->title ?? 'نامشخص';
    }

    public function render()
    {
        $user = Auth::guard('doctor')->check() ? Auth::guard('doctor')->user() : Auth::guard('secretary')->user();
       
        return view('livewire.dr.panel.layouts.partials.dr-sidebar', compact('user'));
    }
}