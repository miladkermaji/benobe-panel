<?php

namespace App\Livewire;

use Livewire\Component;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;

class BlockMultipleUsersModal extends Component
{
    public $blockedAt;
    public $unblockedAt;
    public $blockReason;
    public $mobiles = [];

    public function mount($mobiles = [])
    {
        $this->blockedAt = Jalalian::now()->format('Y/m/d');
        $this->mobiles = $mobiles;
    }

    public function blockMultipleUsers()
    {



        $data = [
            'blockedAt' => $this->blockedAt,
            'unblockedAt' => $this->unblockedAt,
            'blockReason' => $this->blockReason,
            'selectedMobiles' => $this->mobiles,
        ];


        $this->dispatch('block-multiple-users', $data);
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }

    public function render()
    {
        return view('livewire.block-multiple-users-modal');
    }
}
