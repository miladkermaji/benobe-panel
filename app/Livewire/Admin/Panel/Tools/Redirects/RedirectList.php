<?php

namespace App\Livewire\Admin\Panel\Tools\Redirects;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use App\Models\Admin\Panel\Tools\Redirect;

class RedirectList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteRedirectConfirmed' => 'deleteRedirect'];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedRedirects = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadRedirects()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($redirectId)
    {
        $redirect = Redirect::findOrFail($redirectId);
        $redirect->update(['is_active' => !$redirect->is_active]);

        $this->dispatch('show-alert', type: $redirect->is_active ? 'success' : 'info', message: $redirect->is_active ? 'فعال شد!' : 'غیرفعال شد!');
        Cache::forget('redirects_' . $this->search . '_page_' . $this->getPage()); // اصلاح $this->page به $this->getPage()
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteRedirect($id)
    {
        $redirect = Redirect::findOrFail($id);
        $redirect->delete();

        Cache::forget('redirects_' . $this->search . '_page_' . $this->getPage()); // اصلاح شده
        $this->dispatch('show-alert', type: 'success', message: 'ریدایرکت حذف شد!');

        $totalRedirects = Redirect::count();
        $maxPage = ceil($totalRedirects / $this->perPage);
        if ($this->getPage() > $maxPage && $maxPage > 0) {
            $this->setPage($maxPage);
        } elseif ($maxPage == 0) {
            $this->resetPage();
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getRedirectsQuery()->pluck('id')->toArray();
        $this->selectedRedirects = $value ? $currentPageIds : [];
    }

    public function updatedSelectedRedirects()
    {
        $currentPageIds = $this->getRedirectsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedRedirects) && count(array_diff($currentPageIds, $this->selectedRedirects)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedRedirects)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ ریدایرکتی انتخاب نشده است.');
            return;
        }

        Redirect::whereIn('id', $this->selectedRedirects)->delete();
        $this->selectedRedirects = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'ریدایرکت‌های انتخاب‌شده حذف شدند!');
    }

    private function getRedirectsQuery()
    {
        return Redirect::where('source_url', 'like', '%' . $this->search . '%')
            ->orWhere('target_url', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $redirects = $this->readyToLoad ? $this->getRedirectsQuery() : null;

        return view('livewire.admin.panel.tools.redirects.redirect-list', [
            'redirects' => $redirects,
        ]);
    }
}