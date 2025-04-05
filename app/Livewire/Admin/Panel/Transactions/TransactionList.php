<?php

namespace App\Livewire\Admin\Panel\Transactions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Secretary;

class TransactionList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $expandedEntities = [];
    public $readyToLoad = false;

    public $perPage = 5; // تعداد موجودیت‌ها در هر صفحه
    public $transactionsPerPage = 5; // تعداد تراکنش‌ها در هر صفحه
    public $entityPages = []; // آرایه برای صفحه‌بندی تراکنش‌ها

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = ['deleteTransactionConfirmed' => 'deleteTransaction'];

    public function mount()
    {
        $this->readyToLoad = true;
    }

    public function toggleEntity($type, $id)
    {
        $key = $type . '-' . $id;
        if (in_array($key, $this->expandedEntities)) {
            $this->expandedEntities = array_diff($this->expandedEntities, [$key]);
        } else {
            $this->expandedEntities[] = $key;
            if (!isset($this->entityPages[$key])) {
                $this->entityPages[$key] = 1; // صفحه اولیه تراکنش‌ها
            }
        }
    }

    public function setEntityPage($key, $page)
    {
        $this->entityPages[$key] = max(1, $page);
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

    public function updatedSearch()
    {
        $this->resetPage();
        $this->expandedEntities = [];
        $this->entityPages = [];
    }

    private function getEntitiesQuery($model, $type)
    {
        return $model::with(['transactions' => function ($query) {
            $query->where('gateway', 'like', '%' . $this->search . '%')
                  ->orWhere('transaction_id', 'like', '%' . $this->search . '%');
        }])
        ->where(function ($query) {
            $query->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%');
        })
        ->has('transactions');
    }

 public function render()
{
    // کوئری‌های اولیه برای موجودیت‌ها
    $users = $this->readyToLoad ? $this->getEntitiesQuery(User::class, 'user')
        ->paginate($this->perPage, ['*'], 'usersPage') : collect();

    $doctors = $this->readyToLoad ? $this->getEntitiesQuery(Doctor::class, 'doctor')
        ->paginate($this->perPage, ['*'], 'doctorsPage') : collect();

    $secretaries = $this->readyToLoad ? $this->getEntitiesQuery(Secretary::class, 'secretary')
        ->paginate($this->perPage, ['*'], 'secretariesPage') : collect();

    // پردازش کاربران
    $users = $users->map(function ($entity) {
        $key = 'user-' . $entity->id;
        $currentPage = $this->entityPages[$key] ?? 1;
        $transactions = $entity->transactions() // فقط تراکنش‌های این کاربر
            ->where(function ($query) {
                $query->where('gateway', 'like', '%' . $this->search . '%')
                      ->orWhere('transaction_id', 'like', '%' . $this->search . '%');
            })
            ->forPage($currentPage, $this->transactionsPerPage)
            ->get();

        return [
            'entity' => $entity,
            'type' => 'user',
            'transactions' => $transactions,
            'totalTransactions' => $entity->transactions()->count(), // تعداد کل تراکنش‌های این کاربر
            'currentPage' => $currentPage,
            'lastPage' => ceil($entity->transactions()->count() / $this->transactionsPerPage),
        ];
    });

    // پردازش دکترها
    $doctors = $doctors->map(function ($entity) {
        $key = 'doctor-' . $entity->id;
        $currentPage = $this->entityPages[$key] ?? 1;
        $transactions = $entity->transactions() // فقط تراکنش‌های این دکتر
            ->where(function ($query) {
                $query->where('gateway', 'like', '%' . $this->search . '%')
                      ->orWhere('transaction_id', 'like', '%' . $this->search . '%');
            })
            ->forPage($currentPage, $this->transactionsPerPage)
            ->get();

        return [
            'entity' => $entity,
            'type' => 'doctor',
            'transactions' => $transactions,
            'totalTransactions' => $entity->transactions()->count(), // تعداد کل تراکنش‌های این دکتر
            'currentPage' => $currentPage,
            'lastPage' => ceil($entity->transactions()->count() / $this->transactionsPerPage),
        ];
    });

    // پردازش منشی‌ها
    $secretaries = $secretaries->map(function ($entity) {
        $key = 'secretary-' . $entity->id;
        $currentPage = $this->entityPages[$key] ?? 1;
        $transactions = $entity->transactions() // فقط تراکنش‌های این منشی
            ->where(function ($query) {
                $query->where('gateway', 'like', '%' . $this->search . '%')
                      ->orWhere('transaction_id', 'like', '%' . $this->search . '%');
            })
            ->forPage($currentPage, $this->transactionsPerPage)
            ->get();

        return [
            'entity' => $entity,
            'type' => 'secretary',
            'transactions' => $transactions,
            'totalTransactions' => $entity->transactions()->count(), // تعداد کل تراکنش‌های این منشی
            'currentPage' => $currentPage,
            'lastPage' => ceil($entity->transactions()->count() / $this->transactionsPerPage),
        ];
    });

    return view('livewire.admin.panel.transactions.transaction-list', [
        'users' => $users,
        'doctors' => $doctors,
        'secretaries' => $secretaries,
        'totalUsers' => $this->getEntitiesQuery(User::class, 'user')->count(),
        'totalDoctors' => $this->getEntitiesQuery(Doctor::class, 'doctor')->count(),
        'totalSecretaries' => $this->getEntitiesQuery(Secretary::class, 'secretary')->count(),
    ]);
}
}
