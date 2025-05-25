<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\ContentManagement\Comments\Comment;

class CommentList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedComments = [];
    public $selectAll = false;
    public $perPage = 100;
    public $commentStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadCommentStatuses();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedComments = Comment::where('comment', 'like', '%' . $this->search . '%')
                ->orWhere('name', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedComments = [];
        }
    }

    public function updatedSelectedComments()
    {
        $total = Comment::where('comment', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedComments) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $comment = Comment::find($id);
        if ($comment) {
            $comment->status = !$comment->status; // تغییر فیلد status، نه approve
            $comment->save();
            $this->commentStatuses[$id] = $comment->status;
            $this->dispatch('toast', 'وضعیت نظر با موفقیت تغییر کرد.', [
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
        if (empty($this->selectedComments)) {
            $this->dispatch('toast', 'هیچ نظری انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            Comment::whereIn('id', $this->selectedComments)->delete();
            $this->selectedComments = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'نظرات انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadCommentStatuses();
        } catch (\Exception $e) {
            Log::error('Error deleting comments:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف نظرات: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadCommentStatuses()
    {
        $comments = Comment::where('comment', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->pluck('status', 'id') // استفاده از فیلد status
            ->toArray();
        $this->commentStatuses = array_map('boolval', $comments);
    }

    public function mount()
    {
        $this->loadCommentStatuses();
    }

    public function hydrate()
    {
        $this->loadCommentStatuses();
    }

    public function render()
    {
        $comments = Comment::where('comment', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        foreach ($comments as $comment) {
            $comment->persian_date = \Morilog\Jalali\Jalalian::fromDateTime($comment->created_at)->format('Y/m/d H:i');
        }

        return view('livewire.admin.content-management.comment-list', [
            'comments' => $comments,
        ]);
    }
}
