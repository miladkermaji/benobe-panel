<?php

namespace App\Livewire\Admin\Doctors;

use Livewire\Component;
use App\Models\DoctorWallet;
use Illuminate\Support\Facades\Auth;
use App\Models\Dr\DoctorPaymentSetting;
use App\Models\Dr\DoctorSettlementRequest;
use App\Models\DoctorWalletTransaction;

class DoctorWalletRequest extends Component
{
    public $visit_fee = 20000; // پیش‌فرض 20 هزار تومان
    public $card_number;
    public $requests = []; // فقط آرایه داده‌ها
    public $availableAmount = 0;
    public $search = ''; // برای جستجوی لایو
    public $selectAll = false; // برای انتخاب همه
    public $selectedRequests = []; // آرایه برای ذخیره درخواست‌های انتخاب‌شده
    public $currentPage = 1; // برای صفحه‌بندی
    public $perPage = 10; // تعداد ردیف‌ها در هر صفحه

    public function mount()
    {
        $settings = DoctorPaymentSetting::first();

        if (!$settings) {
            DoctorPaymentSetting::create([
                'visit_fee' => $this->visit_fee,
                'card_number' => null,
            ]);
        } else {
            $this->visit_fee = $settings->visit_fee; // مقدار خام
            $this->card_number = $settings->card_number;
        }

        $this->loadData();
    }

    public function render()
    {
        $totalIncome = DoctorWallet::sum('balance');
        $paid = DoctorWalletTransaction::where('status', 'paid')->sum('amount');
        $available = DoctorWallet::sum('balance');

        $this->loadData();

        return view('livewire.admin.doctors.doctor-wallet-request', [
            'totalIncome' => $totalIncome,
            'paid' => $paid,
            'available' => $available,
            'formatted_visit_fee' => number_format($this->visit_fee), // فرمت‌شده برای نمایش
            'requestsPaginated' => $this->getPaginatedRequests(), // برای صفحه‌بندی در Blade
        ])->layout('layouts.app'); // یا هر لایت دیگری که داری
    }

    public function updatedSearch()
    {
        $this->loadData(); // آپدیت لیست درخواست‌ها با جستجو
        $this->currentPage = 1; // برگشت به صفحه اول بعد از جستجو
    }

    public function updatedSelectAll()
    {
        $this->selectedRequests = $this->selectAll ? $this->requests->pluck('id')->toArray() : [];
        $this->dispatch('updateSelectAll', $this->selectAll); // فراخوانی رویداد برای آپدیت چک‌باکس‌ها
    }

    public function updatedPerPage()
    {
        $this->loadData(); // آپدیت لیست با تغییر تعداد ردیف‌ها
        $this->currentPage = 1; // برگشت به صفحه اول
    }

    public function setPage($page)
    {
        $this->currentPage = $page;
        $this->loadData();
    }

    public function selectAllRequests()
    {
        $this->selectAll = true;
        $this->updatedSelectAll();
    }

    public function deleteSelectedRequests()
    {
        if (empty($this->selectedRequests)) {
            $this->dispatch('toast', ['message' => 'هیچ درخواست انتخاب‌شده‌ای وجود ندارد!', 'type' => 'error']);
            return;
        }

        foreach ($this->selectedRequests as $requestId) {
            $transaction = DoctorSettlementRequest::where('id', $requestId)->first();
            if ($transaction) {
                $transaction->delete(); // حذف نرم
            }
        }

        $this->dispatch('toast', message: 'درخواست‌های انتخاب‌شده با موفقیت حذف شدند.', type: 'success');
        $this->selectedRequests = [];
        $this->selectAll = false;
        $this->loadData();
    }

    public function deleteRequest($requestId)
    {
        $transaction = DoctorSettlementRequest::where('id', $requestId)->first();

        if ($transaction) {
            $transaction->delete(); // حذف نرم
            $this->dispatch('toast', message: 'درخواست‌های انتخاب‌شده با موفقیت حذف شدند.', type: 'success');
        } else {
            $this->dispatch('toast', ['message' => 'درخواست یافت نشد!', 'type' => 'error']);
        }

        $this->loadData();
    }

    protected function loadData()
    {
        $query = DoctorSettlementRequest::with('doctor')->latest();

        // فیلتر بر اساس جستجو
        if (!empty($this->search)) {
            $query->whereHas('doctor', function ($q) {
                $q->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            });
        }

        $paginator = $query->paginate($this->perPage, ['*'], 'page', $this->currentPage);
        $this->requests = collect($paginator->items()); // فقط داده‌های خام

        // اگر نیاز به کیف پول عمومی دارید
        $wallet = DoctorWallet::firstOrCreate([], ['balance' => 0]);
        $this->availableAmount = $wallet->balance;
    }

    protected function getPaginatedRequests()
    {
        $query = DoctorSettlementRequest::with('doctor')->latest();

        if (!empty($this->search)) {
            $query->whereHas('doctor', function ($q) {
                $q->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            });
        }

        return $query->paginate($this->perPage);
    }
}
