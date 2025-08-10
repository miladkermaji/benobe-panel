<?php

namespace App\Livewire\Admin\Panel\Stories;

use App\Models\Story;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StoryList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $storyStatus = [];
    protected $listeners = [
        'deleteStoryConfirmed' => 'deleteStory',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed',
        'approveStoryConfirmed' => 'approveStory',
        'rejectStoryConfirmed' => 'rejectStory',
    ];

    public $perPage = 20;
    public $search = '';
    public $readyToLoad = false;
    public $selectedStories = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $ownerTypeFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'ownerTypeFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
        $this->storyStatus = [];
        if ($this->readyToLoad) {
            $stories = $this->getStoriesQuery()->paginate($this->perPage);
            foreach ($stories as $story) {
                $this->storyStatus[$story->id] = $story->status === 'active';
            }
        }
    }

    public function loadStories()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $story = Story::find($id);
        if (!$story) {
            $this->dispatch('show-alert', type: 'error', message: 'استوری یافت نشد.');
            return;
        }

        $action = $story->status === 'active' ? 'غیرفعال کردن' : 'فعال کردن';
        $this->dispatch('confirm-toggle-status', id: $id, name: $story->title, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $story = Story::find($id);
        if (!$story) {
            $this->dispatch('show-alert', type: 'error', message: 'استوری یافت نشد.');
            return;
        }

        $newStatus = $story->status === 'active' ? 'inactive' : 'active';
        $story->update(['status' => $newStatus]);

        $this->dispatch('show-alert', type: 'success', message: $newStatus === 'active' ? 'استوری فعال شد!' : 'استوری غیرفعال شد!');
        Cache::forget('stories_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }



    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteStory($id)
    {
        $story = Story::findOrFail($id);

        // حذف فایل‌های مرتبط
        if ($story->media_path && Storage::exists($story->media_path)) {
            Storage::delete($story->media_path);
        }

        if ($story->thumbnail_path && Storage::exists($story->thumbnail_path)) {
            Storage::delete($story->thumbnail_path);
        }

        $story->delete();
        $this->dispatch('show-alert', type: 'success', message: 'استوری با موفقیت حذف شد!');
        Cache::forget('stories_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedOwnerTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getStoriesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedStories = $value ? $currentPageIds : [];
    }

    public function updatedSelectedStories()
    {
        $currentPageIds = $this->getStoriesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedStories) && count(array_diff($currentPageIds, $this->selectedStories)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getStoriesQuery();
            $stories = $query->get();

            foreach ($stories as $story) {
                if ($story->media_path && Storage::exists($story->media_path)) {
                    Storage::delete($story->media_path);
                }
                if ($story->thumbnail_path && Storage::exists($story->thumbnail_path)) {
                    Storage::delete($story->thumbnail_path);
                }
            }

            $query->delete();
            $this->selectedStories = [];
            $this->selectAll = false;
            $this->dispatch('show-alert', type: 'success', message: 'تمام استوری‌های فیلتر شده حذف شدند!');
        } else {
            $stories = Story::whereIn('id', $this->selectedStories)->get();

            foreach ($stories as $story) {
                if ($story->media_path && Storage::exists($story->media_path)) {
                    Storage::delete($story->media_path);
                }
                if ($story->thumbnail_path && Storage::exists($story->thumbnail_path)) {
                    Storage::delete($story->thumbnail_path);
                }
            }

            Story::whereIn('id', $this->selectedStories)->delete();
            $this->selectedStories = [];
            $this->selectAll = false;
            $this->dispatch('show-alert', type: 'success', message: 'استوری‌های انتخاب شده حذف شدند!');
        }

        Cache::forget('stories_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'error', message: 'لطفاً یک عملیات انتخاب کنید.');
            return;
        }

        if (empty($this->selectedStories) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'error', message: 'لطفاً حداقل یک استوری انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'activate':
                $this->updateStatus('active');
                break;
            case 'deactivate':
                $this->updateStatus('inactive');
                break;
            case 'approve':
                $this->updateStatus('active');
                break;
            case 'reject':
                $this->updateStatus('inactive');
                break;
            case 'delete':
                $this->dispatch('confirm-delete-selected', allFiltered: $this->applyToAllFiltered ? 'allFiltered' : null);
                break;
            default:
                $this->dispatch('show-alert', type: 'error', message: 'عملیات نامعتبر.');
        }
    }

    private function updateStatus($status)
    {
        if ($this->applyToAllFiltered) {
            $query = $this->getStoriesQuery();
            $count = $query->count();
            $query->update(['status' => $status]);
            $this->dispatch('show-alert', type: 'success', message: "وضعیت {$count} استوری تغییر کرد!");
        } else {
            $count = count($this->selectedStories);
            Story::whereIn('id', $this->selectedStories)->update(['status' => $status]);
            $this->dispatch('show-alert', type: 'success', message: "وضعیت {$count} استوری تغییر کرد!");
        }

        $this->selectedStories = [];
        $this->selectAll = false;
        $this->groupAction = '';
        Cache::forget('stories_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    protected function getStoriesQuery()
    {
        $query = Story::query()
            ->with(['user', 'doctor', 'medicalCenter', 'manager'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            if (in_array($this->statusFilter, ['active', 'inactive', 'pending'])) {
                $query->where('status', $this->statusFilter);
            } elseif (in_array($this->statusFilter, ['image', 'video'])) {
                $query->where('type', $this->statusFilter);
            }
        }

        if ($this->ownerTypeFilter) {
            switch ($this->ownerTypeFilter) {
                case 'user':
                    $query->whereNotNull('user_id');
                    break;
                case 'doctor':
                    $query->whereNotNull('doctor_id');
                    break;
                case 'medical_center':
                    $query->whereNotNull('medical_center_id');
                    break;
                case 'manager':
                    $query->whereNotNull('manager_id');
                    break;
            }
        }

        return $query;
    }
    public function toggleStatus($id)
    {
        $story = Story::find($id);
        if (!$story) {
            $this->dispatch('show-alert', type: 'error', message: 'استوری یافت نشد.');
            return;
        }

        $newStatus = $this->storyStatus[$id] ? 'active' : 'inactive';
        $story->update(['status' => $newStatus]);

        $this->dispatch('show-alert', type: 'success', message: $newStatus === 'active' ? 'استوری فعال شد!' : 'استوری غیرفعال شد!');
        Cache::forget('stories_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }
    public function render()
    {
        if ($this->readyToLoad) {
            $stories = $this->getStoriesQuery()->paginate($this->perPage);
            $this->totalFilteredCount = $this->getStoriesQuery()->count();
            foreach ($stories as $story) {
                $this->storyStatus[$story->id] = $story->status === 'active';
            }
        } else {
            $stories = collect();
            $this->totalFilteredCount = 0;
        }

        return view('livewire.admin.panel.stories.story-list', [
            'stories' => $stories,
        ]);
    }
}
