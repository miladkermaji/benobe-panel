<?php

namespace App\Livewire;

use Livewire\Component;

class AddSickModal extends Component
{
    public function render()
    {
        return view('livewire.add-sick-modal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }
}
