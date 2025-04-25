<?php

namespace App\Livewire;

use Livewire\Component;
use Morilog\Jalali\Jalalian;

class BlockUserModal extends Component
{
    public $appointmentId;
    public $blockedAt;
    public $unblockedAt;
    public $blockReason;

    public function mount($appointmentId = null)
    {
        $this->appointmentId = $appointmentId;
        $this->blockedAt = Jalalian::now()->format('Y/m/d'); // تنظیم تاریخ جلالی امروز
    }

    public function blockUser()
    {
        $this->dispatch('blockUser', [
            'appointmentId' => $this->appointmentId,
            'blockedAt' => $this->blockedAt,
            'unblockedAt' => $this->unblockedAt,
            'blockReason' => $this->blockReason,
        ]);
    }

    public function hideModal()
    {
        $this->dispatch('close-modal', id: 'blockUserModal');
    }

    public function render()
    {
        return view('livewire.block-user-modal');
    }
}
