<?php

namespace App\Livewire\Admin\Panel\Reviews;

use App\Models\Review;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class ReviewList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteReviewConfirmed' => 'deleteReview'];

    public $perPage         = 100;
    public $search          = '';
    public $selectedReviews = [];
    public $selectAll       = false;
    public $readyToLoad     = false;
    public $statusFilter = '';
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function loadReviews()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => ! $review->is_approved]);
        $this->dispatch('show-alert', type: $review->is_approved ? 'success' : 'info', message: $review->is_approved ? 'نظر تأیید شد!' : 'نظر از تأیید خارج شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);
        if ($review->image_path) {
            Storage::disk('public')->delete($review->image_path);
        }
        $review->delete();
        $this->dispatch('show-alert', type: 'success', message: 'نظر با موفقیت حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds        = $this->getReviewsQuery()->pluck('id')->toArray();
        $this->selectedReviews = $value ? $currentPageIds : [];
    }

    public function updatedSelectedReviews()
    {
        $currentPageIds  = $this->getReviewsQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedReviews) && count(array_diff($currentPageIds, $this->selectedReviews)) === 0;
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedGroupAction()
    {
        // Optional: reset selection or handle UI
    }

    public function executeGroupAction()
    {
        if ($this->groupAction === 'delete') {
            if ($this->applyToAllFiltered) {
                $query = $this->getReviewsQuery(false);
                foreach ($query->get() as $review) {
                    if ($review->image_path) {
                        \Storage::disk('public')->delete($review->image_path);
                    }
                    $review->delete();
                }
                $this->selectedReviews = [];
                $this->selectAll = false;
                $this->applyToAllFiltered = false;
                $this->groupAction = '';
                $this->resetPage();
                $this->dispatch('show-alert', type: 'success', message: 'همه نظرات فیلترشده حذف شدند!');
                return;
            }
            if (empty($this->selectedReviews)) {
                return;
            }
            $reviews = \App\Models\Review::whereIn('id', $this->selectedReviews)->get();
            foreach ($reviews as $review) {
                if ($review->image_path) {
                    \Storage::disk('public')->delete($review->image_path);
                }
                $review->delete();
            }
            $this->selectedReviews = [];
            $this->selectAll = false;
            $this->dispatch('show-alert', type: 'success', message: 'نظرات انتخاب شده با موفقیت حذف شدند!');
        } elseif ($this->groupAction === 'status_active') {
            $query = $this->applyToAllFiltered ? $this->getReviewsQuery(false) : \App\Models\Review::whereIn('id', $this->selectedReviews);
            $query->update(['is_approved' => true]);
            $this->dispatch('show-alert', type: 'success', message: 'نظرات تأیید شدند!');
        } elseif ($this->groupAction === 'status_inactive') {
            $query = $this->applyToAllFiltered ? $this->getReviewsQuery(false) : \App\Models\Review::whereIn('id', $this->selectedReviews);
            $query->update(['is_approved' => false]);
            $this->dispatch('show-alert', type: 'info', message: 'نظرات از تأیید خارج شدند!');
        }
        $this->groupAction = '';
        $this->applyToAllFiltered = false;
        $this->selectedReviews = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function updatedApplyToAllFiltered($value)
    {
        // No-op, just for UI
    }

    public function deleteSelected()
    {
        if (empty($this->selectedReviews)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ نظری انتخاب نشده است.');
            return;
        }

        $reviews = Review::whereIn('id', $this->selectedReviews)->get();
        foreach ($reviews as $review) {
            if ($review->image_path) {
                Storage::disk('public')->delete($review->image_path);
            }
            $review->delete();
        }
        $this->selectedReviews = [];
        $this->selectAll       = false;
        $this->dispatch('show-alert', type: 'success', message: 'نظرات انتخاب‌شده حذف شدند!');
    }

    private function getReviewsQuery($paginate = true)
    {
        $query = \App\Models\Review::query();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('comment', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->statusFilter === 'active') {
            $query->where('is_approved', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_approved', false);
        }
        $query->orderBy('created_at', 'desc');
        return $paginate ? $query->paginate($this->perPage) : $query;
    }

    public function render()
    {
        $reviews = $this->getReviewsQuery();
        $this->totalFilteredCount = $this->getReviewsQuery(false)->count();
        return view('livewire.admin.panel.reviews.review-list', [
            'reviews' => $reviews,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
