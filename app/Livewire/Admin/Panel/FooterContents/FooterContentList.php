<?php

namespace App\Livewire\Admin\Panel\FooterContents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FooterContent;
use Illuminate\Support\FacadesStorage;
use Illuminate\Support\Facades\Storage;

class FooterContentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteFooterContentConfirmed' => 'deleteFooterContent'];

    public $perPage = 100;
    public $search = '';
    public $selectedFooterContents = [];
    public $selectAll = false;
    public $readyToLoad = false;
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $statusFilter = '';
    public $totalFilteredCount = 0;

    protected $queryString = ['search' => ['except' => '']];

    public function loadFooterContents()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $footerContent = FooterContent::findOrFail($id);
        $footerContent->update(['is_active' => !$footerContent->is_active]);
        $this->dispatch('show-alert', type: $footerContent->is_active ? 'success' : 'info', message: $footerContent->is_active ? 'آیتم فعال شد!' : 'آیتم غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteFooterContent($id)
    {
        $footerContent = FooterContent::findOrFail($id);
        if ($footerContent->icon_path) {
            Storage::disk('public')->delete($footerContent->icon_path);
        }
        if ($footerContent->image_path) {
            Storage::disk('public')->delete($footerContent->image_path);
        }
        $footerContent->delete();
        $this->dispatch('show-alert', type: 'success', message: 'آیتم فوتر با موفقیت حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getFooterContentsQuery()->pluck('id')->toArray();
        $this->selectedFooterContents = $value ? $currentPageIds : [];
    }

    public function updatedSelectedFooterContents()
    {
        $currentPageIds = $this->getFooterContentsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedFooterContents) && count(array_diff($currentPageIds, $this->selectedFooterContents)) === 0;
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
            $this->confirmDeleteSelected();
        } elseif ($this->groupAction === 'status_active') {
            $this->updateSelectedStatus(true);
        } elseif ($this->groupAction === 'status_inactive') {
            $this->updateSelectedStatus(false);
        }
    }

    public function confirmDeleteSelected()
    {
        if (empty($this->selectedFooterContents) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ آیتمی انتخاب نشده است.');
            return;
        }
        $this->dispatch('confirm-delete-selected');
    }

    public function deleteSelectedConfirmed()
    {
        $ids = $this->applyToAllFiltered
            ? $this->getFooterContentsQueryRaw()->pluck('id')->toArray()
            : $this->selectedFooterContents;
        $footerContents = \App\Models\FooterContent::whereIn('id', $ids)->get();
        foreach ($footerContents as $footerContent) {
            if ($footerContent->icon_path) {
                Storage::disk('public')->delete($footerContent->icon_path);
            }
            if ($footerContent->image_path) {
                Storage::disk('public')->delete($footerContent->image_path);
            }
            $footerContent->delete();
        }
        $this->selectedFooterContents = [];
        $this->selectAll = false;
        $this->applyToAllFiltered = false;
        $this->dispatch('show-alert', type: 'success', message: 'آیتم‌های انتخاب‌شده حذف شدند!');
    }

    public function updateSelectedStatus($status)
    {
        $ids = $this->applyToAllFiltered
            ? $this->getFooterContentsQueryRaw()->pluck('id')->toArray()
            : $this->selectedFooterContents;
        \App\Models\FooterContent::whereIn('id', $ids)->update(['is_active' => $status]);
        $this->selectedFooterContents = [];
        $this->selectAll = false;
        $this->applyToAllFiltered = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت آیتم‌ها بروزرسانی شد.');
    }

    private function getFooterContentsQueryRaw()
    {
        $query = \App\Models\FooterContent::query();
        if ($this->search) {
            $query->where('section', 'like', '%' . $this->search . '%')
                ->orWhere('title', 'like', '%' . $this->search . '%');
        }
        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active' ? 1 : 0);
        }
        return $query->orderBy('order');
    }

    private function getFooterContentsQuery()
    {
        return $this->getFooterContentsQueryRaw()->paginate($this->perPage);
    }

    public function render()
    {
        $this->totalFilteredCount = $this->readyToLoad ? $this->getFooterContentsQueryRaw()->count() : 0;
        $footer_contents = $this->readyToLoad ? $this->getFooterContentsQuery() : collect();
        return view('livewire.admin.panel.footer-contents.footer-content-list', compact('footer_contents'))
            ->with('totalFilteredCount', $this->totalFilteredCount);
    }
}
