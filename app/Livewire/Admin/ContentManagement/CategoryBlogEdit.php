<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\ContentManagement\Blog\CategoryBlog;

class CategoryBlogEdit extends Component
{
    public $categoryId;
    public $name;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:category_blogs,name,' . $this->categoryId,
        ];
    }

    public function mount($id)
    {
        $this->categoryId = $id;
        $category = CategoryBlog::findOrFail($id);
        $this->name = $category->name;
    }

    public function update()
    {
        $this->validate();

        try {
            $category = CategoryBlog::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->name,
            ]);

            Log::info('Category updated', ['id' => $this->categoryId]);
            $this->dispatch('toast', 'دسته‌بندی با موفقیت ویرایش شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.category-blog.index');
        } catch (\Exception $e) {
            Log::error('Error updating category:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ویرایش دسته‌بندی: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.category-blog-edit');
    }
}