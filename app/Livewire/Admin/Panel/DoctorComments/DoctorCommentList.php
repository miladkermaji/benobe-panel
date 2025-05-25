<?php

namespace App\Livewire\Admin\Panel\DoctorComments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorComment;
use App\Models\Doctor;

class DoctorCommentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteDoctorCommentConfirmed' => 'deleteDoctorComment',
        'replySubmitted' => 'saveReply'
    ];

    public $perPage = 100; // برای پیجینیشن اصلی پزشکان
    public $perPageComments = 5; // برای پیجینیشن نظرات هر پزشک
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorComments = [];
    public $selectAll = [];
    public $expandedDoctors = [];
    public $replyText = [];
    public $replyingTo = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
        $this->perPageComments = max($this->perPageComments, 1);
    }

    public function loadDoctorComments()
    {
        $this->readyToLoad = true;
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
        }
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
        $this->selectAll = [];
        $this->dispatch('show-alert', type: 'success', message: 'نظرات انتخاب‌شده حذف شدند!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->expandedDoctors = []; // بستن همه تاشوها بعد جستجو
    }

    public function updatedSelectAll($value, $doctorId)
    {
        $doctorComments = $this->getDoctorComments($doctorId);
        $commentIds = $doctorComments->pluck('id')->toArray();
        if ($value) {
            $this->selectedDoctorComments = array_unique(array_merge($this->selectedDoctorComments, $commentIds));
        } else {
            $this->selectedDoctorComments = array_diff($this->selectedDoctorComments, $commentIds);
        }
    }

    public function updatedSelectedDoctorComments()
    {
        foreach ($this->doctors as $doctor) {
            $doctorComments = $this->getDoctorComments($doctor->id);
            $commentIds = $doctorComments->pluck('id')->toArray();
            $this->selectAll[$doctor->id] = !empty($this->selectedDoctorComments) &&
                count(array_diff($commentIds, $this->selectedDoctorComments)) === 0;
        }
    }

    public function getDoctorComments($doctorId)
    {
        return DoctorComment::where('doctor_id', $doctorId)
            ->where(function ($query) {
                $query->where('user_name', 'like', '%' . $this->search . '%')
                      ->orWhere('comment', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPageComments, ['*'], "comments_page_{$doctorId}");
    }

    public function getDoctorsProperty()
    {
        return Doctor::where(function ($query) {
            $query->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('comments', function ($q) {
                      $q->where('user_name', 'like', '%' . $this->search . '%')
                        ->orWhere('comment', 'like', '%' . $this->search . '%');
                  });
        })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        $doctors = $this->readyToLoad ? $this->doctors : [];
        foreach ($doctors as $doctor) {
            $doctor->comments = $this->getDoctorComments($doctor->id);
        }
        return view('livewire.admin.panel.doctor-comments.doctor-comment-list', [
            'doctors' => $doctors,
        ]);
    }
}
