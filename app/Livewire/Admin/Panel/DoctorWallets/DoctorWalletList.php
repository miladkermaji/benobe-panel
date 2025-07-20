<?php

namespace App\Livewire\Admin\Panel\DoctorWallets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Doctor;
use App\Models\DoctorWalletTransaction;

class DoctorWalletList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $readyToLoad = false;
    public $expandedDoctors = [];
    public $perPageDoctors = 100;
    public $perPageTransactions = 20;
    public $statusFilter = '';
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $selectedTransactions = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'deleteTransactionConfirmed' => 'deleteTransaction',
        'deleteTransactionGroupConfirmed' => 'deleteSelected',
    ];

    public function mount()
    {
        $this->readyToLoad = true;
    }

    public function loadWallets()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->expandedDoctors = [];
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->expandedDoctors = [];
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getTransactionsQuery()->pluck('id')->toArray();
        $this->selectedTransactions = $value ? $currentPageIds : [];
    }

    public function updatedSelectedTransactions()
    {
        $currentPageIds = $this->getTransactionsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedTransactions) && count(array_diff($currentPageIds, $this->selectedTransactions)) === 0;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteTransaction($id)
    {
        $item = DoctorWalletTransaction::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تراکنش با موفقیت حذف شد!');
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getTransactionsQuery();
            $query->delete();
            $this->selectedTransactions = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه تراکنش‌های فیلترشده حذف شدند!');
            return;
        }
        if (empty($this->selectedTransactions)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ تراکنشی انتخاب نشده است.');
            return;
        }
        DoctorWalletTransaction::whereIn('id', $this->selectedTransactions)->delete();
        $this->selectedTransactions = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'تراکنش‌های انتخاب شده حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedTransactions) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ تراکنشی انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        if ($this->applyToAllFiltered) {
            $query = $this->getTransactionsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_paid':
                    $query->update(['status' => 'paid']);
                    $this->dispatch('show-alert', type: 'success', message: 'همه تراکنش‌های فیلترشده پرداخت‌شده شدند!');
                    break;
                case 'status_failed':
                    $query->update(['status' => 'failed']);
                    $this->dispatch('show-alert', type: 'success', message: 'همه تراکنش‌های فیلترشده ناموفق شدند!');
                    break;
            }
            $this->selectedTransactions = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            return;
        }
        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'status_paid':
                $this->updateStatus('paid');
                break;
            case 'status_failed':
                $this->updateStatus('failed');
                break;
        }
        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        DoctorWalletTransaction::whereIn('id', $this->selectedTransactions)
            ->update(['status' => $status]);
        $this->selectedTransactions = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت تراکنش‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    protected function getTransactionsQuery()
    {
        return DoctorWalletTransaction::when($this->search, function ($query) {
            $search = trim($this->search);
            $query->whereHas('doctor', function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                    ->orWhere('mobile', 'like', "%$search%") ;
            });
        })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('id', 'desc');
    }

    public function render()
    {
        $this->totalFilteredCount = $this->readyToLoad ? $this->getTransactionsQuery()->count() : 0;
        $doctors = $this->readyToLoad
            ? Doctor::where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
            })->with(['walletTransactions' => function ($query) {
                $query->when($this->statusFilter, function ($q) {
                    $q->where('status', $this->statusFilter);
                });
                $query->latest();
            }])->get()->filter(function ($doctor) {
                return $doctor->walletTransactions->count() > 0;
            })
            : collect();
        return view('livewire.admin.panel.doctor-wallets.doctor-wallet-list', [
            'doctors' => $doctors,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
