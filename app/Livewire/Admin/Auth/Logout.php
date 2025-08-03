<?php

namespace App\Livewire\Admin\Auth;

use Livewire\Component;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Auth;

class Logout extends Component
{
    public function logout()
    {
        $user = null;
        $guard = null;

        if (Auth::guard('manager')->check()) {
            $user = Auth::guard('manager')->user();
            $guard = 'manager';
            Auth::guard('manager')->logout();
        }

        if ($user) {
            LoginLog::where('loggable_type', get_class($user))
                ->where('loggable_id', $user->id)
                ->whereNull('logout_at')
                ->latest()
                ->first()
                    ?->update(['logout_at' => now()]);
        }

        session()->forget(['current_step', 'step1_completed', 'step3_completed', 'otp_token', 'manager_temp_login']);
        session()->flash('swal-success', 'شما با موفقیت از سایت خارج شدید');
        $this->redirect(route('admin.auth.login-register-form'));
    }

    public function render()
    {
        return view('livewire.admin.auth.logout')
            ->layout('admin.layouts.admin-auth');
    }
}
