<?php

namespace App\Http\Controllers\Mc\Panel\Profile;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Mc\Controller;
use Illuminate\Support\Facades\Auth;

class LoginLogsController extends Controller
{
    public function security()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('mc.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }

        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        // دریافت لاگ‌های دکتر و منشی برای بارگذاری اولیه صفحه
        $doctorLogs = LoginLog::where('doctor_id', $doctorId)->orderBy('login_at', 'desc')->paginate(5);
        $secretaryIds = $doctor->secretaries ? $doctor->secretaries->pluck('id')->toArray() : [];
        $secretaryLogs = LoginLog::whereIn('secretary_id', $secretaryIds)->orderBy('login_at', 'desc')->paginate(5);

        return view("mc.panel.profile.security", compact('doctorLogs', 'secretaryLogs'));
    }


    public function getDoctorLogs(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        $doctorLogs = LoginLog::where('doctor_id', $doctor->id)->orderBy('login_at', 'desc')->paginate(5);

        return response()->json([
         'doctorLogsHtml' => view('mc.panel.profile.partials.doctor_logs', compact('doctorLogs'))->render()
        ]);
    }


    public function getSecretaryLogs(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        $secretaryIds = $doctor->secretaries ? $doctor->secretaries->pluck('id')->toArray() : [];
        $secretaryLogs = LoginLog::whereIn('secretary_id', $secretaryIds)->orderBy('login_at', 'desc')->paginate(5);

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

        $log->delete();

        return response()->json(['success' => true, 'message' => 'لاگ با موفقیت حذف شد']);
    }
}
