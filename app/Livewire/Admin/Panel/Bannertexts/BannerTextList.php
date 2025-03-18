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

    public function deleteSelected()
    {
        if (empty($this->selectedbannertexts)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ بنری انتخاب نشده است.');
            return;
        }

        $items = BannerText::whereIn('id', $this->selectedbannertexts)->get();
        foreach ($items as $item) {
            if ($item->image_path && \Storage::disk('public')->exists($item->image_path)) {
                \Storage::disk('public')->delete($item->image_path);
            }
        }
        BannerText::whereIn('id', $this->selectedbannertexts)->delete();
        $this->selectedbannertexts = [];
        $this->selectAll           = false;
        $this->dispatch('show-alert', type: 'success', message: 'بنرهای انتخاب‌شده حذف شدند!');
    }

    private function getbannertextsQuery()
    {
        return BannerText::where('main_text', 'like', '%' . $this->search . '%')
            ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(switch_words, '$[*]')) LIKE ?", ['%' . $this->search . '%'])
            ->orWhere('image_path', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getbannertextsQuery() : null;

        return view('livewire.admin.panel.bannertexts.bannertext-list', [
            'bannertexts' => $items,
        ]);
    }
}
