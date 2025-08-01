<?php

namespace App\Http\Controllers\Dr\Panel\SecretaryPermission;

use App\Http\Controllers\Dr\Controller;
use App\Models\SecretaryPermission;
use App\Models\Secretary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;

class SecretaryPermissionController extends Controller
{
    public function index(Request $request)
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $medicalCenterId = $this->getSelectedMedicalCenterId();

        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form');
        }

        // اگر کاربر منشی است، از doctor_id آن استفاده می‌کنیم
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        $secretaries = Secretary::where('doctor_id', $doctorId)
            ->with('permissions')
            ->when($medicalCenterId !== null, function ($query) use ($medicalCenterId) {
                $query->where('medical_center_id', $medicalCenterId);
            })
            ->when($medicalCenterId === null, function ($query) {
                $query->whereNull('medical_center_id');
            })
            ->get();

        $permissions = config('permissions');

        if ($request->ajax()) {
            return response()->json(['secretaries' => $secretaries]);
        }

        return view('dr.panel.secretary_permissions.index', compact('secretaries', 'permissions'));
    }

    public function update(Request $request, $secretaryId)
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $clinicId = $this->getSelectedMedicalCenterId();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه‌ی این عملیات را ندارید.',
            ], 403);
        }

        $request->validate([
            'permissions' => 'array',
        ]);

        // اگر کاربر منشی است، از doctor_id آن استفاده می‌کنیم
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        // یافتن دسترسی موجود بر اساس doctor_id, secretary_id و medical_center_id
        $permission = SecretaryPermission::where('doctor_id', $doctorId)
            ->where('secretary_id', $secretaryId)
            ->where(function ($query) use ($clinicId) {
                if ($clinicId) {
                    $query->where('medical_center_id', $clinicId);
                } else {
                    $query->whereNull('medical_center_id');
                }
            })->first();

        // اگر وجود داشت، ویرایش کن
        if ($permission) {
            $permission->update([
                'permissions' => json_encode($request->permissions),
            ]);
        } else {
            // اگر نبود، ایجاد کن
            SecretaryPermission::create([
                'doctor_id'    => $doctorId,
                'secretary_id' => $secretaryId,
                'medical_center_id'    => $clinicId,
                'permissions'  => json_encode($request->permissions),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'دسترسی‌های منشی با موفقیت ویرایش شد.',
        ]);
    }
}
