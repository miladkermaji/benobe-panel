<?php

namespace App\Livewire\Admin\Panel\Layouts\Partials;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminSidebar extends Component
{
    public $user;
    public $userType;

    public function mount()
    {
        $this->user = Auth::guard('manager')->user();

        // تعیین نوع کاربر بر اساس سطح دسترسی
        if ($this->user) {
            switch ($this->user->permission_level) {
                case 1:
                    $this->userType = 'مدیر ارشد';
                    break;
                case 2:
                    $this->userType = 'مدیر عادی';
                    break;
                default:
                    $this->userType = 'نامشخص';
                    break;
            }
        } else {
            $this->userType = 'نامشخص';
        }
    }

    /**
     * بررسی دسترسی مدیر به بخش خاص
     */
    public function hasManagerPermission($permissionKey)
    {
        if (!$this->user) {
            return false;
        }

        // مدیر ارشد همه دسترسی‌ها را دارد
        if ($this->user->permission_level == 1) {
            return true;
        }

        // مدیر عادی باید دسترسی داشته باشد
        if ($this->user->permission_level == 2) {
            // استفاده از متد getPermissionsAttribute که خودکار دسترسی‌های پیش‌فرض را ایجاد می‌کند
            $permissions = $this->user->permissions->permissions ?? [];

            // بررسی دسترسی مستقیم
            if (in_array($permissionKey, $permissions)) {
                return true;
            }

            // بررسی دسترسی‌های گروهی
            $permissionsConfig = config('admin-permissions');
            if (isset($permissionsConfig[$permissionKey])) {
                $permissionData = $permissionsConfig[$permissionKey];
                if (isset($permissionData['routes'])) {
                    foreach ($permissionData['routes'] as $route => $title) {
                        if (in_array($route, $permissions)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function render()
    {
        if (! $this->user) {
            return redirect()->route('admin.auth.login-register-form');
        }

        return view('livewire.admin.panel.layouts.partials.admin-sidebar');
    }
}
