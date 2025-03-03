<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\ContentManagement\Links\Link;
use App\Models\Admin\ContentManagement\Links\LinkCategory;

class LinkCreate extends Component
{
    public $name;
    public $category_id; // مقدار اولیه رو تو mount می‌ذارم
    public $url;
    public $rel = '0';
    public $approve = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:link_categories,id',
        'url' => 'required|url|max:255',
        'rel' => 'nullable|string|max:50',
        'approve' => 'boolean',
    ];

    public function mount()
    {
        // مقدار پیش‌فرض برای category_id (اولین دسته‌بندی)
        $this->category_id = LinkCategory::first()->id ?? null;
    }

    public function save()
    {
        $this->validate();

        try {
            Link::create([
                'name' => $this->name,
                'category_id' => $this->category_id,
                'url' => $this->url,
                'rel' => $this->rel === '0' ? null : $this->rel,
                'approve' => $this->approve,
            ]);

            $this->dispatch('toast', 'پیوند با موفقیت اضافه شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.links.index');
        } catch (\Exception $e) {
            Log::error('Error creating link:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در افزودن پیوند: ' . $e->getMessage(), [
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
        return view('livewire.admin.content-management.link-create', [
            'categories' => $categories,
        ]);
    }
}