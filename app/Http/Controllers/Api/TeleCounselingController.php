<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CounselingAppointment;

class TeleCounselingController extends Controller
{
    /**
     * گرفتن لیست مشاوران تلفنی، تصویری و متنی
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "مشاوران با موفقیت دریافت شدند",
     *   "data": [
     *     {
     *       "doctor_id": 1,
     *       "first_name": "دکتر",
     *       "last_name": "محمدی",
     *       "display_name": "دکتر محمدی",
     *       "profile_photo_url": "http://example.com/storage/profiles/photo1.jpg",
     *       "specialty": "متخصص گوارش",
     *       "average_rating": 4.5,
     *       "appointment_type": "phone",
     *       "video_meeting_link": null,
     *       "chat_history": null
     *     }
     *   ]
     * }
     */
    public function index(Request $request)
    {
        try {
            $appointmentTypes = ['phone', 'video', 'text'];
            $appointments     = CounselingAppointment::whereIn('appointment_type', $appointmentTypes)
                ->where('status', 'scheduled')
                ->with(['doctor' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'display_name', 'profile_photo_path', 'average_rating')
                        ->with(['specialty' => function ($q) {
                            $q->select('id', 'name');
                        }]);
                }])
                ->get()
                ->map(function ($appointment) {
                    $doctor = $appointment->doctor;
                    return [
                        'doctor_id'          => $doctor->id,
                        'first_name'         => $doctor->first_name,
                        'last_name'          => $doctor->last_name,
                        'display_name'       => $doctor->display_name,
                        'profile_photo_url'  => $doctor->profile_photo_path ? asset('storage/' . $doctor->profile_photo_path) : null,
                        'specialty'          => $doctor->specialty ? $doctor->specialty->name : null,
                        'average_rating'     => $doctor->average_rating,
                        'appointment_type'   => $appointment->appointment_type,
                        'video_meeting_link' => $appointment->video_meeting_link,
                        'chat_history'       => $appointment->chat_history,
                    ];
                })
                ->unique('doctor_id')
                ->values();

            return response()->json([
                'status'  => 'success',
                'message' => 'مشاوران با موفقیت دریافت شدند',
                'data'    => $appointments,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطایی در سرور رخ داده است',
                'data'    => null,
            ], 500);
        }
    }
}
