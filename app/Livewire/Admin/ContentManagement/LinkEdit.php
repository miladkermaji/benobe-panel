<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\ContentManagement\Links\Link;
use App\Models\Admin\ContentManagement\Links\LinkCategory;

class LinkEdit extends Component
{
    public $linkId;
    public $name;
    public $category_id;
    public $url;
    public $rel;
    public $approve;

    protected $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:link_categories,id',
        'url' => 'required|url|max:255',
        'rel' => 'nullable|string|max:50',
        'approve' => 'boolean',
    ];

    public function mount($id)
    {
        $this->linkId = $id;
        $link = Link::findOrFail($id);
        $this->name = $link->name;
        $this->category_id = $link->category_id; // مطمئن می‌شیم مقدار درست لود بشه
        $this->url = $link->url;
        $this->rel = $link->rel ?? '0';
        $this->approve = $link->approve;
    }

    public function save()
    {
        $this->validate();

        try {
            $link = Link::findOrFail($this->linkId);
            $link->update([
                'name' => $this->name,
                'category_id' => $this->category_id,
                'url' => $this->url,
                'rel' => $this->rel === '0' ? null : $this->rel,
                'approve' => $this->approve,
            ]);

            $this->dispatch('toast', 'پیوند با موفقیت ویرایش شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.links.index');
        } catch (\Exception $e) {
            Log::error('Error updating link:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ویرایش پیوند: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        $categories = LinkCategory::all();
        return view('livewire.admin.content-management.link-edit', [
            'categories' => $categories,
        ]);
    }
}