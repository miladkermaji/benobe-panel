<?php

namespace App\Livewire\Admin\Panel\Menus;

use App\Models\Admin\Dashboard\Menu\Menu;
use Livewire\Component;
use Livewire\WithFileUploads;

class MenuCreate extends Component
{
    use WithFileUploads;

    public $name;
    public $url;
    public $icon; // این باید یک فایل موقت باشد تا آپلود شود
    public $position  = 'top';
    public $parent_id = null;
    public $order     = 0;
    public $status    = 1;
    public $successMessage;

    protected $rules = [
        'name'      => 'required|string|max:255',
        'url'       => 'nullable|string|max:255',
        'icon'      => 'nullable|image|max:2048', // حداکثر 2 مگابایت
        'position'  => 'required|in:top,bottom,top_bottom',
        'parent_id' => 'nullable|exists:menus,id',
        'order'     => 'nullable|integer|min:0',
        'status'    => 'required|boolean',
    ];

    protected $messages = [
        'name.required'     => 'نام منو الزامی است.',
        'name.string'       => 'نام منو باید یک رشته باشد.',
        'name.max'          => 'نام منو نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        'url.string'        => 'لینک منو باید یک رشته باشد.',
        'url.max'           => 'لینک منو نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        'icon.image'        => 'فایل آیکون باید یک تصویر باشد.',
        'icon.max'          => 'حجم آیکون نمی‌تواند بیشتر از ۲ مگابایت باشد.',
        'position.required' => 'جایگاه منو الزامی است.',
        'position.in'       => 'جایگاه منو باید یکی از مقادیر بالا، پایین یا هر دو باشد.',
        'parent_id.exists'  => 'زیرمجموعه انتخاب‌شده معتبر نیست.',
        'order.integer'     => 'ترتیب باید یک عدد صحیح باشد.',
        'order.min'         => 'ترتیب نمی‌تواند کمتر از ۰ باشد.',
        'status.required'   => 'وضعیت منو الزامی است.',
        'status.boolean'    => 'وضعیت منو باید فعال یا غیرفعال باشد.',
    ];

    public function store()
    {
        $this->validate($this->rules, $this->messages);

        $parentId = $this->parent_id === '' ? null : $this->parent_id;
        $iconPath = $this->icon ? $this->icon->store('menu/icons', 'public') : null; // ذخیره در storage/public/menu/icons

        Menu::create([
            'name'      => $this->name,
            'url'       => $this->url,
            'icon'      => $iconPath, // مسیر فایل ذخیره‌شده
            'position'  => $this->position,
            'parent_id' => $parentId,
            'order'     => $this->order,
            'status'    => $this->status,
        ]);

        $this->reset(['name', 'url', 'icon', 'position', 'parent_id', 'order', 'status']);
        $this->successMessage = 'منو با موفقیت اضافه شد!';
        $this->dispatch('menuAdded');
    }

    public function render()
    {
        $items = Menu::all();
        return view('livewire.admin.panel.menus.menu-create', ['menus' => $items]);
    }
}
