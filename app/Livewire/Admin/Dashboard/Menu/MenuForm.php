<?php

namespace App\Livewire\Admin\Dashboard\Menu;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Admin\Dashboard\Menu\Menu;

class MenuForm extends Component
{
    public $menuId, $name, $url, $icon, $position = 'top', $parent_id, $order = 0, $status = 1;
    public $successMessage = '';
    use WithFileUploads;

    protected $rules = [
        'name' => 'required|string|max:255',
        'url' => 'nullable|string|max:255',
        'icon' => 'nullable|string|max:255',
        'position' => 'required|in:top,bottom,top_bottom',
        'parent_id' => 'nullable|exists:menus,id',
        'order' => 'integer|min:0',
        'status' => 'boolean',
    ];

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|image|max:2048', // بررسی آپلود فایل
            'position' => 'required|string',
            'order' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);

        if ($this->icon) {
            $iconPath = $this->icon->store('uploads/menu/icons', 'public'); // ذخیره عکس در storage
        } else {
            $iconPath = null;
        }

        Menu::create([
            'name' => $this->name,
            'url' => $this->url,
            'icon' => $iconPath,
            'position' => $this->position,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'status' => $this->status,
        ]);

        $this->reset(['name', 'url', 'icon', 'position', 'parent_id', 'order', 'status']);
        $this->successMessage = 'منو با موفقیت اضافه شد!';
        $this->dispatch('menuAdded');
    }




    public function render()
    {
        return view('livewire.admin.dashboard.menu.menu-form', [
            'menus' => Menu::whereNull('parent_id')->get(),
        ]);
    }
}

