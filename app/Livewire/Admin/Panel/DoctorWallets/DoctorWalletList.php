<?php

namespace App\Livewire\Admin\Panel\DoctorWallets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Doctor;
use App\Models\DoctorWalletTransaction;
use Jalalian; // برای تاریخ فارسی

class DoctorWalletList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $readyToLoad = false;
    public $expandedDoctors = []; // برای ذخیره پزشک‌هایی که باز شدن
    public $perPageDoctors = 5; // تعداد پزشکان در هر صفحه
    public $perPageTransactions = 5; // تعداد تراکنش‌ها در هر صفحه برای هر پزشک

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = ['deleteTransactionConfirmed' => 'deleteTransaction'];

    public function mount()
    {
        $this->readyToLoad = true;
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
        }
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

    public function updatedSearch()
    {
        $this->resetPage();
        $this->expandedDoctors = []; // بستن همه تاگل‌ها بعد از جستجو
    }

    public function render()
    {
        $doctors = $this->readyToLoad
            ? Doctor::where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                       ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
            })->with(['walletTransactions' => function ($query) {
                $query->latest();
            }])->paginate($this->perPageDoctors)
            : collect();

        return view('livewire.admin.panel.doctor-wallets.doctor-wallet-list', [
            'doctors' => $doctors,
        ]);
    }

    public function getTransactionsForDoctor($doctorId, $page = 1)
    {
        return DoctorWalletTransaction::where('doctor_id', $doctorId)
            ->latest()
            ->paginate($this->perPageTransactions, ['*'], "transactions_page_{$doctorId}", $page);
    }
}
