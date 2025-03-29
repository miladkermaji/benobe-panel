<?php

namespace App\Livewire\Admin\Panel\Tools\SmsGateway;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;


class SmsGatewayCreate extends Component
{
    public $name;
    public $title;
    public $is_active = false;
    public $settings = '{}';

    protected $rules = [
        'name' => 'required|string|max:50|unique:sms_gateways,name',
        'title' => 'required|string|max:255',
        'is_active' => 'required|boolean',
        'settings' => 'required|json',
    ];

    protected $messages = [
        'name.required' => 'نام سیستم درگاه الزامی است.',
        'name.string' => 'نام سیستم درگاه باید متنی باشد.',
        'name.max' => 'نام سیستم درگاه نمی‌تواند بیشتر از ۵۰ کاراکتر باشد.',
        'name.unique' => 'این نام سیستم قبلاً ثبت شده است.',
        'title.required' => 'عنوان درگاه الزامی است.',
        'title.string' => 'عنوان درگاه باید متنی باشد.',
        'title.max' => 'عنوان درگاه نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        'is_active.required' => 'وضعیت درگاه الزامی است.',
        'is_active.boolean' => 'وضعیت درگاه باید فعال یا غیرفعال باشد.',
        'settings.required' => 'تنظیمات درگاه الزامی است.',
        'settings.json' => 'تنظیمات باید به فرمت JSON معتبر باشد.',
    ];

    public function save()
    {
        $this->validate();

        if ($this->is_active) {
            DB::table('sms_gateways')->update(['is_active' => false]);
        }

        $created = DB::table('sms_gateways')->insert([
            'name' => $this->name,
            'title' => $this->title,
            'is_active' => $this->is_active,
            'settings' => $this->settings,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($created) {
            $this->dispatch('show-alert', type: 'success', message: 'درگاه پرداخت با موفقیت ایجاد شد!');
            return redirect()->route('admin.panel.tools.sms-gateways.index');
        } else {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ایجاد درگاه پرداخت!');
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.tools.sms-gateway.sms-gateway-create');
    }
}

