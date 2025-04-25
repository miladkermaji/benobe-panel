<?php

namespace App\Livewire;

use Livewire\Component;

class BlockMultipleUsersModal extends Component
{
    public $blockedAt;
    public $unblockedAt;
    public $blockReason;

    public function render()
    {
        return view('livewire.block-multiple-users-modal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }

    public function blockMultipleUsers()
    {
        // منطق مسدود کردن گروهی کاربران
        $this->dispatch('hideModal');
    }
}
