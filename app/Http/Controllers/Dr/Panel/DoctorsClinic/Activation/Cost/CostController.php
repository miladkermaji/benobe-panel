<?php

namespace App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Cost;

use App\Http\Controllers\Dr\Controller;
use App\Models\ClinicDepositSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostController extends Controller
{
    public function index($clinicId)
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
        $averageDeposit = ClinicDepositSetting::whereNotNull('deposit_amount')->avg('deposit_amount'); // میانگین بیعانه

        return view('dr.panel.doctors-clinic.activation.cost.index', compact(['clinicId', 'doctorId', 'averageDeposit']));
    }

    public function listDeposits($clinicId)
    {
        $deposits = ClinicDepositSetting::where('medical_center_id', $clinicId)
            ->get(['id', 'deposit_amount']);

        return response()->json($deposits);
    }

    public function deleteDeposit(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:medical_center_deposit_settings,id',
        ]);

        $deposit = ClinicDepositSetting::findOrFail($request->id);
        $deposit->delete();

        return response()->json(['success' => true, 'message' => 'بیعانه با موفقیت حذف شد.']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'medical_center_id'       => 'required|exists:medical_centers,id',
            'doctor_id'       => 'required|exists:doctors,id',
            'deposit_amount'  => 'nullable|numeric|min:0',
            'is_custom_price' => 'required|boolean',
            'no_deposit'      => 'nullable|boolean',
        ]);

        // اگر کاربر بدون بیعانه را انتخاب کرده باشد یا مقدار صفر باشد
        $depositAmount = $request->no_deposit || !$request->deposit_amount ? 0 : $request->deposit_amount;

        // بررسی وجود بیعانه برای کلینیک و دکتر
        $existingDeposit = ClinicDepositSetting::where('medical_center_id', $request->medical_center_id)
            ->where('doctor_id', $request->doctor_id)
            ->first();

        if ($existingDeposit) {
            // آپدیت بیعانه موجود
            $existingDeposit->update([
                'deposit_amount'  => $depositAmount,
                'is_custom_price' => $request->is_custom_price && $depositAmount > 0,
            ]);
        } else {
            // ایجاد بیعانه جدید
            ClinicDepositSetting::create([
                'medical_center_id' => $request->medical_center_id,
                'doctor_id'         => $request->doctor_id,
                'deposit_amount'    => $depositAmount,
                'is_custom_price'   => $request->is_custom_price && $depositAmount > 0,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'تنظیمات با موفقیت ذخیره شد.']);
    }
}
