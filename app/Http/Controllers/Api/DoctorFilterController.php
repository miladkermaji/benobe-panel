<?php
namespace App\Http\Controllers\Api;

use App\Models\Zone;
use App\Models\Service;
use App\Models\Insurance;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\DoctorCounselingConfig;

class DoctorFilterController extends Controller
{
    /**
     * گرفتن لیست گزینه‌های فیلتر (استان‌ها، شهرها، تخصص‌ها، انواع خدمات، خدمات، بیمه‌ها، جنسیت، نوبت باز)
     */
    public function getFilterOptions(Request $request)
    {
        try {
            // کش کردن برای 24 ساعت (1440 دقیقه)
            $data = Cache::remember('doctor_filter_options', 1440, function () {
                // گرفتن استان‌ها (level = 1)
                $provinces = Zone::where('level', 1)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();

                // گرفتن شهرها (level = 2)
                $cities = Zone::where('level', 2)
                    ->select('id', 'name', 'parent_id as province_id')
                    ->orderBy('name')
                    ->get();

                // گرفتن تخصص‌ها از جدول specialties
                $specialties = Specialty::select('id', 'name')
                    ->orderBy('name')
                    ->get();

                // بررسی اینکه کدام نوع مشاوره فعال است
                $hasPhoneCounseling = DoctorCounselingConfig::where('has_phone_counseling', true)->exists();
                $hasTextCounseling  = DoctorCounselingConfig::where('has_text_counseling', true)->exists();
                $hasVideoCounseling = DoctorCounselingConfig::where('has_video_counseling', true)->exists();

                                                                                           // تعریف انواع خدمات (تب‌ها) - فقط مواردی که حداقل یک پزشک فعال کرده باشد
                $serviceTypes = [['value' => 'in_person', 'name' => 'نوبت‌دهی']]; // نوبت‌دهی همیشه وجود دارد
                if ($hasPhoneCounseling) {
                    $serviceTypes[] = ['value' => 'phone', 'name' => 'مشاوره تلفنی'];
                }
                if ($hasTextCounseling) {
                    $serviceTypes[] = ['value' => 'text', 'name' => 'مشاوره متنی'];
                }
                if ($hasVideoCounseling) {
                    $serviceTypes[] = ['value' => 'video', 'name' => 'مشاوره ویدیویی'];
                }

                // گرفتن خدمات از جدول services (فقط 10 مورد اول برای بهینه‌سازی)
                $services = Service::where('status', true)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();

                // گرفتن بیمه‌ها از جدول insurances
                $insurances = Insurance::select('id', 'name')
                    ->orderBy('name')
                    ->get();

                // تعریف گزینه‌های جنسیت (هماهنگ با UI)
                $genders = [
                    ['value' => 'male', 'name' => 'آقا'],
                    ['value' => 'female', 'name' => 'خانم'],
                    ['value' => 'both', 'name' => 'خانم و آقا'],
                ];

                // گزینه نوبت باز
                $availableAppointments = [
                    'value' => true,
                    'name'  => 'پزشکان دارای نوبت باز',
                ];

                return [
                    'provinces'              => $provinces,
                    'cities'                 => $cities,
                    'specialties'            => $specialties,
                    'service_types'          => $serviceTypes,
                    'services'               => $services,
                    'insurances'             => $insurances,
                    'genders'                => $genders,
                    'available_appointments' => $availableAppointments,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetFilterOptions - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}
