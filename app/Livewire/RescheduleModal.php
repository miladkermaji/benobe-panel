<?php

namespace App\Livewire;

use Livewire\Component;

class RescheduleModal extends Component
{
    public $appointmentId;
    public $rescheduleNewDate;

    public function mount($appointmentId = null)
    {
        $this->appointmentId = $appointmentId;
    }

    public function updateAppointmentDate()
    {
        $this->validate([
            'rescheduleNewDate' => 'required|date_format:Y-m-d',
        ]);

        $this->dispatch('updateAppointmentDate', id: $this->appointmentId, date: $this->rescheduleNewDate);
        $this->dispatch('hideModal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }

    public function render()
    {
        return view('livewire.reschedule-modal');
    }
}
