<?php

namespace Modules\SendOtp\App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\SendOtp\App\Models\SmsGateway;
use Illuminate\Support\Facades\Cache;

class SmsGatewayList extends Component
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
  $gateway = SmsGateway::find($gatewayId);
  $isActive = !$gateway->is_active;

  if ($isActive) {
   SmsGateway::query()->update(['is_active' => false]);
  }

  $gateway->update(['is_active' => $isActive]);

  $activeCount = SmsGateway::where('is_active', true)->count();
  if ($activeCount == 0) {
   SmsGateway::where('name', 'pishgamrayan')->update(['is_active' => true]);
   $this->dispatch('show-alert', type: 'warning', message: 'پیشگام رایان به‌صورت خودکار فعال شد!');
  } else {
   $this->dispatch('show-alert', type: $isActive ? 'success' : 'info', message: $isActive ? 'فعال شد!' : 'غیرفعال شد!');
  }

  Cache::forget('sms_gateways_' . $this->search . '_page_' . $this->page);
 }

 public function confirmDelete($name)
 {
  $this->dispatch('confirm-delete', name: $name);
 }

 public function deleteGateway($name)
 {
  $gateway = SmsGateway::where('name', $name)->first();
  if ($gateway->is_active) {
   $this->dispatch('show-alert', type: 'error', message: 'نمی‌توانید پنل فعال را حذف کنید.');
   return;
  }

  $gateway->delete();
  Cache::forget('sms_gateways_' . $this->search . '_page_' . $this->page);
  $this->dispatch('show-alert', type: 'success', message: 'حذف شد!');

  $totalGateways = SmsGateway::count();
  $maxPage = ceil($totalGateways / $this->perPage);
  if ($this->page > $maxPage && $maxPage > 0) {
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
   ? SmsGateway::where('title', 'like', '%' . $this->search . '%')
    ->orWhere('name', 'like', '%' . $this->search . '%')
    ->paginate($this->perPage)
   : null;

  return view('sendotp::livewire.sms-gateway-list', [
   'gateways' => $gateways,
  ]);
 }
}