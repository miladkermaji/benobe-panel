<?php

namespace App\Livewire\Admin\Panel\Reviews;

use App\Models\Review;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteReviewConfirmed' => 'deleteReview'];

    public $perPage         = 10;
    public $search          = '';
    public $selectedReviews = [];
    public $selectAll       = false;

    protected $queryString = ['search' => ['except' => '']];

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
            \Storage::disk('public')->delete($review->image_path);
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

    public function deleteSelected()
    {
        if (empty($this->selectedReviews)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ نظری انتخاب نشده است.');
            return;
        }

        $reviews = Review::whereIn('id', $this->selectedReviews)->get();
        foreach ($reviews as $review) {
            if ($review->image_path) {
                \Storage::disk('public')->delete($review->image_path);
            }
            $review->delete();
        }
        $this->selectedReviews = [];
        $this->selectAll       = false;
        $this->dispatch('show-alert', type: 'success', message: 'نظرات انتخاب‌شده حذف شدند!');
    }

    private function getReviewsQuery()
    {
        return Review::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('comment', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $reviews = $this->getReviewsQuery();
        return view('livewire.admin.panel.reviews.review-list', compact('reviews'));
    }
}
