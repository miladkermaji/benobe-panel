<?php
namespace App\Http\Controllers\Dr\Panel\SecretaryPermission;

use App\Http\Controllers\Dr\Controller;
use App\Models\SecretaryPermission;
use Illuminate\Http\Request;

class SecretaryPermissionController extends Controller
{
    public function index(Request $request)
    {
        $doctor   = auth()->guard('doctor')->user();
        $clinicId = $request->input('selectedClinicId') === 'default' ? null : $request->input('selectedClinicId');

        if (! $doctor) {
            return redirect()->route('dr.auth.login-register-form');
        }

        // دریافت منشی‌ها با توجه به کلینیک
        $secretaries = $doctor->secretaries()
            ->with('permissions')
            ->when($clinicId !== null, function ($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })
            ->when($clinicId === null, function ($query) {
                $query->whereNull('clinic_id');
            })
            ->get();

        // دریافت دسترسی‌ها
        $permissions = config('permissions');

        if ($request->ajax()) {
            return response()->json(['secretaries' => $secretaries]);
        }

        return view('dr.panel.secretary_permissions.index', compact('secretaries', 'permissions'));
    }

    public function update(Request $request, $secretaryId)
    {
        $doctor   = auth()->guard('doctor')->user();
        $clinicId = $request->input('selectedClinicId') === 'default' ? null : $request->input('selectedClinicId');

        if (! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه‌ی این عملیات را ندارید.',
            ], 403);
        }

        $request->validate([
            'permissions' => 'array',
        ]);

        // یافتن دسترسی موجود بر اساس doctor_id, secretary_id و clinic_id
        $permission = SecretaryPermission::where('doctor_id', $doctor->id)
            ->where('secretary_id', $secretaryId)
            ->where(function ($query) use ($clinicId) {
                if ($clinicId) {
                    $query->where('clinic_id', $clinicId);
                } else {
                    $query->whereNull('clinic_id');
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
                'doctor_id'    => $doctor->id,
                'secretary_id' => $secretaryId,
                'clinic_id'    => $clinicId,
                'permissions'  => json_encode($request->permissions),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'دسترسی‌های منشی با موفقیت ویرایش شد.',
        ]);
    }

}
