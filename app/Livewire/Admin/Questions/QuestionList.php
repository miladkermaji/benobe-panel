<?php

namespace App\Livewire\Admin\Questions;

use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Question\Question;
use App\Models\Admin\Question\QuestionCategory;

class QuestionList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedQuestions = [];
    public $selectAll = false;
    public $perPage = 100;
    public $questionStatuses = [];
    public $categoryFilter = 0;

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadQuestionStatuses();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
        $this->loadQuestionStatuses();
    }

    public function updatedSelectAll($value)
    {
        $query = Question::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('question', 'like', '%' . $this->search . '%');
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }
        $this->selectedQuestions = $value ? $query->pluck('id')->toArray() : [];
    }

    public function updatedSelectedQuestions()
    {
        $query = Question::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('question', 'like', '%' . $this->search . '%');
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }
        $total = $query->count();
        $this->selectAll = count($this->selectedQuestions) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $question = Question::find($id);
        if ($question) {
            $question->approve = !$question->approve;
            $question->save();
            $this->questionStatuses[$id] = $question->approve;
            $this->dispatch('toast', 'وضعیت سوال با موفقیت تغییر کرد.', [
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
        if (empty($this->selectedQuestions)) {
            $this->dispatch('toast', 'هیچ سوالی انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            Question::whereIn('id', $this->selectedQuestions)->delete();
            $this->selectedQuestions = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'سوالات انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadQuestionStatuses();
        } catch (\Exception $e) {
            Log::error('Error deleting questions:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف سوالات: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadQuestionStatuses()
    {
        $query = Question::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('question', 'like', '%' . $this->search . '%');
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }
        $this->questionStatuses = $query->pluck('approve', 'id')->all();
    }

    public function mount()
    {
        $this->loadQuestionStatuses();
    }

    public function hydrate()
    {
        $this->loadQuestionStatuses();
    }

    public function render()
    {
        $query = Question::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('question', 'like', '%' . $this->search . '%');
        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }
        $questions = $query->with('category')->paginate($this->perPage);

        foreach ($questions as $question) {
            $question->persian_date = Jalalian::fromDateTime($question->created_at)->format('Y/m/d H:i');
        }

        $categories = QuestionCategory::all();

        return view('livewire.admin.questions.question-list', [
            'questions' => $questions,
            'categories' => $categories,
        ]);
    }
}
