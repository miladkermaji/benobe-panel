<?php

namespace Modules\SendOtp\App\Livewire;

use Livewire\Component;
use Modules\SendOtp\App\Models\SmsGateway;
use Illuminate\Support\Facades\Validator;

class SmsGatewayEdit extends Component
{
 public $gateway;
 public $title;
 public $is_active;
 public $settings;

 public function mount($name)
 {
  $gateway = SmsGateway::where('name', $name)->first();
  if (!$gateway) {
   session()->flash('error', 'پنل مورد نظر یافت نشد.');
   return redirect()->route('admin.sms-gateways.index');
  }

  $this->gateway = $gateway;
  $this->title = $gateway->title;
  $this->is_active = $gateway->is_active;
  $this->settings = json_encode($gateway->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
 }

 public function update()
 {
  $validator = Validator::make([
   'title' => $this->title,
   'is_active' => $this->is_active,
   'settings' => $this->settings,
  ], [
   'title' => 'required|string|max:255',
   'is_active' => 'required|boolean',
   'settings' => 'required|json',
  ], [
   'title.required' => 'عنوان پنل الزامی است.',
   'title.string' => 'عنوان پنل باید متنی باشد.',
   'title.max' => 'عنوان پنل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
   'is_active.required' => 'وضعیت پنل الزامی است.',
   'is_active.boolean' => 'وضعیت پنل باید فعال یا غیرفعال باشد.',
   'settings.required' => 'تنظیمات پنل الزامی است.',
   'settings.json' => 'تنظیمات باید به فرمت JSON معتبر باشد.',
  ]);

  if ($validator->fails()) {
   $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
   return;
  }

  if ($this->is_active) {
   SmsGateway::query()->update(['is_active' => false]);
  }

  $updated = SmsGateway::where('name', $this->gateway->name)->update([
   'title' => $this->title,
   'is_active' => $this->is_active,
   'settings' => $this->settings,
   'updated_at' => now(),
  ]);

  if ($updated) {
   $this->dispatch('show-alert', type: 'success', message: 'تغییرات با موفقیت ذخیره شد!');
   return redirect()->route('admin.sms-gateways.index');
  } else {
   $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره تغییرات!');
  }
 }

 public function render()
 {
  return view('sendotp::livewire.sms-gateway-edit');
 }
}