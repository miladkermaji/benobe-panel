<?php

namespace App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Cost;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dr\ClinicDepositSetting;

class CostController
{
    public function index($clinicId)
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $averageDeposit = ClinicDepositSetting::whereNotNull('deposit_amount')->avg('deposit_amount'); // میانگین بیعانه

        return view('dr.panel.doctors-clinic.activation.cost.index', compact('clinicId', 'doctorId', 'averageDeposit'));
    }

    public function listDeposits($clinicId)
    {
        $deposits = ClinicDepositSetting::where('clinic_id', $clinicId)
            ->get(['id', 'deposit_amount']);

        return response()->json($deposits);
    }
    public function deleteDeposit(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:clinic_deposit_settings,id',
        ]);

        $deposit = ClinicDepositSetting::findOrFail($request->id);
        $deposit->delete();

        return response()->json(['success' => true, 'message' => 'بیعانه با موفقیت حذف شد.']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'doctor_id' => 'required|exists:users,id',
            'deposit_amount' => 'nullable|numeric',
            'is_custom_price' => 'required|boolean',
        ]);

        // بررسی وجود بیعانه برای کلینیک و دکتر
        $existingDeposit = ClinicDepositSetting::where('clinic_id', $request->clinic_id)
            ->where('doctor_id', $request->doctor_id)
            ->first();

        if ($existingDeposit) {
            return response()->json(['success' => false, 'message' => 'شما قبلاً یک بیعانه برای این کلینیک ثبت کرده‌اید. لطفاً ابتدا آن را حذف کنید.']);
        }

        $setting = ClinicDepositSetting::create([
            'clinic_id' => $request->clinic_id,
            'doctor_id' => $request->doctor_id,
            'deposit_amount' => $request->deposit_amount,
            'is_custom_price' => $request->is_custom_price,
        ]);

        return response()->json(['success' => true, 'message' => 'تنظیمات بیعانه با موفقیت ذخیره شد.']);
    }


}
