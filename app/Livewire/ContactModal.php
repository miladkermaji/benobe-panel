<?php

namespace App\Livewire;

use Livewire\Component;

class ContactModal extends Component
{
    public function render()
    {
        return view('livewire.contact-modal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
        $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'تماس امن با موفقیت فعال شد']);
    }
}
