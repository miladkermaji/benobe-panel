<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\ContentManagement\Links\Link;

class LinkList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedLinks = [];
    public $selectAll = false;
    public $perPage = 10;
    public $linkStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadLinkStatuses();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedLinks = Link::where('name', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedLinks = [];
        }
    }

    public function updatedSelectedLinks()
    {
        $total = Link::where('name', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedLinks) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $link = Link::find($id);
        if ($link) {
            $link->approve = !$link->approve;
            $link->save();
            $this->linkStatuses[$id] = $link->approve;
            $this->dispatch('toast', 'وضعیت پیوند با موفقیت تغییر کرد.', [
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
        if (empty($this->selectedLinks)) {
            $this->dispatch('toast', 'هیچ پیوندی انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            Link::whereIn('id', $this->selectedLinks)->delete();
            $this->selectedLinks = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'پیوندهای انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadLinkStatuses();
        } catch (\Exception $e) {
            Log::error('Error deleting links:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف پیوندها: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadLinkStatuses()
    {
        $links = Link::where('name', 'like', '%' . $this->search . '%')
            ->pluck('approve', 'id')
            ->toArray();
        $this->linkStatuses = array_map('boolval', $links);
    }

    public function mount()
    {
        $this->loadLinkStatuses();
    }

    public function hydrate()
    {
        $this->loadLinkStatuses();
    }

    public function render()
    {
        $links = Link::where('name', 'like', '%' . $this->search . '%')
            ->with('category')
            ->paginate($this->perPage);

        return view('livewire.admin.content-management.link-list', [
            'links' => $links,
        ]);
    }
}