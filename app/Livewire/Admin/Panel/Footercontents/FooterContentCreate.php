<?php

namespace App\Livewire\Admin\Panel\FooterContents;

use App\Models\FooterContent;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class FooterContentCreate extends Component
{
    use WithFileUploads;

    public $section;
    public $title;
    public $description;
    public $link_url;
    public $link_text;
    public $icon;
    public $image;
    public $order = 0;
    public $is_active = true;

    public function store()
    {
        $validator = Validator::make($this->all(), [
            'section'     => 'required|string|max:255',
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'link_url'    => 'nullable|url',
            'link_text'   => 'nullable|string|max:255',
            'icon'        => 'nullable|image|max:2048',
            'image'       => 'nullable|image|max:2048',
            'order'       => 'required|integer|min:0',
            'is_active'   => 'boolean',
        ], [
            'section.required' => 'بخش الزامی است.',
            'section.max'      => 'بخش نباید بیشتر از ۲۵۵ حرف باشد.',
            'title.max'        => 'عنوان نباید بیشتر از ۲۵۵ حرف باشد.',
            'description.max'  => 'توضیحات نباید بیشتر از ۱۰۰۰ حرف باشد.',
            'link_url.url'     => 'لینک باید یک URL معتبر باشد.',
            'link_text.max'    => 'متن لینک نباید بیشتر از ۲۵۵ حرف باشد.',
            'icon.image'       => 'فایل آیکن باید تصویر باشد.',
            'icon.max'         => 'حجم آیکن نباید بیشتر از ۲ مگابایت باشد.',
            'image.image'      => 'فایل تصویر باید تصویر باشد.',
            'image.max'        => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'order.required'   => 'ترتیب الزامی است.',
            'order.integer'    => 'ترتیب باید عدد صحیح باشد.',
            'order.min'        => 'ترتیب نمی‌تواند منفی باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $data = $validator->validated();
        if ($this->icon) {
            $data['icon_path'] = $this->icon->store('footer', 'public');
        }
        if ($this->image) {
            $data['image_path'] = $this->image->store('footer', 'public');
        }

        FooterContent::create($data);

        $this->dispatch('show-alert', type: 'success', message: 'آیتم فوتر با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.footercontents.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.footercontents.footercontent-create');
    }
}
