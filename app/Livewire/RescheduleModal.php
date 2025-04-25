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

    public function render()
    {
        return view('livewire.reschedule-modal');
    }

    public function hideModal()
    {
        $this->dispatch('hideModal');
    }

    public function updateAppointmentDate()
    {
        // منطق به‌روزرسانی تاریخ نوبت
        $this->dispatch('hideModal');
    }
}
