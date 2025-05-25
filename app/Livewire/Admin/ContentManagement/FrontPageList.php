<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\FrontPages\FrontPage;

class FrontPageList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedPages = [];
    public $selectAll = false;
    public $perPage = 100;
    public $pageStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadPageStatuses();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedPages = FrontPage::where('title', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedPages = [];
        }
    }

    public function updatedSelectedPages()
    {
        $total = FrontPage::where('title', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedPages) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $page = FrontPage::find($id);
        if ($page) {
            $page->approve = !$page->approve;
            $page->save();
            $this->pageStatuses[$id] = $page->approve;
            $this->dispatch('toast', 'وضعیت صفحه با موفقیت تغییر کرد.', [
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
        if (empty($this->selectedPages)) {
            $this->dispatch('toast', 'هیچ صفحه‌ای انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            $pages = FrontPage::whereIn('id', $this->selectedPages)->get();
            foreach ($pages as $page) {
                if ($page->image && Storage::disk('public')->exists($page->image)) {
                    Storage::disk('public')->delete($page->image);
                }
            }
            FrontPage::whereIn('id', $this->selectedPages)->delete();
            $this->selectedPages = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'صفحات انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadPageStatuses();
        } catch (\Exception $e) {
            Log::error('Error deleting pages:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف صفحات: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadPageStatuses()
    {
        $pages = FrontPage::where('title', 'like', '%' . $this->search . '%')
            ->pluck('approve', 'id')
            ->toArray();
        $this->pageStatuses = array_map('boolval', $pages);
    }

    public function mount()
    {
        $this->loadPageStatuses();
    }

    public function hydrate()
    {
        $this->loadPageStatuses();
    }

    public function render()
    {
        $pages = FrontPage::where('title', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        foreach ($pages as $page) {
            $page->persian_date = Jalalian::fromDateTime($page->created_at)->format('Y/m/d H:i');
        }

        return view('livewire.admin.content-management.front-page-list', [
            'pages' => $pages,
        ]);
    }
}
