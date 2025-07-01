<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppointmentController extends Controller
{
    /**
     * لغو نوبت
     * @authenticated
     * @urlParam id integer required شناسه نوبت
     * @response 200 {
     *   "status": "success",
     *   "message": "نوبت با موفقیت لغو شد",
     *   "data": null
     * }
     * @response 400 {
     *   "status": "error",
     *   "message": "نوبت قبلاً لغو شده است",
     *   "data": null
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "کاربر احراز هویت نشده است",
     *   "data": null
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "نوبت یافت نشد",
     *   "data": null
     * }
     */
    public function cancelAppointment(Request $request, $id)
    {
        try {
            // احراز هویت کاربر
            $user = JWTAuth::setToken($request->cookie('auth_token') ?: $request->bearerToken())->authenticate();
            if (! $user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'کاربر احراز هویت نشده است',
                    'data'    => null,
                ], 401);
            }

            // پیدا کردن نوبت
            $appointment = Appointment::where('id', $id)
                ->where('patientable_id', $user->id)
                ->where('patientable_type', get_class($user))
                ->first();

            if (! $appointment) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'نوبت یافت نشد',
                    'data'    => null,
                ], 404);
            }

            // بررسی وضعیت نوبت
            if ($appointment->status === 'cancelled') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'نوبت قبلاً لغو شده است',
                    'data'    => null,
                ], 400);
            }

            if ($appointment->status !== 'scheduled') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'فقط نوبت‌های در انتظار قابل لغو هستند',
                    'data'    => null,
                ], 400);
            }

            // لغو نوبت
            $appointment->status = 'cancelled';
            $appointment->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'نوبت با موفقیت لغو شد',
                'data'    => null,
            ], 200);

        } catch (\Exception $e) {
            Log::error('CancelAppointment - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
    /**
     * گرفتن لیست نوبت‌های کاربر
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "tracking_code": "۱۲۳۴۵۶",
     *       "appointment_date": "1402-05-12",
     *       "appointment_time": "14:30",
     *       "status": "completed",
     *       "consultation_type": "in_person",
     *       "fee": 500000,
     *       "notes": "این نوبت شامل بررسی اولیه و نوار قلب می‌باشد. لطفاً ۱۵ دقیقه زودتر در محل حضور داشته باشید.",
     *       "doctor": {
     *         "id": 1,
     *         "name": "دکتر محمدی",
     *         "specialty": "متخصص قلب و عروق"
     *       },
     *       "clinic": {
     *         "id": 1,
     *         "address": "تهران، میدان آرژانتین، خیابان الوند، جنب بیمارستان کسری، بن بست آفرین، ساختمان پزشکان آفرین، طبقه دوم"
     *       },
     *       "patient": {
     *         "mobile": "09121234567",
     *         "name": "زهرا احمدی"
     *       }
     *     }
     *   ]
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "توکن نامعتبر است",
     *   "data": null
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getAppointments(Request $request)
    {
        try {
            // گرفتن توکن از هدر یا کوکی
            $token = $request->bearerToken() ?: $request->cookie('auth_token');
            if (!$token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن ارائه نشده است',
                    'data'    => null,
                ], 401);
            }

            // احراز هویت کاربر
            try {
                $user = JWTAuth::setToken($token)->authenticate();
                if (!$user) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کاربر یافت نشد',
                        'data'    => null,
                    ], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                Log::error('GetAppointments - JWT Error: ' . $e->getMessage());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن نوبت‌های کاربر
            $appointments = Appointment::where('patientable_id', $user->id)
                ->where('patientable_type', get_class($user))
                ->with([
                    'doctor' => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'specialty_id')
                            ->with(['specialty' => function ($query) {
                                $query->select('id', 'name');
                            }]);
                    },
                    'clinic' => function ($query) {
                        $query->select('id', 'address');
                    },
                    'patientable',
                ])
                ->select('id', 'doctor_id', 'clinic_id', 'patientable_id', 'patientable_type', 'appointment_date', 'appointment_time', 'status', 'consultation_type', 'fee', 'notes', 'tracking_code')
                ->get();

            // فرمت کردن داده‌ها
            $formattedAppointments = $appointments->map(function ($appointment) {
                $patient = $appointment->patientable;
                return [
                    'id' => $appointment->id,
                    'tracking_code' => $appointment->tracking_code ?? 'نامشخص',
                    'appointment_date' => $appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d') : null,
                    'appointment_time' => $appointment->appointment_time ? $appointment->appointment_time->format('H:i:s') : null,
                    'status' => $appointment->status,
                    'consultation_type' => $appointment->consultation_type ?? 'in_person',
                    'fee' => $appointment->fee,
                    'notes' => $appointment->notes,
                    'doctor' => $appointment->doctor ? [
                        'id' => $appointment->doctor->id,
                        'name' => $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name,
                        'specialty' => $appointment->doctor->specialty ? $appointment->doctor->specialty->name : null,
                    ] : null,
                    'clinic' => $appointment->clinic ? [
                        'id' => $appointment->clinic->id,
                        'address' => $appointment->clinic->address,
                    ] : null,
                    'patient' => $patient ? [
                        'mobile' => $patient->mobile ?? null,
                        'name' => ($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''),
                    ] : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formattedAppointments,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetAppointments - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور',
                'data' => null,
            ], 500);
        }
    }

}
