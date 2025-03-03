<?php

namespace App\Livewire\Admin\ContentManagement\Tags;

use Livewire\Component;
use App\Models\Admin\ContentManagement\Tags\Tag;

class TagCreate extends Component
{
    public $name;
    public $usage_count = 0;
    public $status = true;

    protected $rules = [
        'name' => 'required|string|max:255|unique:tags,name',
        'usage_count' => 'nullable|integer|min:0',
        'status' => 'boolean',
    ];

    public function save()
    {
        $this->validate();

        Tag::create([
            'name' => $this->name,
            'usage_count' => $this->usage_count,
            'status' => $this->status,
        ]);

        $this->dispatch('toast', 'تگ با موفقیت ایجاد شد.', [
            'type' => 'success',
            'position' => 'top-right',
            'timeOut' => 3000,
            'progressBar' => true,
        ]);

        return redirect()->route('admin.content-management.tags.index');
    }

    public function render()
    {
        return view('livewire.admin.content-management.tags.tag-create');
    }
}