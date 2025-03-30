<?php

namespace App\Livewire\Admin\Panel\Bannertexts;

use App\Models\BannerText;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class BannerTextCreate extends Component
{
    use WithFileUploads;

    public $main_text;
    public $switch_words = ['']; // آرایه‌ای با یک مقدار پیش‌فرض خالی
    public $switch_interval;
    public $image;
    public $status = true;

    public function addSwitchWord()
    {
        $this->switch_words[] = '';
    }

    public function removeSwitchWord($index)
    {
        unset($this->switch_words[$index]);
        $this->switch_words = array_values($this->switch_words); // بازآرایی آرایه
    }

    public function store()
    {
        $validator = Validator::make([
            'main_text'       => $this->main_text,
            'switch_words'    => $this->switch_words,
            'switch_interval' => $this->switch_interval,
            'image'           => $this->image,
            'status'          => $this->status,
        ], [
            'main_text'       => 'required|string|max:255',
            'switch_words'    => 'nullable|array',
            'switch_words.*'  => 'string|max:100',
            'switch_interval' => 'nullable|integer|min:1',
            'image'           => 'nullable|image|max:2048',
            'status'          => 'required|boolean',
        ], [
            'main_text.required'      => 'فیلد متن اصلی الزامی است.',
            'main_text.string'        => 'متن اصلی باید رشته باشد.',
            'main_text.max'           => 'متن اصلی نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'switch_words.array'      => 'کلمات متغیر باید به صورت آرایه باشند.',
            'switch_words.*.string'   => 'هر کلمه متغیر باید رشته باشد.',
            'switch_words.*.max'      => 'هر کلمه متغیر نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
            'switch_interval.integer' => 'فاصله تعویض باید عدد صحیح باشد.',
            'switch_interval.min'     => 'فاصله تعویض باید حداقل ۱ باشد.',
            'image.image'             => 'فایل باید یک تصویر باشد.',
            'image.max'               => 'حجم تصویر نمی‌تواند بیشتر از ۲ مگابایت باشد.',
            'status.required'         => 'وضعیت الزامی است.',
            'status.boolean'          => 'وضعیت باید بله یا خیر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('banners', 'public');
        }

        // فیلتر کردن مقادیر خالی از switch_words
        $filteredSwitchWords = array_filter($this->switch_words, fn ($word) => trim($word) !== '');

        BannerText::create([
            'main_text'       => $this->main_text,
            'switch_words'    => ! empty($filteredSwitchWords) ? $filteredSwitchWords : null,
            'switch_interval' => $this->switch_interval,
            'image_path'      => $imagePath,
            'status'          => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'بنر با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.bannertexts.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.bannertexts.bannertext-create');
    }
}
