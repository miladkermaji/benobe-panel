<?php

namespace App\Livewire\Admin\Panel\Bannertexts;

use App\Models\BannerText;
use Livewire\Component;
use Livewire\WithPagination;

class BannerTextList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteBannerTextConfirmed' => 'deleteBannerText'];

    public $perPage             = 10;
    public $search              = '';
    public $readyToLoad         = false;
    public $selectedbannertexts = [];
    public $selectAll           = false;
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $statusFilter = '';
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadbannertexts()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = BannerText::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteBannerText($id)
    {
        $item = BannerText::findOrFail($id);
        if ($item->image_path && \Storage::disk('public')->exists($item->image_path)) {
            \Storage::disk('public')->delete($item->image_path);
        }
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'بنر حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds            = $this->getbannertextsQuery()->pluck('id')->toArray();
        $this->selectedbannertexts = $value ? $currentPageIds : [];
    }

    public function updatedSelectedbannertexts()
    {
        $currentPageIds  = $this->getbannertextsQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedbannertexts) && count(array_diff($currentPageIds, $this->selectedbannertexts)) === 0;
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
        if (empty($this->selectedbannertexts) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ بنری انتخاب نشده است.');
            return;
        }
        $this->dispatch('confirm-delete-selected');
    }

    public function deleteSelectedConfirmed()
    {
        $ids = $this->applyToAllFiltered
            ? $this->getbannertextsQueryRaw()->pluck('id')->toArray()
            : $this->selectedbannertexts;
        $items = \App\Models\BannerText::whereIn('id', $ids)->get();
        foreach ($items as $item) {
            if ($item->image_path && \Storage::disk('public')->exists($item->image_path)) {
                \Storage::disk('public')->delete($item->image_path);
            }
        }
        \App\Models\BannerText::whereIn('id', $ids)->delete();
        $this->selectedbannertexts = [];
        $this->selectAll = false;
        $this->applyToAllFiltered = false;
        $this->dispatch('show-alert', type: 'success', message: 'بنرهای انتخاب‌شده حذف شدند!');
    }

    public function updateSelectedStatus($status)
    {
        $ids = $this->applyToAllFiltered
            ? $this->getbannertextsQueryRaw()->pluck('id')->toArray()
            : $this->selectedbannertexts;
        \App\Models\BannerText::whereIn('id', $ids)->update(['status' => $status]);
        $this->selectedbannertexts = [];
        $this->selectAll = false;
        $this->applyToAllFiltered = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت بنرها بروزرسانی شد.');
    }

    private function getbannertextsQueryRaw()
    {
        $query = \App\Models\BannerText::query();
        if ($this->search) {
            $query->where('main_text', 'like', '%' . $this->search . '%')
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(switch_words, '$[*]')) LIKE ?", ['%' . $this->search . '%'])
                ->orWhere('image_path', 'like', '%' . $this->search . '%');
        }
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter === 'active' ? 1 : 0);
        }
        return $query;
    }

    private function getbannertextsQuery()
    {
        return $this->getbannertextsQueryRaw()->paginate($this->perPage);
    }

    public function render()
    {
        $this->totalFilteredCount = $this->readyToLoad ? $this->getbannertextsQueryRaw()->count() : 0;
        $items = $this->readyToLoad ? $this->getbannertextsQuery() : null;
        return view('livewire.admin.panel.banner-texts.banner-text-list', [
            'bannertexts' => $items,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
