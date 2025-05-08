<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ScheduleCheckBox extends Component
{
    public $id;
    public $day;

    public function __construct($id, $day)
    {
        $this->id = $id;
        $this->day = $day;
    }

    public function render()
    {
        return view('components.schedule-check-box');
    }
}
