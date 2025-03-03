<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\HomeVideo\HomeVideo;

class HomeVideoList extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $selectedVideos = [];
    public $selectAll = false;
    public $perPage = 10;
    public $videoStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadVideoStatuses();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedVideos = HomeVideo::where('title', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedVideos = [];
        }
    }

    public function updatedSelectedVideos()
    {
        $total = HomeVideo::where('title', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedVideos) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $video = HomeVideo::find($id);
        if ($video) {
            $video->approve = !$video->approve;
            $video->save();
            $this->videoStatuses[$id] = $video->approve;
            $this->dispatch('toast', 'وضعیت ویدئو با موفقیت تغییر کرد.', [
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
        if (empty($this->selectedVideos)) {
            $this->dispatch('toast', 'هیچ ویدئویی انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            $videos = HomeVideo::whereIn('id', $this->selectedVideos)->get();
            foreach ($videos as $video) {
                if ($video->image && Storage::disk('public')->exists($video->image)) {
                    Storage::disk('public')->delete($video->image);
                }
                if ($video->video && Storage::disk('public')->exists($video->video)) {
                    Storage::disk('public')->delete($video->video);
                }
            }
            HomeVideo::whereIn('id', $this->selectedVideos)->delete();
            $this->selectedVideos = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'ویدئوهای انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadVideoStatuses();
        } catch (\Exception $e) {
            Log::error('Error deleting videos:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف ویدئوها: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadVideoStatuses()
    {
        $videos = HomeVideo::where('title', 'like', '%' . $this->search . '%')
            ->pluck('approve', 'id')
            ->toArray();
        $this->videoStatuses = array_map('boolval', $videos);
    }

    public function mount()
    {
        $this->loadVideoStatuses();
    }

    public function hydrate()
    {
        $this->loadVideoStatuses();
    }

    public function render()
    {
        $videos = HomeVideo::where('title', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.admin.content-management.home-video-list', [
            'videos' => $videos,
        ]);
    }
}