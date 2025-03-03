<?php

namespace App\Livewire\Admin\Dashboard\Menu;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Admin\Dashboard\Menu\Menu;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Edit extends Component
{
 use WithFileUploads;

 public $menuId; // ID منوی در حال ویرایش
 public $name; // نام منو
 public $url; // لینک منو
 public $icon; // آیکون جدید
 public $currentIcon; // آیکون فعلی
 public $position = 'top'; // جایگاه منو
 public $parent_id; // زیرمجموعه
 public $order = 0; // ترتیب
 public $status = 1; // وضعیت
 public $successMessage = ''; // پیام موفقیت

 protected $rules = [
  'name' => 'required|string|max:255',
  'url' => 'nullable|string|max:255',
  'icon' => 'nullable|image|max:2048', // آیکون جدید، حداکثر 2MB
  'position' => 'required|in:top,bottom,top_bottom',
  'parent_id' => 'nullable|exists:menus,id',
  'order' => 'integer|min:0',
  'status' => 'boolean',
 ];

 public function mount($menuId)
 {
  $this->menuId = $menuId;
  $menu = Menu::findOrFail($menuId);
  $this->name = $menu->name;
  $this->url = $menu->url;
  $this->currentIcon = $menu->icon;
  $this->position = $menu->position;
  $this->parent_id = $menu->parent_id;
  $this->order = $menu->order;
  $this->status = $menu->status;
  Log::info('EditMenu mounted', ['menu' => $menu]);
 }

 public function update()
 {
  $this->validate();

  try {
   $menu = Menu::findOrFail($this->menuId);

   // مدیریت آیکون
   if ($this->icon) {
    // حذف آیکون قبلی اگه وجود داره
    if ($menu->icon) {
     Storage::disk('public')->delete($menu->icon);
     Log::info('Previous icon deleted', ['path' => $menu->icon]);
    }
    // ذخیره آیکون جدید
    $iconPath = $this->icon->store('uploads/menu/icons', 'public');
   } else {
    $iconPath = $menu->icon; // نگه داشتن آیکون فعلی
   }

   // آپدیت منو
   $menu->update([
    'name' => $this->name,
    'url' => $this->url,
    'icon' => $iconPath,
    'position' => $this->position,
    'parent_id' => $this->parent_id,
    'order' => $this->order,
    'status' => $this->status,
   ]);

   Log::info('Menu updated', ['menu_id' => $this->menuId]);

   $this->successMessage = 'منو با موفقیت ویرایش شد!';
   $this->dispatch('menuUpdated'); // رویداد برای بستن الرت

  } catch (\Exception $e) {
   Log::error('Error updating menu:', [
    'message' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
   ]);
   $this->dispatch('toast', 'خطا در ویرایش منو: ' . $e->getMessage(), [
    'type' => 'error',
    'position' => 'top-right',
    'timeOut' => 3000,
    'progressBar' => true,
   ]);
  }
 }

 public function render()
 {
  return view('livewire.admin.dashboard.menu.edit', [
   'menus' => Menu::whereNull('parent_id')->get(),
  ]);
 }
}