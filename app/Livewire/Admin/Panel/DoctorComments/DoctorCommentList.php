<?php

namespace App\Livewire\Admin\Panel\DoctorComments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorComment;
use App\Models\Doctor;
use Illuminate\Support\Facades\Cache;

class DoctorCommentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteDoctorCommentConfirmed' => 'deleteDoctorComment',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed',
        'replySubmitted' => 'saveReply'
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorComments = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $replyText = [];
    public $replyingTo = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => '']
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadDoctorComments()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $comment = DoctorComment::find($id);
        if (!$comment) {
            $this->dispatch('show-alert', type: 'error', message: 'نظر یافت نشد.');
            return;
        }
        $doctorName = $comment->doctor->first_name . ' ' . $comment->doctor->last_name;
        $action = $comment->status ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $doctorName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $comment = DoctorComment::find($id);
        if (!$comment) {
            $this->dispatch('show-alert', type: 'error', message: 'نظر یافت نشد.');
            return;
        }

        $comment->update(['status' => !$comment->status]);

        $this->dispatch('show-alert', type: 'success', message: $comment->status ? 'نظر فعال شد!' : 'نظر غیرفعال شد!');
        Cache::forget('doctor_comments_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorComment($id)
    {
        $comment = DoctorComment::find($id);
        if (!$comment) {
            $this->dispatch('show-alert', type: 'error', message: 'نظر یافت نشد.');
            return;
        }
        $comment->delete();
        $this->dispatch('show-alert', type: 'success', message: 'نظر با موفقیت حذف شد!');
        Cache::forget('doctor_comments_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function toggleReply($commentId)
    {
        $this->replyingTo = $this->replyingTo === $commentId ? null : $commentId;
        $this->replyText[$commentId] = $this->replyText[$commentId] ?? '';
    }

    public function saveReply($commentId)
    {
        $comment = DoctorComment::find($commentId);
        if (!$comment) {
            $this->dispatch('show-alert', type: 'error', message: 'نظر یافت نشد.');
            return;
        }
        $comment->update(['reply' => $this->replyText[$commentId]]);
        $this->replyingTo = null;
        $this->dispatch('show-alert', type: 'success', message: 'پاسخ با موفقیت ثبت شد!');
        Cache::forget('doctor_comments_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = Cache::remember('doctor_comments_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getCommentsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectedDoctorComments = $value ? $currentPageIds : [];
    }

    public function updatedSelectedDoctorComments()
    {
        $currentPageIds = Cache::remember('doctor_comments_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getCommentsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectAll = !empty($this->selectedDoctorComments) && count(array_diff($currentPageIds, $this->selectedDoctorComments)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getCommentsQuery();
            $comments = $query->get();
            foreach ($comments as $comment) {
                $comment->delete();
            }
            $this->selectedDoctorComments = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه نظرات فیلترشده حذف شدند!');
            Cache::forget('doctor_comments_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        if (empty($this->selectedDoctorComments)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ نظری انتخاب نشده است.');
            return;
        }

        $comments = DoctorComment::whereIn('id', $this->selectedDoctorComments)->get();
        foreach ($comments as $comment) {
            $comment->delete();
        }
        $this->selectedDoctorComments = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'نظرات انتخاب‌شده حذف شدند!');
        Cache::forget('doctor_comments_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedDoctorComments) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ نظری انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getCommentsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'activate':
                    $query->update(['status' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه نظرات فیلترشده فعال شدند!');
                    break;
                case 'deactivate':
                    $query->update(['status' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه نظرات فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedDoctorComments = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('doctor_comments_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'activate':
                $this->updateStatus(true);
                break;
            case 'deactivate':
                $this->updateStatus(false);
                break;
        }

        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        $comments = DoctorComment::whereIn('id', $this->selectedDoctorComments)->get();
        foreach ($comments as $comment) {
            $comment->update(['status' => $status]);
        }

        $this->selectedDoctorComments = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت نظرات انتخاب‌شده با موفقیت تغییر کرد.');
        Cache::forget('doctor_comments_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    private function getCommentsQuery()
    {
        $query = DoctorComment::with(['doctor'])
            ->whereHas('doctor', function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
            })
            ->orWhere(function ($q) {
                $q->where('user_name', 'like', '%' . $this->search . '%')
                  ->orWhere('comment', 'like', '%' . $this->search . '%');
            });

        if ($this->statusFilter === 'active') {
            $query->where('status', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('status', false);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $cacheKey = 'doctor_comments_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage();
        $comments = $this->readyToLoad ? Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getCommentsQuery()->paginate($this->perPage);
        }) : [];
        $this->totalFilteredCount = $this->readyToLoad ? $this->getCommentsQuery()->count() : 0;

        return view('livewire.admin.panel.doctor-comments.doctor-comment-list', [
            'comments' => $comments,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
