<?php

namespace App\Livewire;

use Livewire\Component;
use Morilog\Jalali\Jalalian;

class BlockMultipleUsersModal extends Component
{
    public $blockedAt;
    public $unblockedAt;
    public $blockReason;

    public function mount()
    {
        $this->blockedAt = Jalalian::now()->format('Y/m/d'); // تنظیم تاریخ جلالی امروز
    }

    public function blockMultipleUsers()
    {
        // دریافت شماره‌های موبایل از چک‌باکس‌های انتخاب‌شده
        $this->dispatch('blockMultipleUsers', [
            'blockedAt' => $this->blockedAt,
            'unblockedAt' => $this->unblockedAt,
            'blockReason' => $this->blockReason,
        ]);
    }

    public function hideModal()
    {
        $this->dispatch('close-modal', id: 'blockMultipleUsersModal');
    }

    public function render()
    {
        return view('livewire.block-multiple-users-modal');
    }
}
