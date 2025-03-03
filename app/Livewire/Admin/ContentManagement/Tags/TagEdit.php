<?php

namespace App\Livewire\Admin\ContentManagement\Tags;

use Livewire\Component;
use App\Models\Admin\ContentManagement\Tags\Tag;

class TagEdit extends Component
{
    public $tagId;
    public $name;
    public $usage_count;
    public $status;

    protected $rules = [
        'name' => 'required|string|max:255|unique:tags,name,{{ $this->tagId }}',
        'usage_count' => 'nullable|integer|min:0',
        'status' => 'boolean',
    ];

    public function mount($id)
    {
        $this->tagId = $id;
        $tag = Tag::findOrFail($id);
        $this->name = $tag->name;
        $this->usage_count = $tag->usage_count;
        $this->status = $tag->status;
    }

    public function update()
    {
        $this->validate();

        $tag = Tag::findOrFail($this->tagId);
        $tag->update([
            'name' => $this->name,
            'usage_count' => $this->usage_count,
            'status' => $this->status,
        ]);

        $this->dispatch('toast', 'تگ با موفقیت ویرایش شد.', [
            'type' => 'success',
            'position' => 'top-right',
            'timeOut' => 3000,
            'progressBar' => true,
        ]);

        return redirect()->route('admin.content-management.tags.index');
    }

    public function render()
    {
        return view('livewire.admin.content-management.tags.tag-edit');
    }
}