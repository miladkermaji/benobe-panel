<?php

namespace App\Livewire\Admin\Panel\Tools\PaymentGateways;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GatewayEdit extends Component
{
    public $gateway;
    public $title;
    public $is_active;
    public $settings;

    public function mount($name)
    {
        $gateway = DB::table('payment_gateways')->where('name', $name)->first();
        if (!$gateway) {
            session()->flash('error', 'درگاه مورد نظر یافت نشد.');
            return redirect()->route('admin.panel.tools.payment_gateways.index');
        }

        $this->gateway = $gateway;
        $this->title = $gateway->title;
        $this->is_active = $gateway->is_active;
        $this->settings = $gateway->settings;
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
            'title.required' => 'عنوان درگاه الزامی است.',
            'title.string' => 'عنوان درگاه باید متنی باشد.',
            'title.max' => 'عنوان درگاه نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'is_active.required' => 'وضعیت درگاه الزامی است.',
            'is_active.boolean' => 'وضعیت درگاه باید فعال یا غیرفعال باشد.',
            'settings.required' => 'تنظیمات درگاه الزامی است.',
            'settings.json' => 'تنظیمات باید به فرمت JSON معتبر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        if ($this->is_active) {
            DB::table('payment_gateways')->update(['is_active' => false]);
        }

        $updated = DB::table('payment_gateways')->where('name', $this->gateway->name)->update([
            'title' => $this->title,
            'is_active' => $this->is_active,
            'settings' => $this->settings,
            'updated_at' => now(),
        ]);

        if ($updated) {
            $this->dispatch('show-alert', type: 'success', message: 'تغییرات با موفقیت ذخیره شد!');
            return redirect()->route('admin.panel.tools.payment_gateways.index');
        } else {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره تغییرات!');
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.tools.payment-gateways.gateway-edit');
    }
}
