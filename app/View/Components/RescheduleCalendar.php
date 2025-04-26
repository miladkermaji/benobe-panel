<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RescheduleCalendar extends Component
{
    public $appointmentId;

    public function __construct($appointmentId = null)
    {
        $this->appointmentId = $appointmentId;
    }

    public function render(): View|Closure|string
    {
        return view('components.reschedule-calendar');
    }
}
