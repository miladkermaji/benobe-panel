<?php

namespace App\Http\Controllers\Admin\Panel;

use App\Http\Controllers\Admin\Controller;
use App\Models\MedicalCenter;
use App\Models\MedicalCenterPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalCenterPermissionController extends Controller
{
    public function index(Request $request)
    {
        $medicalCenters = MedicalCenter::with('permissions')->paginate(15);
        $permissions = config('medical-center-permissions');

        if ($request->ajax()) {
            return response()->json(['medical_centers' => $medicalCenters]);
        }

        return view('admin.panel.medical_centers.permissions.index', compact('medicalCenters', 'permissions'));
    }

    public function show($id)
    {
        $medicalCenter = MedicalCenter::with('permissions')->findOrFail($id);
        $permissions = config('medical-center-permissions');

        return view('admin.panel.medical_centers.permissions.show', compact('medicalCenter', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $medicalCenter = MedicalCenter::findOrFail($id);

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
