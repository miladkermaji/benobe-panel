<?php
namespace App\Livewire\Admin\Panel\Layouts\Partials;

use Livewire\Component;
use App\Models\Dr\DoctorWallet;
use Illuminate\Support\Facades\Auth;
use App\Models\Dr\DoctorWalletTransaction;

class HeaderComponent extends Component
{
    public $walletBalance = 0;

    public function mount()
    {
        $doctorId = Auth::guard('manager')->user()->id;
        $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
            ->sum('balance');
    }

    public function render()
    {
        return view('livewire.admin.panel.layouts.partials.header-component');
    }
}