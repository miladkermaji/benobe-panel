<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\Slider\Slide;

class SlideList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedSlides = [];
    public $selectAll = false;
    public $perPage = 100;
    public $slideStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadSlideStatuses();
    }

    public function updatedSearch()
    {
        Log::info('Search Query Updated:', ['search' => $this->search]);
        $this->resetPage();
        $this->loadSlideStatuses();
    }

    public function updatedSelectAll($value)
    {
        Log::info('Select All Updated:', ['value' => $value]);
        if ($value) {
            $this->selectedSlides = Slide::where('title', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedSlides = [];
        }
        Log::info('Selected Slides:', ['selectedSlides' => $this->selectedSlides]);
    }

    public function updatedSelectedSlides()
    {
        Log::info('Selected Slides Updated:', ['selectedSlides' => $this->selectedSlides]);
        $total = Slide::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedSlides) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $slide = Slide::find($id);
        if ($slide) {
            $slide->status = !$slide->status;
            $slide->save();
            $this->slideStatuses[$id] = $slide->status;
            Log::info('Slide status toggled', ['id' => $id, 'status' => $slide->status]);
            $this->dispatch('toast', 'وضعیت اسلایدر با موفقیت تغییر کرد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedSlides)) {
            Log::info('No slides selected for deletion');
            $this->dispatch('toast', 'هیچ اسلایدری انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            $slides = Slide::whereIn('id', $this->selectedSlides)->get();
            foreach ($slides as $slide) {
                if ($slide->image && Storage::disk('public')->exists($slide->image)) {
                    Storage::disk('public')->delete($slide->image);
                }
            }
            Slide::whereIn('id', $this->selectedSlides)->delete();

            $this->selectedSlides = [];
            $this->selectAll = false;
            Log::info('Slides deleted', ['ids' => $this->selectedSlides]);
            $this->dispatch('toast', 'اسلایدرهای انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadSlideStatuses();
        } catch (\Exception $e) {
            Log::error('Error deleting slides:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف اسلایدرها: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadSlideStatuses()
    {
        $slides = Slide::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->get();
        foreach ($slides as $slide) {
            $this->slideStatuses[$slide->id] = $slide->status;
        }
    }

    public function render()
    {
        $slides = Slide::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.admin.content-management.slide-list', [
            'slides' => $slides,
        ]);
    }
}
