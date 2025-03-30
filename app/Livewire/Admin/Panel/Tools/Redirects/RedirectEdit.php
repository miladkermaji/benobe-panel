<?php

namespace App\Livewire\Admin\Panel\Tools\Redirects;

use App\Models\Admin\Panel\Tools\Redirect;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class RedirectEdit extends Component
{
    public $redirect;
    public $source_url;
    public $target_url;
    public $status_code;
    public $is_active;
    public $description;

    public function mount($id)
    {
        $this->redirect    = Redirect::findOrFail($id);
        $this->source_url  = $this->redirect->source_url;
        $this->target_url  = $this->redirect->target_url;
        $this->status_code = $this->redirect->status_code;
        $this->is_active   = $this->redirect->is_active;
        $this->description = $this->redirect->description;
    }

    public function update()
    {
        $validator = Validator::make([
            'source_url'  => $this->source_url,
            'target_url'  => $this->target_url,
            'status_code' => $this->status_code,
            'is_active'   => $this->is_active,
            'description' => $this->description,
        ], [
            'source_url'  => 'required|url|unique:redirects,source_url,' . $this->redirect->id,
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

        $this->redirect->update([
            'source_url'  => $this->source_url,
            'target_url'  => $this->target_url,
            'status_code' => $this->status_code,
            'is_active'   => $this->is_active,
            'description' => $this->description,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'ریدایرکت با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.tools.redirects.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.tools.redirects.redirect-edit');
    }
}
