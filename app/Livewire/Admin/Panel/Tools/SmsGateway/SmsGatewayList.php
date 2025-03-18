<?php
namespace App\Livewire\Admin\Panel\Tools\SmsGateway;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SmsGatewayList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteGatewayConfirmed' => 'deleteGateway'];

    public $perPage     = 10;
    public $search      = '';
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
        $gateway  = DB::table('sms_gateways')->where('id', $gatewayId)->first();
        $isActive = ! $gateway->is_active;

        if ($isActive) {
            DB::table('sms_gateways')->update(['is_active' => false]);
        }

        DB::table('sms_gateways')->where('id', $gatewayId)->update(['is_active' => $isActive]);

        $activeCount = DB::table('sms_gateways')->where('is_active', true)->count();
        if ($activeCount == 0) {
            DB::table('sms_gateways')->where('name', 'pishgamrayan')->update(['is_active' => true]);
            $this->dispatch('show-alert', type: 'warning', message: 'پیشگام رایان به‌صورت خودکار فعال شد!');
        } else {
            $this->dispatch('show-alert', type: $isActive ? 'success' : 'info', message: $isActive ? 'فعال شد!' : 'غیرفعال شد!');
        }

        Cache::forget('sms_gateways_' . $this->search . '_page_' . $this->getPage());
    }

    public function confirmDelete($name)
    {
        $this->dispatch('confirm-delete', name: $name);
    }

    public function deleteGateway($name)
    {
        $gateway = DB::table('sms_gateways')->where('name', $name)->first();
        if ($gateway->is_active) {
            $this->dispatch('show-alert', type: 'error', message: 'نمی‌توانید پنل فعال را حذف کنید.');
            return;
        }

        DB::table('sms_gateways')->where('name', $name)->delete();
        Cache::forget('sms_gateways_' . $this->search . '_page_' . $this->getPage());
        $this->dispatch('show-alert', type: 'success', message: 'حذف شد!');

        $totalGateways = DB::table('sms_gateways')->count();
        $maxPage       = ceil($totalGateways / $this->perPage);
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
        ? DB::table('sms_gateways')
            ->select('id', 'name', 'title', 'is_active')
            ->where('title', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage) // مطمئن شو که paginate فراخوانی شده
        : null;

        return view('livewire.admin.panel.tools.sms-gateway.sms-gateway-list', [
            'gateways' => $gateways,
        ]);
    }

}
