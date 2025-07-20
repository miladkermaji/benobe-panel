<?php

namespace App\Livewire\Admin\Panel\Transactions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Secretary;
use Illuminate\Support\Facades\DB;

class TransactionList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $readyToLoad = false;
    public $perPage = 5;
    public $transactionsPerPage = 5;
    public $entityPages = [];
    public $statusFilter = '';
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $selectedTransactions = [];
    public $selectAll = false;
    public $entityTypeFilter = 'all';

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

    public function loadTransactions()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->entityPages = [];
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->entityPages = [];
    }

    public function updatedEntityTypeFilter()
    {
        $this->resetPage();
        $this->entityPages = [];
        $this->selectedTransactions = [];
        $this->selectAll = false;
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
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تراکنش حذف شد!');
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
        Transaction::whereIn('id', $this->selectedTransactions)->delete();
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
                case 'status_pending':
                    $query->update(['status' => 'pending']);
                    $this->dispatch('show-alert', type: 'success', message: 'همه تراکنش‌های فیلترشده در انتظار شدند!');
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
            case 'status_pending':
                $this->updateStatus('pending');
                break;
        }
        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        Transaction::whereIn('id', $this->selectedTransactions)
            ->update(['status' => $status]);
        $this->selectedTransactions = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت تراکنش‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    protected function getTransactionsQuery()
    {
        return Transaction::when($this->search, function ($query) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('gateway', 'like', "%$search%")
                    ->orWhere('transaction_id', 'like', "%$search%")
                    ->orWhere('amount', 'like', "%$search%")
                    ->orWhereHasMorph('transactionable', ['App\\Models\\User', 'App\\Models\\Doctor', 'App\\Models\\Secretary'], function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('mobile', 'like', "%$search%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                    });
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
        $users = collect();
        $doctors = collect();
        $secretaries = collect();
        if ($this->readyToLoad && ($this->entityTypeFilter === 'user' || $this->entityTypeFilter === 'all')) {
            $users = User::whereHas('transactions')->get()->map(function ($entity) {
                $key = 'user-' . $entity->id;
                $currentPage = $this->entityPages[$key] ?? 1;
                $transactions = $entity->transactions()
                    ->when($this->search, function ($query) {
                        $search = trim($this->search);
                        $query->where(function ($q) use ($search) {
                            $q->where('gateway', 'like', "%$search%")
                                ->orWhere('transaction_id', 'like', "%$search%")
                                ->orWhere('amount', 'like', "%$search%") ;
                        });
                    })
                    ->when($this->statusFilter, function ($query) {
                        $query->where('status', $this->statusFilter);
                    })
                    ->forPage($currentPage, $this->transactionsPerPage)
                    ->get();
                return [
                    'entity' => $entity,
                    'type' => 'user',
                    'transactions' => $transactions,
                    'totalTransactions' => $entity->transactions()->count(),
                    'currentPage' => $currentPage,
                    'lastPage' => ceil($entity->transactions()->count() / $this->transactionsPerPage),
                ];
            });
        }
        if ($this->readyToLoad && ($this->entityTypeFilter === 'doctor' || $this->entityTypeFilter === 'all')) {
            $doctors = Doctor::whereHas('transactions')->get()->map(function ($entity) {
                $key = 'doctor-' . $entity->id;
                $currentPage = $this->entityPages[$key] ?? 1;
                $transactions = $entity->transactions()
                    ->when($this->search, function ($query) {
                        $search = trim($this->search);
                        $query->where(function ($q) use ($search) {
                            $q->where('gateway', 'like', "%$search%")
                                ->orWhere('transaction_id', 'like', "%$search%")
                                ->orWhere('amount', 'like', "%$search%") ;
                        });
                    })
                    ->when($this->statusFilter, function ($query) {
                        $query->where('status', $this->statusFilter);
                    })
                    ->forPage($currentPage, $this->transactionsPerPage)
                    ->get();
                return [
                    'entity' => $entity,
                    'type' => 'doctor',
                    'transactions' => $transactions,
                    'totalTransactions' => $entity->transactions()->count(),
                    'currentPage' => $currentPage,
                    'lastPage' => ceil($entity->transactions()->count() / $this->transactionsPerPage),
                ];
            });
        }
        if ($this->readyToLoad && ($this->entityTypeFilter === 'secretary' || $this->entityTypeFilter === 'all')) {
            $secretaries = Secretary::whereHas('transactions')->get()->map(function ($entity) {
                $key = 'secretary-' . $entity->id;
                $currentPage = $this->entityPages[$key] ?? 1;
                $transactions = $entity->transactions()
                    ->when($this->search, function ($query) {
                        $search = trim($this->search);
                        $query->where(function ($q) use ($search) {
                            $q->where('gateway', 'like', "%$search%")
                                ->orWhere('transaction_id', 'like', "%$search%")
                                ->orWhere('amount', 'like', "%$search%") ;
                        });
                    })
                    ->when($this->statusFilter, function ($query) {
                        $query->where('status', $this->statusFilter);
                    })
                    ->forPage($currentPage, $this->transactionsPerPage)
                    ->get();
                return [
                    'entity' => $entity,
                    'type' => 'secretary',
                    'transactions' => $transactions,
                    'totalTransactions' => $entity->transactions()->count(),
                    'currentPage' => $currentPage,
                    'lastPage' => ceil($entity->transactions()->count() / $this->transactionsPerPage),
                ];
            });
        }
        return view('livewire.admin.panel.transactions.transaction-list', [
            'users' => $users,
            'doctors' => $doctors,
            'secretaries' => $secretaries,
            'totalFilteredCount' => $this->totalFilteredCount,
            'transactionsPerPage' => $this->transactionsPerPage,
        ]);
    }
}
