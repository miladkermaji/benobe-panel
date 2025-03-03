<?php

namespace App\Livewire\Admin\Doctors\CommentDoctor;

use Livewire\Component;
use App\Models\Admin\Doctors\DoctorManagements\DoctorComment;

class AdminDoctorsCommentShow extends Component
{
    public $commentId;
    public $commentText;
    public $commentStatus;
    public $replyText;

    public function mount($id)
    {
        $comment = DoctorComment::findOrFail($id);
        $this->commentId = $comment->id;
        $this->commentText = $comment->comment;
        $this->commentStatus = $comment->status;
        $this->replyText = $comment->reply ?? '';
    }

    public function render()
    {
        $comment = DoctorComment::with('doctor')->findOrFail($this->commentId);
        return view('livewire.admin.doctors.comment-doctor.admin-doctors-comment-show', [
            'comment' => $comment,
        ]);
    }

    public function updateComment()
    {
        $this->validate([
            'commentText' => 'required|string|max:1000|min:3',
            'commentStatus' => 'required|boolean',
        ], [
            'commentText.required' => 'متن نظر الزامی است.',
            'commentText.max' => 'متن نظر نباید بیشتر از 1000 کاراکتر باشد.',
            'commentText.min' => 'متن نظر باید حداقل 3 کاراکتر باشد.',
            'commentStatus.required' => 'وضعیت نظر الزامی است.',
        ]);

        $comment = DoctorComment::find($this->commentId);
        if ($comment) {
            $comment->update([
                'comment' => $this->commentText,
                'status' => $this->commentStatus,
            ]);
            $this->dispatch(
                'show-toastr',
                type: 'success',
                message: 'نظر با موفقیت به‌روزرسانی شد.',
            );
        } else {
            $this->dispatch(
                'show-toastr',
                type: 'error',
                message: 'نظر یافت نشد!',
            );
        }
    }
    public function addReply()
    {
        $this->validate([
            'replyText' => 'required|string|max:1000|min:3',
        ], [
            'replyText.required' => 'متن پاسخ الزامی است.',
            'replyText.max' => 'متن پاسخ نباید بیشتر از 1000 کاراکتر باشد.',
            'replyText.min' => 'متن پاسخ باید حداقل 3 کاراکتر باشد.',
        ]);

        $comment = DoctorComment::find($this->commentId);
        if ($comment) {
            $comment->update(['reply' => $this->replyText]);
            $this->dispatch(
                'show-toastr',
                type: 'success',
                message: 'پاسخ با موفقیت ثبت شد.',
            );
        } else {
            $this->dispatch(
                'show-toastr',
                type: 'error',
                message: 'نظر یافت نشد!',
            );
        }
    }
}