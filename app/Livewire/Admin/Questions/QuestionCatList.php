<?php

namespace App\Livewire\Admin\Questions;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Question\QuestionCategory;

class QuestionCatList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategories = [];
    public $selectAll = false;
    public $perPage = 10;
    public $categoryStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadCategoryStatuses();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCategories = QuestionCategory::where('name', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedCategories = [];
        }
    }

    public function updatedSelectedCategories()
    {
        $total = QuestionCategory::where('name', 'like', '%' . $this->search . '%')->count();
        $this->selectAll = count($this->selectedCategories) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $category = QuestionCategory::find($id);
        if ($category) {
            $category->approve = !$category->approve;
            $category->save();
            $this->categoryStatuses[$id] = $category->approve;
            $this->dispatch('toast', 'وضعیت دسته‌بندی با موفقیت تغییر کرد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function confirmDeleteSelected()
    {
        // فقط برای فراخوانی از جاوااسکریپت
    }

    public function deleteSelected()
    {
        if (empty($this->selectedCategories)) {
            $this->dispatch('toast', 'هیچ دسته‌بندی انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            QuestionCategory::whereIn('id', $this->selectedCategories)->delete();
            $this->selectedCategories = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'دسته‌بندی‌های انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadCategoryStatuses();
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

    public function loadCategoryStatuses()
    {
        $this->categoryStatuses = QuestionCategory::where('name', 'like', '%' . $this->search . '%')
            ->pluck('approve', 'id')
            ->all();
    }

    public function mount()
    {
        $this->loadCategoryStatuses();
    }

    public function hydrate()
    {
        $this->loadCategoryStatuses();
    }

    public function render()
    {
        $categories = QuestionCategory::where('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.admin.questions.question-cat-list', [
            'categories' => $categories,
        ]);
    }
}