<?php

namespace App\Livewire\Admin\Panel\Layouts\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

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
                    $this->userType = 'مدیر عادی';
                    break;
                case 2:
                    $this->userType = 'مدیر ارشد';
                    break;
                default:
                    $this->userType = 'نامشخص';
                    break;
            }
        } else {
            $this->userType = 'نامشخص';
        }
    }

    public function render()
    {
        if (!$this->user) {
            return redirect()->route('admin.login');
        }

        return view('livewire.admin.panel.layouts.partials.admin-sidebar');
    }
}