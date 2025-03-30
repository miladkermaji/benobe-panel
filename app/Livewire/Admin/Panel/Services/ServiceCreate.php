<?php

namespace App\Livewire\Admin\Panel\Services;

use App\Models\Service;
use Livewire\Component;

class ServiceCreate extends Component
{
    public $name;
    public $description;
    public $status = true;

    public function store()
    {
        $this->validate([
            'name'        => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:500',
            'status'      => 'required|boolean',
        ], [
            'name.required'      => 'لطفاً نام خدمت را وارد کنید.',
            'name.string'        => 'نام خدمت باید یک متن باشد.',
            'name.max'           => 'نام خدمت نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'name.unique'        => 'این نام خدمت قبلاً ثبت شده است.',
            'description.string' => 'توضیحات باید یک متن باشد.',
            'description.max'    => 'توضیحات نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
            'status.required'    => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean'     => 'وضعیت باید فعال یا غیرفعال باشد.',
        ]);

        Service::create([
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'خدمت با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.services.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.services.service-create');
    }
}
