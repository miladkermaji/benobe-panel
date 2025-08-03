<?php

namespace App\Http\Controllers\Mc\Panel\SecretaryPermission;

use App\Http\Controllers\Mc\Controller;
use App\Models\SecretaryPermission;
use App\Models\Secretary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Traits\HasSelectedDoctor;

class SecretaryPermissionController extends Controller
{
    use HasSelectedDoctor;

    public function index(Request $request)
    {
        if (Auth::guard('medical_center')->check()) {
            $medicalCenterId = Auth::guard('medical_center')->id();
            $doctorId = $this->getSelectedDoctorId();
        } else {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $medicalCenterId = $this->getSelectedMedicalCenterId();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
        }

        if (!$doctorId) {
            return redirect()->route('dr.auth.login-register-form');
        }

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

        return view('mc.panel.secretary_permissions.index', compact('secretaries', 'permissions'));
    }

    public function update(Request $request, $secretaryId)
    {
        if (Auth::guard('medical_center')->check()) {
            $medicalCenterId = Auth::guard('medical_center')->id();
            $doctorId = $this->getSelectedDoctorId();
        } else {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            $medicalCenterId = $this->getSelectedMedicalCenterId();
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
        }

        if (!$doctorId) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه‌ی این عملیات را ندارید.',
            ], 403);
        }

        $request->validate([
            'permissions' => 'array',
        ]);

        // یافتن دسترسی موجود بر اساس doctor_id, secretary_id و medical_center_id
        $permission = SecretaryPermission::where('doctor_id', $doctorId)
            ->where('secretary_id', $secretaryId)
            ->where(function ($query) use ($medicalCenterId) {
                if ($medicalCenterId) {
                    $query->where('medical_center_id', $medicalCenterId);
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
                'medical_center_id'    => $medicalCenterId,
                'permissions'  => json_encode($request->permissions),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'دسترسی‌های منشی با موفقیت ویرایش شد.',
        ]);
    }
}
