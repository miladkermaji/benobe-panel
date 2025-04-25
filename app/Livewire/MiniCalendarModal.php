<?php

namespace App\Livewire;

use Livewire\Component;

class MiniCalendarModal extends Component
{
    public function render()
    {
        return view('livewire.mini-calendar-modal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }
}
