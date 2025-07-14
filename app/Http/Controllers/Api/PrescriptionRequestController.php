<?php

namespace App\Http\Controllers\Api;

use App\Models\PrescriptionRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PrescriptionRequestController extends Controller
{
    // لیست نسخه‌های من (کاربر لاگین شده)
    public function myPrescriptions(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'کاربر احراز هویت نشده است',
                'data' => null,
            ], 401);
        }
        $prescriptions = $user->prescriptions()->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $prescriptions,
        ]);
    }

    // ثبت درخواست نسخه جدید
    public function requestPrescription(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'کاربر احراز هویت نشده است',
                'data' => null,
            ], 401);
        }
        $validated = $request->validate([
            // 'type' => 'required|string', // فعلاً نیاز نیست
            'description' => 'required|string',
            'doctor_id' => 'nullable|exists:doctors,id',
            'insurance_id' => 'nullable|exists:insurances,id',
            'price' => 'nullable|integer',
        ]);
        $prescription = $user->prescriptions()->create([
            // 'type' => $validated['type'],
            'description' => $validated['description'],
            'doctor_id' => $validated['doctor_id'] ?? null,
            'insurance_id' => $validated['insurance_id'] ?? null,
            'price' => $validated['price'] ?? null,
            'tracking_code' => uniqid('RX-'),
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'درخواست نسخه با موفقیت ثبت شد',
            'data' => $prescription,
        ], 201);
    }
}
