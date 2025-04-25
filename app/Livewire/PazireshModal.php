<?php

namespace App\Livewire;

use Livewire\Component;

class PazireshModal extends Component
{
    public function render()
    {
        return view('livewire.paziresh-modal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }
}
