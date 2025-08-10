<?php

namespace App\Livewire\Admin\Panel\Faqs;

use App\Models\Faq;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class FaqList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteFaqConfirmed' => 'deleteFaq',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedFaqs = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadFaqs()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = Faq::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'سوال متداول یافت نشد.');
            return;
        }
        $action = $item->is_active ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $item->question, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $item = Faq::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'سوال متداول یافت نشد.');
            return;
        }

        $newStatus = !$item->is_active;
        $item->update(['is_active' => $newStatus]);

        if ($newStatus) {
            $this->dispatch('show-alert', type: 'success', message: 'سوال متداول فعال شد!');
        } else {
            $this->dispatch('show-alert', type: 'info', message: 'سوال متداول غیرفعال شد!');
        }

        Cache::forget('faqs_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $item = Faq::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'سوال متداول یافت نشد.');
            return;
        }

        $this->dispatch('confirm-delete', id: $id, name: $item->question);
    }

    public function deleteFaq($id)
    {
        $item = Faq::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'سوال متداول یافت نشد.');
            return;
        }

        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'سوال متداول با موفقیت حذف شد!');

        Cache::forget('faqs_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedFaqs = $this->getFaqsQuery()->pluck('id')->map(fn ($id) => (string) $id);
        } else {
            $this->selectedFaqs = [];
        }
    }

    public function updatedSelectedFaqs()
    {
        $this->selectAll = false;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered) {
            $faqsToDelete = $this->getFaqsQuery()->pluck('id');
        } else {
            $faqsToDelete = $this->selectedFaqs;
        }

        if (empty($faqsToDelete)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک مورد را انتخاب کنید.');
            return;
        }

        $count = Faq::whereIn('id', $faqsToDelete)->delete();

        $this->selectedFaqs = [];
        $this->selectAll = false;
        $this->groupAction = '';

        $this->dispatch('show-alert', type: 'success', message: "{$count} سوال متداول با موفقیت حذف شد!");

        Cache::forget('faqs_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedFaqs) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک مورد را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'activate':
                $this->updateStatus(true);
                break;
            case 'deactivate':
                $this->updateStatus(false);
                break;
            case 'delete':
                $this->dispatch('confirm-delete-selected', allFiltered: $this->applyToAllFiltered);
                break;
            default:
                $this->dispatch('show-alert', type: 'warning', message: 'عملیات نامعتبر انتخاب شده است.');
                break;
        }
    }

    private function updateStatus($status)
    {
        if ($this->applyToAllFiltered) {
            $faqsToUpdate = $this->getFaqsQuery()->pluck('id');
        } else {
            $faqsToUpdate = $this->selectedFaqs;
        }

        if (empty($faqsToUpdate)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک مورد را انتخاب کنید.');
            return;
        }

        $count = Faq::whereIn('id', $faqsToUpdate)->update(['is_active' => $status]);

        $this->selectedFaqs = [];
        $this->selectAll = false;
        $this->groupAction = '';

        $statusText = $status ? 'فعال' : 'غیرفعال';
        $this->dispatch('show-alert', type: 'success', message: "{$count} سوال متداول {$statusText} شد!");

        Cache::forget('faqs_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    private function getFaqsQuery()
    {
        $query = Faq::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('question', 'like', '%' . $this->search . '%')
                  ->orWhere('answer', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            // Parse combined filter format: "status_category" (e.g., "1_citizens", "0_doctors")
            $filterParts = explode('_', $this->statusFilter);
            if (count($filterParts) === 2) {
                $status = $filterParts[0];
                $category = $filterParts[1];

                $query->where('is_active', $status);
                $query->where('category', $category);
            } else {
                // Fallback for old format
                $query->where('is_active', $this->statusFilter);
            }
        }

        return $query;
    }

    public function render()
    {
        if (!$this->readyToLoad) {
            return view('livewire.admin.panel.faqs.faq-list', [
                'faqs' => collect([]),
                'totalCount' => 0,
                'filteredCount' => 0,
            ]);
        }

        $query = $this->getFaqsQuery();
        $totalCount = Faq::count();
        $filteredCount = $query->count();
        $this->totalFilteredCount = $filteredCount;

        $faqs = $query->orderBy('order', 'asc')
                     ->orderBy('created_at', 'desc')
                     ->paginate($this->perPage);

        return view('livewire.admin.panel.faqs.faq-list', [
            'faqs' => $faqs,
            'totalCount' => $totalCount,
            'filteredCount' => $filteredCount,
        ]);
    }
}
