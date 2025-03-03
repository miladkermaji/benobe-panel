<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\ContentManagement\Blog\CategoryBlog;

class CategoryBlogCreate extends Component
{
    public $name;

    protected $rules = [
        'name' => 'required|string|max:255|unique:category_blogs,name',
    ];

    public function save()
    {
        $this->validate();

        try {
            CategoryBlog::create([
                'name' => $this->name,
            ]);

            Log::info('New category created', ['name' => $this->name]);
            $this->dispatch('toast', 'دسته‌بندی با موفقیت اضافه شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            $this->reset();
            return redirect()->route('admin.content-management.category-blog.index');
        } catch (\Exception $e) {
            Log::error('Error creating category:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در افزودن دسته‌بندی: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.category-blog-create');
    }
}