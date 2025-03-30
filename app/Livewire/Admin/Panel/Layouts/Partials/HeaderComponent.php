<?php

namespace App\Livewire\Admin\Panel\Layouts\Partials;

use App\Models\DoctorWallet;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HeaderComponent extends Component
{
    public $walletBalance = 0;

    public function mount()
    {
        $doctorId            = Auth::guard('manager')->user()->id;
        $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
            ->sum('balance');
    }

    public function render()
    {
        return view('livewire.admin.panel.layouts.partials.header-component');
    }
}
