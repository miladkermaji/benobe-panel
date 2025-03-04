<?php

namespace App\Livewire\Admin\Panel\Tools\PaymentGateways;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GatewayList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteGatewayConfirmed' => 'deleteGateway'];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];


    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadGateways()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($gatewayId)
    {
        $gateway = DB::table('payment_gateways')->where('id', $gatewayId)->first();
        $isActive = !$gateway->is_active;

        if ($isActive) {
            DB::table('payment_gateways')->update(['is_active' => false]);
        }

        DB::table('payment_gateways')->where('id', $gatewayId)->update(['is_active' => $isActive]);

        $activeCount = DB::table('payment_gateways')->where('is_active', true)->count();
        if ($activeCount == 0) {
            DB::table('payment_gateways')->where('name', 'zarinpal')->update(['is_active' => true]);
            $this->dispatch('show-alert', type: 'warning', message: 'زرین‌پال به‌صورت خودکار فعال شد!');
        } else {
            $this->dispatch('show-alert', type: $isActive ? 'success' : 'info', message: $isActive ? 'فعال شد!' : 'غیرفعال شد!');
        }

        Cache::forget('payment_gateways_' . $this->search . '_page_' . $this->getPage());
    }

    public function confirmDelete($name)
    {
        $this->dispatch('confirm-delete', name: $name);
    }

    public function deleteGateway($name)
    {
        $gateway = DB::table('payment_gateways')->where('name', $name)->first();
        if ($gateway->is_active) {
            $this->dispatch('show-alert', type: 'error', message: 'نمی‌توانید درگاه فعال را حذف کنید.');
            return;
        }

        DB::table('payment_gateways')->where('name', $name)->delete();
        Cache::forget('payment_gateways_' . $this->search . '_page_' . $this->getPage());
        $this->dispatch('show-alert', type: 'success', message: 'حذف شد!');

        $totalGateways = DB::table('payment_gateways')->count();
        $maxPage = ceil($totalGateways / $this->perPage);
        if ($this->getPage() > $maxPage && $maxPage > 0) {
            $this->setPage($maxPage);
        } elseif ($maxPage == 0) {
            $this->resetPage();
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $gateways = $this->readyToLoad
            ? DB::table('payment_gateways')
                ->select('id', 'name', 'title', 'is_active')
                ->where('title', 'like', '%' . $this->search . '%')
                ->orWhere('name', 'like', '%' . $this->search . '%')
                ->paginate($this->perPage) // مطمئن شو که paginate فراخوانی شده
            : null;

        return view('livewire.admin.panel.tools.payment-gateways.gateway-list', [
            'gateways' => $gateways,
        ]);
    }


}