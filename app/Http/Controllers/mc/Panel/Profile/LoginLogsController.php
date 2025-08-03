<?php

namespace App\Http\Controllers\Mc\Panel\Profile;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Mc\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasSelectedDoctor;

class LoginLogsController extends Controller
{
    use HasSelectedDoctor;

    public function security()
    {
        if (Auth::guard('medical_center')->check()) {
            $doctorId = $this->getSelectedDoctorId();
            if (!$doctorId) {
                return redirect()->back()->with('error', 'لطفاً ابتدا یک پزشک انتخاب کنید.');
            }
            $doctor = \App\Models\Doctor::find($doctorId);
        } else {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            if (!$doctor) {
                return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
            }
        }

        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        // دریافت لاگ‌های دکتر و منشی برای بارگذاری اولیه صفحه
        $doctorLogs = LoginLog::where('loggable_type', \App\Models\Doctor::class)
            ->where('loggable_id', $doctorId)
            ->orderBy('login_at', 'desc')
            ->paginate(5);

        $secretaryIds = $doctor->secretaries ? $doctor->secretaries->pluck('id')->toArray() : [];
        $secretaryLogs = LoginLog::where('loggable_type', \App\Models\Secretary::class)
            ->whereIn('loggable_id', $secretaryIds)
            ->orderBy('login_at', 'desc')
            ->paginate(5);

        return view("mc.panel.profile.security", compact('doctorLogs', 'secretaryLogs'));
    }


    public function getDoctorLogs(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        $doctorLogs = LoginLog::where('loggable_type', \App\Models\Doctor::class)
            ->where('loggable_id', $doctor->id)
            ->orderBy('login_at', 'desc')
            ->paginate(5);

        return response()->json([
            'doctorLogsHtml' => view('mc.panel.profile.partials.doctor_logs', compact('doctorLogs'))->render()
        ]);
    }


    public function getSecretaryLogs(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        $secretaryIds = $doctor->secretaries ? $doctor->secretaries->pluck('id')->toArray() : [];
        $secretaryLogs = LoginLog::where('loggable_type', \App\Models\Secretary::class)
            ->whereIn('loggable_id', $secretaryIds)
            ->orderBy('login_at', 'desc')
            ->paginate(5);

        return response()->json([
            'secretaryLogsHtml' => view('mc.panel.profile.partials.secretary_logs', compact('secretaryLogs'))->render()
        ]);
    }




    public function deleteLog($id)
    {
        $log = LoginLog::find($id);

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'لاگ یافت نشد'], 404);
        }

        // بررسی دسترسی: فقط لاگ‌های مربوط به پزشک انتخاب‌شده قابل حذف است
        if (Auth::guard('medical_center')->check()) {
            $doctorId = $this->getSelectedDoctorId();
            if (!$doctorId || $log->loggable_id != $doctorId) {
                return response()->json(['success' => false, 'message' => 'شما اجازه حذف این لاگ را ندارید'], 403);
            }
        } else {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            if ($log->loggable_id != $doctorId) {
                return response()->json(['success' => false, 'message' => 'شما اجازه حذف این لاگ را ندارید'], 403);
            }
        }

        $log->delete();

        return response()->json(['success' => true, 'message' => 'لاگ با موفقیت حذف شد']);
    }
}
