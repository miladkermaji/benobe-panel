<?php

namespace App\Livewire;

use Livewire\Component;

class BlockUserModal extends Component
{
    public $appointmentId;
    public $blockedAt;
    public $unblockedAt;
    public $blockReason;

    public function mount($appointmentId = null)
    {
        $this->appointmentId = $appointmentId;
    }

    public function render()
    {
        return view('livewire.block-user-modal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }

    public function blockUser()
    {
        // منطق مسدود کردن کاربر
        $this->dispatch('hideModal');
    }
}
