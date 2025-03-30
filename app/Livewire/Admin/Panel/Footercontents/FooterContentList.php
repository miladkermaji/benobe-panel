<?php

namespace App\Livewire\Admin\Panel\FooterContents;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FooterContent;
use Illuminate\Support\Facades\Storage;

class FooterContentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteFooterContentConfirmed' => 'deleteFooterContent'];

    public $perPage = 10;
    public $search = '';
    public $selectedFooterContents = [];
    public $selectAll = false;

    protected $queryString = ['search' => ['except' => '']];

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

    public function deleteSelected()
    {
        if (empty($this->selectedFooterContents)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ آیتمی انتخاب نشده است.');
            return;
        }

        $footerContents = FooterContent::whereIn('id', $this->selectedFooterContents)->get();
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
        $this->dispatch('show-alert', type: 'success', message: 'آیتم‌های انتخاب‌شده حذف شدند!');
    }

    private function getFooterContentsQuery()
    {
        return FooterContent::where('section', 'like', '%' . $this->search . '%')
            ->orWhere('title', 'like', '%' . $this->search . '%')
            ->orderBy('order')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $footer_contents = $this->getFooterContentsQuery();
        return view('livewire.admin.panel.footercontents.footercontent-list', compact('footer_contents'));
    }
}
