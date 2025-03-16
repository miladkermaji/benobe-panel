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
                ->where('patient_id', $user->id)
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
     * @authenticated
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "doctor": {
     *         "id": 1,
     *         "name": "دکتر محمدی",
     *         "specialty": "متخصص قلب و عروق",
     *         "license_number": "۱۲۳۴۵۶",
     *         "profile_photo": "https://example.com/photos/doctor1.jpg"
     *       },
     *       "clinic": {
     *         "name": "کلینیک تهران",
     *         "address": "خیابان ولیعصر"
     *       },
     *       "appointment_date": "۱۴۰۲/۰۵/۱۲",
     *       "start_time": "۱۴:۳۰:۰۰",
     *       "fee": 500000,
     *       "notes": "این نوبت شامل بررسی اولیه و نوار قلب می‌باشد.",
     *       "status": "scheduled"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "کاربر احراز هویت نشده است",
     *   "data": null
     * }
     */

   public function getAppointments(Request $request)
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

        // گرفتن نوبت‌های کاربر
        $appointments = Appointment::where('patient_id', $user->id)
            ->with([
                'doctor' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'license_number', 'profile_photo_path', 'specialty_id')
                        ->with(['specialty' => function ($query) {
                            $query->select('id', 'name');
                        }]);
                },
                'clinic' => function ($query) {
                    $query->select('id', 'name', 'address');
                },
            ])
            ->select('id', 'doctor_id', 'clinic_id', 'appointment_date', 'start_time', 'fee', 'notes', 'status')
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date', 'desc')
            ->get();

        // فرمت کردن داده‌ها
        $formattedAppointments = $appointments->map(function ($appointment) {
            return [
                'id'               => $appointment->id,
                'doctor'           => [
                    'id'             => $appointment->doctor->id,
                    'name'           => $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name,
                    'specialty'      => $appointment->doctor->specialty ? $appointment->doctor->specialty->name : null,
                    'license_number' => $appointment->doctor->license_number,
                    'profile_photo'  => $appointment->doctor->profile_photo_path,
                ],
                'clinic'           => [
                    'name'    => $appointment->clinic ? $appointment->clinic->name : null,
                    'address' => $appointment->clinic ? $appointment->clinic->address : null,
                ],
                'appointment_date' => is_string($appointment->appointment_date) 
                    ? $appointment->appointment_date 
                    : $appointment->appointment_date->toDateString(),
                'start_time'       => is_string($appointment->start_time) 
                    ? $appointment->start_time 
                    : $appointment->start_time->toTimeString(),
                'fee'              => $appointment->fee,
                'notes'            => $appointment->notes,
                'status'           => $appointment->status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $formattedAppointments,
        ], 200);

    } catch (\Exception $e) {
        Log::error('GetAppointments - Error: ' . $e->getMessage());
        return response()->json([
            'status'  => 'error',
            'message' => 'خطای سرور',
            'data'    => null,
        ], 500);
    }
}

  
}
