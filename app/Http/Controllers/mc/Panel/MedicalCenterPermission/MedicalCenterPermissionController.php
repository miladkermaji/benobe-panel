<?php

namespace App\Http\Controllers\Mc\Panel\MedicalCenterPermission;

use App\Http\Controllers\Mc\Controller;
use App\Models\MedicalCenterPermission;
use App\Models\MedicalCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalCenterPermissionController extends Controller
{
    public function index(Request $request)
    {
        $medicalCenter = Auth::guard('medical_center')->user();

        if (!$medicalCenter) {
            return redirect()->route('dr.auth.login-register-form');
        }

        $permissions = config('medical-center-permissions');

        if ($request->ajax()) {
            return response()->json(['medical_center' => $medicalCenter]);
        }

        return view('mc.panel.medical_center_permissions.index', compact('medical_center', 'permissions'));
    }

    public function update(Request $request)
    {
        $medicalCenter = Auth::guard('medical_center')->user();

        if (!$medicalCenter) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه‌ی این عملیات را ندارید.',
            ], 403);
        }

        $request->validate([
            'permissions' => 'array',
        ]);

        // یافتن دسترسی موجود
        $permission = MedicalCenterPermission::where('medical_center_id', $medicalCenter->id)->first();

        // اگر وجود داشت، ویرایش کن
        if ($permission) {
            $permission->update([
                'permissions' => $request->permissions,
            ]);
        } else {
            // اگر نبود، ایجاد کن
            MedicalCenterPermission::create([
                'medical_center_id' => $medicalCenter->id,
                'permissions' => $request->permissions,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'دسترسی‌های مرکز درمانی با موفقیت ویرایش شد.',
        ]);
    }
}
