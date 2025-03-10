<?php

namespace App\Livewire\Dr\Auth;

use Livewire\Component;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Auth;

class DoctorLogout extends Component
{
    public function logout()
    {
        $user = null;
        $guard = null;

        if (Auth::guard('doctor')->check()) {
            $user = Auth::guard('doctor')->user();
            $guard = 'doctor';
            Auth::guard('doctor')->logout();
        } elseif (Auth::guard('secretary')->check()) {
            $user = Auth::guard('secretary')->user();
            $guard = 'secretary';
            Auth::guard('secretary')->logout();
        }

        if ($user) {
            LoginLog::where('doctor_id', $guard === 'doctor' ? $user->id : null)
                ->where('secretary_id', $guard === 'secretary' ? $user->id : null)
                ->whereNull('logout_at')
                ->latest()
                ->first()
                    ?->update(['logout_at' => now()]);
        }

        session()->forget(['current_step', 'step1_completed', 'step3_completed', 'otp_token', 'doctor_temp_login', 'secretary_temp_login']);
        session()->flash('swal-success', 'شما با موفقیت از سایت خارج شدید');
        $this->redirect(route('dr.auth.login-register-form'), navigate: true);
    }

    public function render()
    {
        return view('livewire.dr.auth.doctor-logout')
            ->layout('dr.layouts.dr-auth');
    }
}