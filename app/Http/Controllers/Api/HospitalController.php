<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class HospitalController extends Controller
{
    public function getHospitalDetails($slug, Request $request)
    {
        try {
            // دریافت اطلاعات بیمارستان بر اساس slug
            $hospital = Hospital::with(['province', 'city', 'doctors', 'doctors.specialty', 'doctors.province', 'doctors.city'])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (!$hospital) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بیمارستان یافت نشد',
                    'data' => null,
                ], 404);
            }

            // تعداد پزشکان فعال و تخصصی
            $totalDoctors = $hospital->doctors()->where('is_active', true)->count();
            $specializedDoctors = $hospital->doctors()->where('is_active', true)
                ->whereNotNull('specialty_id')
                ->count();

            // لیست تخصص‌ها برای فیلتر
            $specialties = Specialty::whereIn('id', $hospital->doctors()->pluck('specialty_id'))
                ->get(['id', 'name']);

            // پارامترهای فیلتر و جستجو
            $searchQuery = $request->query('search', '');
            $specialtyId = $request->query('specialty_id', null);

            // دریافت لیست پزشکان
            $doctorsQuery = $hospital->doctors()->with(['specialty', 'province', 'city'])
                ->where('is_active', true);

            if ($searchQuery) {
                $doctorsQuery->where(function ($query) use ($searchQuery) {
                    $query->where('first_name', 'like', "%$searchQuery%")
                          ->orWhere('last_name', 'like', "%$searchQuery%")
                          ->orWhere('display_name', 'like', "%$searchQuery%");
                });
            }

            if ($specialtyId) {
                $doctorsQuery->where('specialty_id', $specialtyId);
            }

            $doctors = $doctorsQuery->get();

            // ساختاردهی داده‌های پزشکان
            $doctorsData = $doctors->map(function ($doctor) {
                $nextAvailableSlot = $this->getNextAvailableSlot($doctor);
                return [
                    'id' => $doctor->id,
                    'slug' => $doctor->slug,
                    'full_name' => $doctor->display_name ?? $doctor->first_name . ' ' . $doctor->last_name,
                    'specialty' => $doctor->specialty ? $doctor->specialty->name : 'نامشخص',
                    'city' => $doctor->city ? $doctor->city->name : null,
                    'rating' => $doctor->rating ?? 4.3,
                    'reviews_count' => $doctor->reviews_count ?? 189,
                    'views_count' => $doctor->views_count ?? 54000,
                    'next_available_slot' => $nextAvailableSlot['next_available_slot'],
                    'next_available_datetime' => $nextAvailableSlot['next_available_datetime'],
                    'tags' => $this->getDoctorTags($doctor),
                    'appointment_options' => [
                        'in_person' => $doctor->clinics()->exists(),
                        'phone' => $doctor->counselingConfig()->exists() && $doctor->counselingConfig->price_15min > 0,
                        'text' => $doctor->counselingConfig()->exists() && $doctor->counselingConfig->price_15min > 0,
                    ],
                    'profile_photo' => $doctor->profile_photo_path ?? '/default-avatar.png',
                ];
            });

            // پاسخ نهایی
            return response()->json([
                'status' => 'success',
                'data' => [
                    'hospital' => [
                        'id' => $hospital->id,
                        'slug' => $hospital->slug,
                        'name' => $hospital->name,
                        'address' => $hospital->address,
                        'phone_number' => $hospital->phone_number ?? $hospital->secretary_phone,
                        'description' => $hospital->description,
                        'city' => $hospital->city ? $hospital->city->name : null,
                        'province' => $hospital->province ? $hospital->province->name : null,
                        'latitude' => $hospital->latitude,
                        'longitude' => $hospital->longitude,
                        'avatar' => $hospital->avatar ?? '/default-hospital-avatar.png',
                        'total_doctors' => $totalDoctors,
                        'specialized_doctors' => $specializedDoctors,
                        'rating' => $hospital->rating ?? 4.3,
                        'reviews_count' => $hospital->reviews_count ?? 1903,
                    ],
                    'specialties' => $specialties->map(function ($specialty) {
                        return [
                            'id' => $specialty->id,
                            'name' => $specialty->name,
                        ];
                    }),
                    'doctors' => $doctorsData,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('GetHospitalDetails - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور',
                'data' => null,
            ], 500);
        }
    }

 private function getNextAvailableSlot($doctor)
{
    try {
        $now = Carbon::now('Asia/Tehran');
        $appointment = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', '>=', $now->toDateString())
            ->whereIn('status', ['scheduled', 'pending_review'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();

        if (!$appointment) {
            return [
                'next_available_slot' => 'در حال حاضر نوبت خالی موجود نیست',
                'next_available_datetime' => null,
            ];
        }

        // اطمینان از فرمت درست
        $appointmentDate = Carbon::parse($appointment->appointment_date)->toDateString();
        $appointmentTime = Carbon::parse($appointment->appointment_time)->format('H:i:s');
        $appointmentDateTime = Carbon::parse("$appointmentDate $appointmentTime", 'Asia/Tehran');

        $jalaliDate = Jalalian::fromCarbon($appointmentDateTime)->format('j F Y');
        $slotTime = $appointmentDateTime->format('H:i');

        return [
            'next_available_slot' => "$jalaliDate ساعت $slotTime",
            'next_available_datetime' => $appointmentDateTime->toDateTimeString(),
        ];
    } catch (\Exception $e) {
        Log::error('GetNextAvailableSlot - Error: ' . $e->getMessage());
        return [
            'next_available_slot' => 'خطا در محاسبه نوبت',
            'next_available_datetime' => null,
        ];
    }
}

    private function getDoctorTags($doctor)
    {
        return [
            ['name' => 'کمترین معطلی', 'color' => 'green'],
            ['name' => 'خوش برخورد', 'color' => 'orange'],
            ['name' => 'پوشش بیمه', 'color' => 'yellow'],
        ];
    }
}
