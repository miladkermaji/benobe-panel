<?php

namespace App\Livewire\Dr\Panel\DoctorComments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorComment;
use App\Models\Doctor;

class DoctorCommentsList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteDoctorCommentConfirmed' => 'deleteDoctorComment',
        'replySubmitted' => 'saveReply'
    ];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorComments = [];
    public $selectAll = false;
    public $replyText = [];
    public $replyingTo = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $comment = DoctorComment::findOrFail($id);
        $comment->update(['status' => !$comment->status]);
        $this->dispatch('show-alert', type: $comment->status ? 'success' : 'info', message: $comment->status ? 'نظر فعال شد!' : 'نظر غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorComment($id)
    {
        $comment = DoctorComment::findOrFail($id);
        $comment->delete();
        $this->dispatch('show-alert', type: 'success', message: 'نظر با موفقیت حذف شد!');
    }

    public function toggleReply($commentId)
    {
        $this->replyingTo = $this->replyingTo === $commentId ? null : $commentId;
        $this->replyText[$commentId] = $this->replyText[$commentId] ?? '';
    }

    public function saveReply($commentId)
    {
        $comment = DoctorComment::findOrFail($commentId);
        $comment->update(['reply' => $this->replyText[$commentId]]); // Assuming a 'reply' column exists
        $this->replyingTo = null;
        $this->dispatch('show-alert', type: 'success', message: 'پاسخ با موفقیت ثبت شد!');
    }

    public function toggleSelectedStatus()
    {
        if (empty($this->selectedDoctorComments)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ نظری انتخاب نشده است.');
            return;
        }

        $comments = DoctorComment::whereIn('id', $this->selectedDoctorComments)->get();
        foreach ($comments as $comment) {
            $comment->update(['status' => !$comment->status]);
        }
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت نظرات انتخاب‌شده تغییر کرد!');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedDoctorComments)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ نظری انتخاب نشده است.');
            return;
        }

        DoctorComment::whereIn('id', $this->selectedDoctorComments)->delete();
        $this->selectedDoctorComments = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'نظرات انتخاب‌شده حذف شدند!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $comments = $this->getCommentsQuery();
        $commentIds = $comments->pluck('id')->toArray();
        $this->selectedDoctorComments = $value ? $commentIds : [];
    }

    public function updatedSelectedDoctorComments()
    {
        $comments = $this->getCommentsQuery();
        $commentIds = $comments->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedDoctorComments) && count(array_diff($commentIds, $this->selectedDoctorComments)) === 0;
    }

    private function getCommentsQuery()
    {
        return DoctorComment::with('doctor')
            ->where(function ($query) {
                $query->where('user_name', 'like', '%' . $this->search . '%')
                      ->orWhere('comment', 'like', '%' . $this->search . '%')
                      ->orWhereHas('doctor', function ($q) {
                          $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $comments = $this->readyToLoad ? $this->getCommentsQuery() : collect();
        return view('livewire.dr.panel.doctor-comments.doctor-comment-list', [
            'comments' => $comments,
        ]);
    }
}
