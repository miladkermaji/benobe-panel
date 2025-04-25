<?php

namespace App\Livewire;

use Livewire\Component;

class EndVisitModal extends Component
{
    public $appointmentId;
    public $endVisitDescription;

    public function mount($appointmentId = null)
    {
        $this->appointmentId = $appointmentId;
    }

    public function render()
    {
        return view('livewire.end-visit-modal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }

    public function endVisit()
    {
        // منطق پایان ویزیت
        $this->dispatch('hideModal');
    }
}
