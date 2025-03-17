<?php
namespace App\Livewire\Admin\Panel\Menus;

use App\Models\Admin\Dashboard\Menu\Menu;
use Livewire\Component;
use Livewire\WithFileUploads;

class MenuEdit extends Component
{
    use WithFileUploads;

    public $menu;
    public $name;
    public $url;
    public $icon; // فایل جدید آپلودشده
    public $position;
    public $parent_id;
    public $order;
    public $status;
    public $successMessage;

    protected $rules = [
        'name'      => 'required|string|max:255',
        'url'       => 'nullable|string|max:255',
        'icon'      => 'nullable|image|max:2048',
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

    public function mount($id)
    {
        $this->menu      = Menu::findOrFail($id);
        $this->name      = $this->menu->name;
        $this->url       = $this->menu->url;
        $this->icon      = null; // فایل جدید، مقدار اولیه null
        $this->position  = $this->menu->position ?? 'top';
        $this->parent_id = $this->menu->parent_id ?? null;
        $this->order     = $this->menu->order ?? 0;
        $this->status    = $this->menu->status ?? 1;
    }

    public function update()
    {
        $this->validate($this->rules, $this->messages);

        $parentId = $this->parent_id === '' ? null : $this->parent_id;
        $iconPath = $this->icon ? $this->icon->store('menu/icons', 'public') : $this->menu->icon; // اگر فایل جدید آپلود شده باشد، جایگزین می‌شود

        $this->menu->update([
            'name'      => $this->name,
            'url'       => $this->url,
            'icon'      => $iconPath,
            'position'  => $this->position,
            'parent_id' => $parentId,
            'order'     => $this->order,
            'status'    => $this->status,
        ]);

        $this->successMessage = 'منو با موفقیت به‌روزرسانی شد!';
        $this->dispatch('menuUpdated');
    }

    public function render()
    {
        $items = Menu::all();
        return view('livewire.admin.panel.menus.menu-edit', ['menus' => $items]);
    }
}
