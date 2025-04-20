<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MyCheck extends Component
{
    public $isChecked;
    public $id;
    public $day;
    public $model;

    public function __construct($isChecked = false, $id, $day, $model = null)
    {
        $this->isChecked = $isChecked;
        $this->id = $id;
        $this->day = $day;
        $this->model = $model;
    }

    public function render()
    {
        return view('components.my-check');
    }
}
