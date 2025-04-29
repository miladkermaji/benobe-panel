<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomAlert extends Component
{
    public string $id;
    public string $type;
    public ?string $title;
    public ?string $message;
    public string $size;
    public bool $show;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $id = null,
        string $type = 'info', // success, error, warning, info
        ?string $title = null,
        ?string $message = null,
        string $size = 'md', // sm, md, lg
        bool $show = false
    ) {
        $this->id = $id ?? 'custom-alert-' . uniqid();
        $this->type = in_array($type, ['success', 'error', 'warning', 'info']) ? $type : 'info';
        $this->title = $title;
        $this->message = $message;
        $this->size = in_array($size, ['sm', 'md', 'lg']) ? $size : 'md';
        $this->show = $show;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.custom-alert');
    }
}
