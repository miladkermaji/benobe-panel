<?php

namespace App\Livewire\Admin\Doctors\CommentDoctor;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Doctors\DoctorManagements\DoctorComment;

class AdminDoctorsCommentIndex extends Component
{
    use WithPagination;

    public $search = ''; // برای جستجوی لایو
    public $selectAll = false; // برای انتخاب همه
    public $selectedComments = []; // آرایه برای ذخیره نظرات انتخاب‌شده
    public $perPage = 10; // تعداد ردیف‌ها در هر صفحه
    public $commentItems = []; // برای ذخیره داده‌های خام (بدون صفحه‌بندی)
    public $commentStatuses = []; // برای ذخیره وضعیت‌های نظرات

    public function mount()
    {
        // لود اولیه وضعیت‌ها از دیتابیس
        $this->loadCommentStatuses();
    }

    public function render()
    {
        $query = DoctorComment::with('doctor')
            ->when($this->search, function ($query) {
                $searchTerm = '%' . trim($this->search) . '%';
                $query->where('user_name', 'like', $searchTerm)
                    ->orWhere('user_phone', 'like', $searchTerm)
                    ->orWhere('comment', 'like', $searchTerm);
            })
            ->orderBy('created_at', 'desc');

        // دیباگ: لاگ کردن کوئری برای بررسی
       

        $paginatedComments = $query->paginate($this->perPage);

        // ذخیره داده‌های خام توی پراپرتی $commentItems
        $this->commentItems = $paginatedComments->items();

        // به‌روزرسانی وضعیت‌ها برای رندر
        $this->loadCommentStatuses();

        return view('livewire.admin.doctors.comment-doctor.admin-doctors-comment-index', [
            'comments' => $paginatedComments, // برای صفحه‌بندی و محاسبه ردیف توی Blade
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage(); // ریست کردن صفحه‌بندی هنگام تغییر جستجو
    }

    public function updatedSelectAll()
    {
        $this->selectedComments = $this->selectAll ? collect($this->commentItems)->pluck('id')->toArray() : [];
        $this->dispatch('updateSelectAll', $this->selectAll);
    }

    public function deleteSelectedComments()
    {
        if (empty($this->selectedComments)) {
            $this->dispatch(
                'show-toastr',
                type: 'error',
                message: 'هیچ نظری انتخاب‌شده‌ای وجود ندارد.',
            );
            return;
        }

        DoctorComment::whereIn('id', $this->selectedComments)->delete();
        $this->dispatch(
            'show-toastr',
            type: 'success',
            message: 'نظرات انتخاب‌شده با موفقیت حذف شدند.',
        );
        $this->selectedComments = [];
        $this->selectAll = false;
        $this->loadCommentStatuses(); // به‌روزرسانی وضعیت‌ها بعد از حذف
    }

    public function deleteComment($commentId)
    {
        $comment = DoctorComment::find($commentId);

        if ($comment) {
            $comment->delete();
            $this->dispatch(
                'show-toastr',
                type: 'success',
                message: 'نظر با موفقیت حذف شد.',
            );
            $this->loadCommentStatuses(); // به‌روزرسانی وضعیت‌ها بعد از حذف
        } else {
            $this->dispatch(
                'show-toastr',
                type: 'error',
                message: 'نظر یافت نشد!',
            );
        }
    }

    public function toggleStatus($commentId)
    {
        $comment = DoctorComment::find($commentId);
        if ($comment) {
            $comment->update(['status' => !$comment->status]);
            $this->commentStatuses[$commentId] = $comment->status; // به‌روزرسانی وضعیت توی پراپرتی
            $status = $comment->status ? 'فعال' : 'غیرفعال';
            $this->dispatch(
                'show-toastr',
                type: 'success',
                message: "وضعیت نظر به '{$status}' تغییر یافت.",
            );
        } else {
            $this->dispatch(
                'show-toastr',
                type: 'error',
                message: 'نظر یافت نشد!',
            );
        }
    }

    protected function loadCommentStatuses()
    {
        $this->commentStatuses = DoctorComment::pluck('status', 'id')->toArray();
    }
}