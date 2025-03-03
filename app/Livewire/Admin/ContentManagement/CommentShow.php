<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\ContentManagement\Comments\Comment;

class CommentShow extends Component
{
    public $commentId;
    public $name;
    public $email;
    public $ip;
    public $commentText;
    public $approve;
    public $reply; // بدون live، فقط برای ثبت
    public $persianDate;

    public function mount($commentId)
    {
        $this->commentId = $commentId;
        $this->loadComment();
    }

    public function loadComment()
    {
        $comment = Comment::findOrFail($this->commentId);
        $this->name = $comment->name;
        $this->email = $comment->email;
        $this->ip = $comment->ip;
        $this->commentText = $comment->comment;
        $this->approve = (bool) $comment->approve;
        $this->reply = $comment->reply; // مقدار اولیه پاسخ از دیتابیس
        $this->persianDate = \Morilog\Jalali\Jalalian::fromDateTime($comment->created_at)->format('Y/m/d H:i');
    }

    public function updateStatus()
    {
        try {
            $comment = Comment::findOrFail($this->commentId);
            $comment->approve = $this->approve;
            $comment->save();
            $this->dispatch('toast', 'وضعیت نظر با موفقیت به‌روزرسانی شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating comment status:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در به‌روزرسانی وضعیت: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function saveReply()
    {
        if (empty($this->reply)) {
            $this->dispatch('toast', 'پاسخ نمی‌تواند خالی باشد.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            $comment = Comment::findOrFail($this->commentId);
            $comment->reply = $this->reply;
            $comment->save();
            $this->dispatch('toast', 'پاسخ با موفقیت ثبت شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return redirect()->route('admin.content-management.comments.index');
        } catch (\Exception $e) {
            Log::error('Error saving reply:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ثبت پاسخ: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function hydrate()
    {
        $this->loadComment();
    }

    public function render()
    {
        return view('livewire.admin.content-management.comment-show');
    }
}