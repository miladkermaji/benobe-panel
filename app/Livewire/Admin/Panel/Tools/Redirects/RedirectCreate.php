<?php

namespace App\Livewire\Admin\Panel\Tools\Redirects;

use App\Models\Admin\Panel\Tools\Redirect;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class RedirectCreate extends Component
{
    public $source_url;
    public $target_url;
    public $status_code = 301;  // مقدار پیش‌فرض
    public $is_active   = true; // مقدار پیش‌فرض
    public $description;

    public function store()
    {
        $validator = Validator::make([
            'source_url'  => $this->source_url,
            'target_url'  => $this->target_url,
            'status_code' => $this->status_code,
            'is_active'   => $this->is_active,
            'description' => $this->description,
        ], [
            'source_url'  => 'required|url|unique:redirects,source_url',
            'target_url'  => 'required|url',
            'status_code' => 'required|in:301,302',
            'is_active'   => 'required|boolean',
            'description' => 'nullable|string|max:500',
        ], [
            'source_url.required'  => 'URL مبدا الزامی است.',
            'source_url.url'       => 'URL مبدا باید معتبر باشد.',
            'source_url.unique'    => 'این URL مبدا قبلاً ثبت شده است.',
            'target_url.required'  => 'URL مقصد الزامی است.',
            'target_url.url'       => 'URL مقصد باید معتبر باشد.',
            'status_code.required' => 'کد وضعیت الزامی است.',
            'status_code.in'       => 'کد وضعیت باید 301 یا 302 باشد.',
            'description.max'      => 'توضیحات نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        Redirect::create([
            'source_url'  => $this->source_url,
            'target_url'  => $this->target_url,
            'status_code' => $this->status_code,
            'is_active'   => $this->is_active,
            'description' => $this->description,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'ریدایرکت با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.tools.redirects.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.tools.redirects.redirect-create');
    }
}
