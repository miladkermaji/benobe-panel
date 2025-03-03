<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\ContentManagement\Blog\CategoryBlog;

class CategoryBlogList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategories = [];
    public $selectAll = false;
    public $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        Log::info('Search Query Updated:', ['search' => $this->search]);
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        Log::info('Select All Updated:', ['value' => $value]);
        if ($value) {
            $this->selectedCategories = CategoryBlog::where('name', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedCategories = [];
        }
        Log::info('Selected Categories:', ['selectedCategories' => $this->selectedCategories]);
    }

    public function updatedSelectedCategories()
    {
        Log::info('Selected Categories Updated:', ['selectedCategories' => $this->selectedCategories]);
        $total = CategoryBlog::where('name', 'like', '%' . $this->search . '%')->count();
        $this->selectAll = count($this->selectedCategories) === $total && $total > 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedCategories)) {
            Log::info('No categories selected for deletion');
            $this->dispatch('toast', 'هیچ دسته‌بندی‌ای انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            CategoryBlog::whereIn('id', $this->selectedCategories)->delete();
            $this->selectedCategories = [];
            $this->selectAll = false;
            Log::info('Categories deleted', ['ids' => $this->selectedCategories]);
            $this->dispatch('toast', 'دسته‌بندی‌های انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting categories:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف دسته‌بندی‌ها: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        $categories = CategoryBlog::where('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.admin.content-management.category-blog-list', [
            'categories' => $categories,
        ]);
    }
}