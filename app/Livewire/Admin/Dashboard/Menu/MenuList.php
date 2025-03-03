<?php

namespace App\Livewire\Admin\Dashboard\Menu;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Dashboard\Menu\Menu;

class MenuList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedRows = [];
    public $selectAll = false;

    protected $listeners = ['updateDeleteButton' => 'checkIfRowsSelected', 'deleteSelectedMenus' => 'deleteSelected'];
    


    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = Menu::pluck('id')->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function checkIfRowsSelected()
    {
        $this->selectAll = count($this->selectedRows) === Menu::count();
    }

    public function deleteSelected()
    {
        if (count($this->selectedRows) > 0) {
            $menus = Menu::whereIn('id', $this->selectedRows)->get();

            foreach ($menus as $menu) {
                // بررسی و حذف فایل آیکون اگر وجود داشته باشد
                if ($menu->icon && file_exists(storage_path('app/public/uploads/menu/icons/' . basename($menu->icon)))) {
                    unlink(storage_path('app/public/uploads/menu/icons/' . basename($menu->icon)));
                }
            }

            // حذف منوها از دیتابیس
            Menu::whereIn('id', $this->selectedRows)->delete();

            // ریست کردن انتخاب‌ها
            $this->selectedRows = [];
            $this->selectAll = false;

            // ارسال رویداد برای غیرفعال کردن دکمه حذف
            $this->dispatch('refreshDeleteButton', hasSelectedRows: false);

            // ارسال پیام موفقیت
            $this->dispatch('show-toastr', type: 'success', message: 'منوهای انتخاب‌شده با موفقیت حذف شدند.');
        }
    }

    public function toggleStatus($id)
    {
        $city = Menu::find($id);
        $city->status = !$city->status;
        $city->save();

        $this->dispatch('show-toastr', type: 'success', message: 'وضعیت منو با موفقیت تغییر کرد.');
    }





    public function searchUpdated()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.dashboard.menu.menu-list', [
            'menus' => Menu::where('name', 'like', "%{$this->search}%")->paginate(10),
        ]);
    }
   
}
