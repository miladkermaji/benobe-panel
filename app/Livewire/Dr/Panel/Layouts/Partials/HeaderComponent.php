<?php
namespace App\Livewire\Dr\Panel\Layouts\Partials;

use Livewire\Component;
use App\Models\Dr\DoctorWallet;
use Illuminate\Support\Facades\Auth;
use App\Models\Dr\DoctorWalletTransaction;

class HeaderComponent extends Component
{
    public $walletBalance = 0;

    public function mount()
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
            ->sum('balance');
    }

    public function render()
    {
        return view('livewire.dr.panel.layouts.partials.header-component');
    }
}